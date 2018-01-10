<?php
/*
 * Copyright (c) 2017, Josef Kufner  <josef@kufner.cz>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

namespace Smalldb\SmalldbBundle\DependencyInjection;

use Smalldb\Flupdo\FlupdoWrapper;
use Smalldb\SmalldbBundle\ArgumentResolver\ReferenceValueResolver;
use Smalldb\SmalldbBundle\Security\SmalldbAuthenticationListener;
use Smalldb\SmalldbBundle\Security\SmalldbAuthenticationProvider;
use Smalldb\SmalldbBundle\DataCollector\SmalldbDataCollector;
use Smalldb\SmalldbBundle\DataCollector\DebugLogger;
use Smalldb\StateMachine\Smalldb;
use Smalldb\SmalldbBundle\JsonDirBackend;
use Smalldb\Flupdo\Flupdo;
use Smalldb\Flupdo\IFlupdo;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;


class SmalldbExtension extends Extension implements CompilerPassInterface
{
	public function load(array $configs, ContainerBuilder $container)
	{
		// Get configuration
		$configuration = new Configuration();
		$config = $this->processConfiguration($configuration, $configs);

		// Create Smalldb entry point
		$smalldb_definition = $container->autowire(Smalldb::class)
			->setShared(true)
			->setPublic(true);
		$container->setAlias('smalldb', Smalldb::class);

		// Reference resolver
		$container->autowire(ReferenceValueResolver::class)
			->setArguments([new Reference(Smalldb::class)])
			->addTag('controller.argument_value_resolver', ['priority' => 200]);

		// Default services
		if (!empty($config['smalldb'])) {
			// Default machine implementations as non-shared services
			$container->autowire(\Smalldb\StateMachine\FlupdoMachine::class)->setPublic(true)->setShared(false);
			$container->autowire(\Smalldb\StateMachine\FlupdoCrudMachine::class)->setPublic(true)->setShared(false);
			$container->autowire(\Smalldb\StateMachine\Auth\SharedTokenMachine::class)->setPublic(true)->setShared(false);

			// Create default Smalldb backend
			if (!empty($config['smalldb']['base_dir'])) {
				$container->autowire(JsonDirBackend::class)
					->setArguments([$config['smalldb'], new Reference('service_container')])
					->addTag('smalldb.backend')
					->setShared(true);
			}

			// Initialize authenticator
			if (empty($config['auth']['class'])) {
				throw new \InvalidArgumentException('Authenticator not defined. Please set smalldb.auth.class option.');
			}
			$container->register('auth', $config['auth']['class'])
				->setArguments([$config['auth'], new Reference(Smalldb::class)])
				->addMethodCall('checkSession');	// FIXME: Isn't this supposed to be in the authentication listener ?

			// Authentication listener
			$container->autowire(SmalldbAuthenticationListener::class)
				->setArguments([
					new Reference('security.token_storage'),
					new Reference('security.authentication.manager'),
					new Reference(Smalldb::class)
				]);

			// Authentication provider
			$container->autowire(SmalldbAuthenticationProvider::class);

			// Compile command
			$container->autowire(\Smalldb\SmalldbBundle\Command\CompileSmalldbCommand::class)
				->setArguments([new Reference(Smalldb::class), $config['smalldb']['code_dest_dir']])
				->addTag('console.command')
				->setShared(false);
		}

		// Database connection & query builder
		if (!empty($config['flupdo'])) {
			if (!empty($config['flupdo']['wrapper_class'])) {
				$wrapper = $container->autowire(IFlupdo::class)
					->setClass($config['flupdo']['wrapper_class'])
					->setShared(true);
				if ($config['flupdo']['wrapped_service']) {
					$wrapper->setArguments([new Reference($config['flupdo']['wrapped_service'])]);
				}
				$container->setAlias(FlupdoWrapper::class, IFlupdo::class);
			} else if (!empty($config['flupdo']['driver']) || !empty($config['flupdo']['dsn'])) {
				$container->autowire(IFlupdo::class)
					->setFactory([Flupdo::class, 'createInstanceFromConfig'])
					->setArguments([$config['flupdo']])
					->setShared(true);
				$container->setAlias(Flupdo::class, IFlupdo::class);
				$container->setAlias(\PDO::class, IFlupdo::class);
			}
			$container->setAlias('flupdo', IFlupdo::class)->setPublic(true);
		}

		// Developper tools
		if ($config['debug']) {
			// Register debugger
			$smalldb_definition->addMethodCall('setDebugLogger', [new Reference(DebugLogger::class)]);

			// Data logger
			$container->register(DebugLogger::class);

			// Web Profiler page
			$container->autowire(SmalldbDataCollector::class)
				->setArguments([new Reference(Smalldb::class), new Reference(DebugLogger::class)])
				->addTag('data_collector', [
					'template' => '@Smalldb/data_collector/template.html.twig',
					'id'       => 'smalldb',
					'priority' => 270,
				]);
		}
	}


	public function process(ContainerBuilder $container)
	{
		if (!$container->has(Smalldb::class)) {
			return;
		}
		$smalldb_definition = $container->findDefinition(Smalldb::class);

		$tagged_services = $container->findTaggedServiceIds('smalldb.backend');
		foreach ($tagged_services as $id => $tags) {
			$smalldb_definition->addMethodCall('registerBackend', array(new Reference($id)));
		}
	}

}

