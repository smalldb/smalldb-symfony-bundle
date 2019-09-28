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

namespace Smalldb\SmalldbBundle\DataCollector;

use Smalldb\StateMachine\Definition\StateMachineDefinition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Smalldb\StateMachine\Smalldb;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;


class SmalldbDataCollector implements DataCollectorInterface
{

	private const DEFINITIONS_SNAPSHOT = false;

	/** @var Smalldb */
	private $smalldb;

	/** @var string[] */
	private $machineTypes;

	/** @var StateMachineDefinition[] */
	private $definitions;

	private $references_created_count = 0;


	public function __construct(Smalldb $smalldb)
	{
		$this->reset();
		$this->smalldb = $smalldb;
	}


	public function __sleep()
	{
		$propertyBlacklist = ['smalldb'];
		return array_diff(array_keys(get_object_vars($this)), $propertyBlacklist);
	}


	public function hasDefinitions(): bool
	{
		return !empty($this->definitions);
	}


	public function collect(Request $request, Response $response, \Exception $exception = null)
	{
		$this->machineTypes = $this->smalldb->getMachineTypes();
		if (static::DEFINITIONS_SNAPSHOT) {
			$this->definitions = [];
			foreach ($this->machineTypes as $machineType) {
				$this->definitions[$machineType] = $this->smalldb->getDefinition($machineType);
			}
		}
	}


	public function reset()
	{
		$this->machineTypes = [];
		$this->definitions = [];
		$this->references_created_count = 0;
	}


	public function getName()
	{
		return 'smalldb';
	}


	/**
	 * @return string[]
	 */
	public function getMachineTypes(): array
	{
		return $this->machineTypes;
	}


	/**
	 * @return StateMachineDefinition[]
	 */
	public function getDefinitions(): array
	{
		return $this->definitions;
	}


	public function getDefinition(string $machineType): StateMachineDefinition
	{
		return $this->definitions[$machineType];
	}


	public function getLog(): array
	{
		return [];
	}

}

