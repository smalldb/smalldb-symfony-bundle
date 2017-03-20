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

namespace Smalldb\SmalldbBundle\Security;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;


class SmalldbSecurityFactory implements SecurityFactoryInterface
{

	public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
	{
		$providerId = 'security.authentication.provider.smalldb.'.$id;
		$container->setDefinition($providerId, new DefinitionDecorator('smalldb.security.authentication.provider'));

		$listenerId = 'security.authentication.listener.smalldb.'.$id;
		$listener = $container->setDefinition($listenerId, new DefinitionDecorator('smalldb.security.authentication.listener'));

		return array($providerId, $listenerId, $defaultEntryPoint);
	}


	public function getPosition()
	{
		return 'pre_auth';
	}


	public function getKey()
	{
		return 'smalldb';
	}


	public function addConfiguration(NodeDefinition $node)
	{
		// No configuration.
	}

}

