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

use Smalldb\SmalldbBundle\Security\SmalldbAuthenticationListener;
use Smalldb\SmalldbBundle\Security\SmalldbAuthenticationProvider;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;


class SmalldbExtension extends Extension
{
	public function load(array $configs, ContainerBuilder $container)
	{
		// Get configuration
		$configuration = new Configuration();
		$config = $this->processConfiguration($configuration, $configs);
		$config['smalldb']['machine_global_config']['flupdo_resource'] = 'flupdo';

                // Create Smalldb backend
		$smalldb = $container->register('smalldb', \Smalldb\SmalldbBundle\JsonDirBackend::class);
		$smalldb->setArguments([$config['smalldb'], null, 'smalldb']);
		$smalldb->addMethodCall('setContext', [new Reference('service_container')]);

                // Initialize database connection & query builder
		$flupdo = $container->register('flupdo', \Smalldb\Flupdo\Flupdo::class);
		$flupdo->setFactory([\Smalldb\Flupdo\Flupdo::class, 'createInstanceFromConfig']);
		$flupdo->setArguments([$config['flupdo']]);

                // Initialize authenticator
                if (!isset($config['auth']['class'])) {
                        throw new InvalidArgumentException('Authenticator not defined. Please set smalldb.auth.class option.');
                }
                $auth_class = $config['auth']['class'];
		$auth = $container->register('auth', $auth_class);
		$auth->setArguments([$config['auth'], $smalldb]);
		$auth->addMethodCall('checkSession');	// FIXME: Isn't this supposed to be in the authentication listener ?

		// Authentication listener
		$auth_listener = new Definition(SmalldbAuthenticationListener::class, array(
			new Reference('security.token_storage'),
			new Reference('security.authentication.manager'),
			new Reference('smalldb'),
		));
		$auth_listener->setPublic(false);
		$container->setDefinition('smalldb.security.authentication.listener', $auth_listener);

		// Authentication provider
		$auth_provider = new Definition(SmalldbAuthenticationProvider::class, array());
		$auth_provider->setPublic(false);
		$container->setDefinition('smalldb.security.authentication.provider', $auth_provider);

		// Reference resolver
		$definition = new Definition('Smalldb\SmalldbBundle\ArgumentResolver\ReferenceValueResolver', array(new Reference('smalldb')));
		$definition->addTag('controller.argument_value_resolver', array('priority' => 200));
		$container->setDefinition('app.value_resolver.smalldb_reference', $definition);
	}
}

