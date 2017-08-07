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
use Smalldb\SmalldbBundle\Security\SmalldbAuthenticationListener;
use Smalldb\SmalldbBundle\Security\SmalldbAuthenticationProvider;
use Smalldb\SmalldbBundle\DataCollector\SmalldbDataCollector;
use Smalldb\SmalldbBundle\DataCollector\DebugLogger;
use Smalldb\StateMachine\Smalldb;
use Smalldb\StateMachine\AbstractBackend;
use Smalldb\SmalldbBundle\JsonDirBackend;
use Smalldb\Flupdo\Flupdo;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;


class SmalldbExtension extends Extension
{
	public function load(array $configs, ContainerBuilder $container)
	{
		// Get configuration
		$configuration = new Configuration();
		$config = $this->processConfiguration($configuration, $configs);

		// Don't load anything when configuration is completely missing
		if (empty($config['smalldb'])) {
			return;
		}

		$config['smalldb']['machine_global_config']['flupdo_resource'] = 'flupdo';

		// Create Smalldb entry point
		$container->autowire(Smalldb::class)
			->addMethodCall('registerBackend', [new Reference(AbstractBackend::class)])
			->setShared(true)
			->setPublic(true);

                // Create Smalldb backend
		$container->autowire(JsonDirBackend::class)
			->setArguments([$config['smalldb'], null, 'smalldb'])
			->addMethodCall('setDebugLogger', [new Reference('smalldb.debug_logger')])
			->addMethodCall('setContext', [new Reference('service_container')])
			->setShared(true)
			->setPublic(false);
		$container->setAlias(AbstractBackend::class, JsonDirBackend::class);
		$container->setAlias('smalldb', AbstractBackend::class);

                // Initialize database connection & query builder
		$container->autowire('flupdo', Flupdo::class)
			->setFactory([Flupdo::class, 'createInstanceFromConfig'])
			->setArguments([$config['flupdo']])
			->setShared(true);
		$container->setAlias(Flupdo::class, 'flupdo');
		$container->setAlias(\PDO::class, 'flupdo');

                // Initialize authenticator
                if (empty($config['auth']['class'])) {
                        throw new InvalidArgumentException('Authenticator not defined. Please set smalldb.auth.class option.');
                }
		$container->register('auth', $config['auth']['class'])
			->setArguments([$config['auth'], new Reference(AbstractBackend::class)])
			->addMethodCall('checkSession');	// FIXME: Isn't this supposed to be in the authentication listener ?

		// Authentication listener
		$container->register('smalldb.security.authentication.listener', SmalldbAuthenticationListener::class)
			->setArguments([
				new Reference('security.token_storage'),
				new Reference('security.authentication.manager'),
				new Reference(AbstractBackend::class)
			])
			->setPublic(false);

		// Authentication provider
		$container->register('smalldb.security.authentication.provider', SmalldbAuthenticationProvider::class)
			->setPublic(false);

		// Reference resolver
		$container->register('app.value_resolver.smalldb_reference', ReferenceValueResolver::class)
			->setArguments([new Reference(AbstractBackend::class)])
			->addTag('controller.argument_value_resolver', ['priority' => 200]);

                // Data logger
		$container->register('smalldb.debug_logger', DebugLogger::class)
			->setPublic(false);

		// Web Profiler page
		$container->register('smalldb.data_collector', SmalldbDataCollector::class)
			->setArguments([new Reference(Smalldb::class), new Reference('smalldb.debug_logger')])
			->setPublic(false)
			->addTag('data_collector', [
				'template' => '@Smalldb/data_collector/template.html.twig',
				'id'       => 'smalldb',
				'priority' => 270,
			]);
	}
}

