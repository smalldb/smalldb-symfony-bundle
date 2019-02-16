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

use Smalldb\Flupdo\FlupdoDoctrineWrapper;
use Smalldb\Flupdo\FlupdoPdoWrapper;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder('smalldb');
		$treeBuilder->getRootNode()
			->children()
				->booleanNode('debug')
					->info('Enable Smalldb debugging')
					->defaultValue('%kernel.debug%')
					->treatNullLike('%kernel.debug%')
				->end()
				->arrayNode('smalldb')
					->info('Smalldb backend configuration')
					->ignoreExtraKeys(false)
					->children()
						->scalarNode('base_dir')
							->info('Path to state machine definitions in JSON files (see JsonDirReader).')
							->defaultValue('%kernel.project_dir%/config/statemachines')
							->cannotBeEmpty()
							->isRequired()
						->end()
						->booleanNode('cache_disabled')
							->info('Don\'t bake machine definitions into the DI container.')
							->defaultValue(false)
							->treatNullLike(false)
						->end()
						->scalarNode('code_dest_dir')
							->info('Path where generated PHP files will be stored.')
							->defaultValue('%kernel.cache_dir%/smalldb')
							->cannotBeEmpty()
						->end()
					->end()
				->end()
				->arrayNode('flupdo')
					->info('Database configuration (see PDO; use wrapper_class or driver or dsn)')
					->ignoreExtraKeys(false)
					->children()
						->scalarNode('wrapper_class')
							->info('Classname of FlupdoWrapper implementation to use existing PDO connection (e.g. "'.FlupdoPdoWrapper::class.'", or "'.FlupdoDoctrineWrapper::class.'"). Auto-wiring will do the trick.')
						->end()
						->scalarNode('wrapped_service')
							->info('Name of service to be wrapped by the wrapper_class if auto-wiring is not enough (use with wrapper_class).')
						->end()
						->scalarNode('dsn')
							->info('DSN of the database connection.')
						->end()
						->scalarNode('driver')
							->info('PDO driver to establish database connection.')
						->end()
						->scalarNode('host')
							->info('Database server host (use with driver).')
						->end()
						->scalarNode('port')
							->info('Database server port (use with driver).')
						->end()
						->scalarNode('database')
							->info('Database name (use with driver).')
						->end()
						->scalarNode('username')
							->info('User name (use with driver or dsn).')
						->end()
						->scalarNode('password')
							->info('User password (use with driver or dsn).')
						->end()
						->booleanNode('log_query')
							->info('Log all SQL queries to error log? May not work with FlupdoWrapper.')
							->defaultValue(false)
							->treatNullLike(false)
						->end()
						->booleanNode('log_explain')
							->info('Log explains along the SQL queries? May not work with FlupdoWrapper.')
							->defaultValue(false)
							->treatNullLike(false)
						->end()
					->end()
				->end()
				->arrayNode('auth')
					->info('Smalldb authentication and session management')
					->ignoreExtraKeys(false)
					->children()
						->scalarNode('class')
							->info('Classname of the authenticator class')
							->isRequired()
							->defaultValue('Smalldb\\StateMachine\\Auth\\CookieAuth')
						->end()
						->scalarNode('cookie_name')
							->info('Cookie name')
						->end()
						->scalarNode('cookie_ttl')
							->info('Cookie duration [seconds] (default: 30 days)')
						->end()
						->scalarNode('user_id_property')
							->info('Name of the session machine property with user\'s ID')
						->end()
						->scalarNode('all_mighty_user_role')
							->info('Name of all mighty user role (like admin or root)')
						->end()
						->scalarNode('all_mighty_cli')
							->info('Is command line all mighty?')
						->end()
						->scalarNode('session_machine_null_ref')
							->info('Null reference to session machine (array; use session_machine_ref_prefix if not set)')
						->end()
						->scalarNode('session_machine_ref_prefix')
							->info('Prefix of session machine reference (array; token ID will be appended)')
						->end()
					->end()
				->end()
			->end();
		return $treeBuilder;
	}
}

