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

namespace Smalldb\SmalldbBundle;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Smalldb Backend which loads state machine definitions from a directory full 
 * of JSON files and other files included by the JSON files; each JSON file
 * defines a one state machine type.
 *
 * JsonDirBackend supports following file types:
 *
 *   - JSON: Raw structure loaded as is. See configuration schema sections in
 *     AbstractMachine and derived classes. Each of these files represents one
 *     state machine type and can include other files.
 *       - Extensions: `.json`, `.json.php`
 *   - GraphML: Graph created by yEd graph editor. See GraphMLReader.
 *       - Extensions: `.graphml`
 *   - BPMN: Process diagrams in standard BPMN XML file. See BpmnReader.
 *       - Extensions: `.bpmn`
 *
 * @see \Smalldb\StateMachine\JsonDirBackend
 *
 */
class JsonDirBackend extends \Smalldb\StateMachine\JsonDirBackend implements ContainerAwareInterface
{
	/**
	 * Require Symfony's DI container to implement ContainerAwareInterface.
	 */
	public function setContainer(ContainerInterface $container = null)
	{
		return parent::setContainer($container);
	}

}

