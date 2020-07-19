<?php
/*
 * Copyright (c) 2017-2020, Josef Kufner  <josef@kufner.cz>
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

use Smalldb\StateMachine\DebugLoggerInterface;
use Smalldb\StateMachine\Definition\StateMachineDefinition;
use Smalldb\StateMachine\ReferenceInterface;
use Smalldb\StateMachine\RuntimeException;
use Smalldb\StateMachine\Transition\TransitionEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Smalldb\StateMachine\Smalldb;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;
use Symfony\Component\Stopwatch\Stopwatch;


class SmalldbDataCollector implements DataCollectorInterface, LateDataCollectorInterface, DebugLoggerInterface
{
	private const DEFINITIONS_SNAPSHOT = false;

	private Smalldb $smalldb;
	private Stopwatch $stopwatch;

	/** @var string[] */
	private array $machineTypes;

	/** @var StateMachineDefinition[] */
	private array $definitions;

	private bool $definitionsSnapshot;
	private int $referencesCreatedCount;
	private int $transitionsInvokedCount;


	public function __construct(Smalldb $smalldb, Stopwatch $stopwatch)
	{
		$this->reset();
		$this->smalldb = $smalldb;
		$this->stopwatch = $stopwatch;
	}


	public function __sleep()
	{
		$propertyBlacklist = ['smalldb'];
		return array_diff(array_keys(get_object_vars($this)), $propertyBlacklist);
	}


	public function reset()
	{
		$this->machineTypes = [];
		$this->definitions = [];
		$this->definitionsSnapshot = static::DEFINITIONS_SNAPSHOT;
		$this->referencesCreatedCount = 0;
		$this->transitionsInvokedCount = 0;
	}


	public function collect(Request $request, Response $response, \Throwable $exception = null)
	{
		$this->machineTypes = $this->smalldb->getMachineTypes();
		if ($this->definitionsSnapshot) {
			$this->definitions = [];
			foreach ($this->machineTypes as $machineType) {
				$this->definitions[$machineType] = $this->smalldb->getDefinition($machineType);
			}
		}
	}


	public function lateCollect()
	{
	}


	public function getName()
	{
		return 'smalldb';
	}


	public function hasDefinitionsSnapshot(): bool
	{
		return $this->definitionsSnapshot;
	}


	/**
	 * @return string[]
	 */
	public function getMachineTypes(): array
	{
		return $this->machineTypes;
	}


	public function hasDefinitions(): bool
	{
		return !empty($this->definitions);
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
		if (isset($this->definitions[$machineType])) {
			return $this->definitions[$machineType];
		} else {
			throw new RuntimeException('State machine definition not found: ' . $machineType);
		}
	}


	public function getLog(): array
	{
		return [];
	}


	public function logReferenceCreated(ReferenceInterface $ref)
	{
		$this->referencesCreatedCount++;
		$this->stopwatch->start('reference created: ' . $ref->getMachineType(), 'smalldb')->stop();
	}


	public function logTransitionInvoked(TransitionEvent $transitionEvent): ?array
	{
		$this->transitionsInvokedCount++;
		$stopwatchEvent = $this->stopwatch->start('transition ' . $transitionEvent->getRef()->getMachineType() . '::' . $transitionEvent->getTransitionName());
		return [$stopwatchEvent];
	}


	public function logTransitionCompleted(TransitionEvent $transitionEvent, ?array $invokeContext)
	{
		[$stopwatchEvent] = $invokeContext;
		$stopwatchEvent->stop('transition ' . $transitionEvent->getRef()->getMachineType() . '::' . $transitionEvent->getTransitionName());
	}


	public function getReferencesCreatedCount(): int
	{
		return $this->referencesCreatedCount;
	}


	public function getTransitionsInvokedCount(): int
	{
		return $this->transitionsInvokedCount;
	}

}

