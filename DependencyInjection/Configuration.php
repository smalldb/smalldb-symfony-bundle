<?php
/*
 * Copyright (c) 2017-2019, Josef Kufner  <josef@kufner.cz>
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

use Smalldb\StateMachine\SymfonyDI\Configuration as SmalldbConfiguration;
use Symfony\Component\Config\Definition\ConfigurationInterface;


class Configuration extends SmalldbConfiguration implements ConfigurationInterface
{
	public function getConfigTreeBuilder()
	{
		$treeBuilder = parent::getConfigTreeBuilder();
		$treeBuilder->getRootNode()
			->children()
				->booleanNode('debug')
					->info('Enable Smalldb debugging')
					->defaultValue('%kernel.debug%')
					->treatNullLike('%kernel.debug%')
				->end()
			->end();
		return $treeBuilder;
	}
}

