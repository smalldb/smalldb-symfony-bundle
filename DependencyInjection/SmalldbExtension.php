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

use Smalldb\SmalldbBundle\ArgumentResolver\ReferenceValueResolver;
use Smalldb\SmalldbBundle\Controller\ProfilerController;
use Smalldb\SmalldbBundle\DataCollector\DebugLogger;
use Smalldb\SmalldbBundle\DataCollector\SmalldbDataCollector;
use Smalldb\SmalldbBundle\Security\SmalldbAuthenticationListener;
use Smalldb\SmalldbBundle\Security\SmalldbAuthenticationProvider;
use Smalldb\SmalldbBundle\Security\SmalldbUserProvider;
use Smalldb\SmalldbBundle\Security\SmalldbVoter;
use Smalldb\SmalldbBundle\Security\UserRepositoryInterface;
use Smalldb\SmalldbBundle\Twig\SmalldbProfilerTwigExtension;
use Smalldb\StateMachine\Smalldb;
use Smalldb\StateMachine\SymfonyDI\SmalldbExtension as LibSmalldbExtension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;


class SmalldbExtension extends LibSmalldbExtension implements CompilerPassInterface
{

	public function getConfiguration(array $config, ContainerBuilder $container)
	{
		return new Configuration();
	}


	public function load(array $configs, ContainerBuilder $container)
	{
		parent::load($configs, $container);

		// Reference resolver
		$container->autowire(ReferenceValueResolver::class, ReferenceValueResolver::class)
			->setArguments([new Reference(Smalldb::class)])
			->addTag('controller.argument_value_resolver', ['priority' => 200]);

		// Security Voter
		$container->autowire(SmalldbVoter::class, SmalldbVoter::class)
			->setAutoconfigured(true);

		// User provider
		$container->autowire(SmalldbUserProvider::class, SmalldbUserProvider::class)
			->addMethodCall('setSmalldb', [new Reference(Smalldb::class)])
			->addMethodCall('addUserRepositories', [tagged_iterator('smalldb.user_repository')]);
		$container->registerForAutoconfiguration(UserRepositoryInterface::class)
			->addTag('smalldb.user_repository');

		// Developper tools
		if (!empty($this->config['debug'])) {
			// Data logger
			//$container->register(DebugLogger::class);

			// Web Profiler page
			$container->autowire('data_collector.smalldb', SmalldbDataCollector::class)
				//->setArguments([new Reference(Smalldb::class), new Reference(DebugLogger::class)])
				->setArguments([new Reference(Smalldb::class)])
				->addTag('data_collector', [
					'template' => '@Smalldb/data_collector/template.html.twig',
					'id' => 'smalldb',
					'priority' => 270,
				]);

			$container->autowire(ProfilerController::class, ProfilerController::class)
				->setPublic(true);

			// Register debugger
			$container->getDefinition(Smalldb::class)->addMethodCall('setDebugLogger', [new Reference('data_collector.smalldb')]);

			$container->autowire(SmalldbProfilerTwigExtension::class)
				->addTag('twig.extension');
		}
	}

/*
	public function load(array $configs, ContainerBuilder $container)
	{
		// ...

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

		// ...

		return $config;
	}
*/


	public function process(ContainerBuilder $container)
	{
		parent::process($container);

		// ...
	}

}

