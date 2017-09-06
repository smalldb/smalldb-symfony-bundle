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

use Smalldb\StateMachine\IDebugLogger;
use Smalldb\StateMachine\AbstractBackend;
use Smalldb\StateMachine\AbstractMachine;
use Smalldb\StateMachine\Reference;
use Smalldb\StateMachine\IListing;

class DebugLogger implements IDebugLogger
{
	public $log = [];

	public $machines_defined_count = 0;
	public $machines_created_count = 0;
	public $references_created_count = 0;
	public $listings_created_count = 0;
	public $transitions_invoked_count = 0;


	function afterDebugLoggerRegistered(AbstractBackend $backend)
	{
		$this->machines_defined_count = count($backend->getKnownTypes());
	}


	function afterMachineCreated(AbstractBackend $backend = null, string $type, AbstractMachine $machine)
	{
		$this->machines_created_count++;
		$this->log[] = [
			'backend' => $backend ? get_class($backend) : null,
			'event' => 'afterMachineCreated',
			'machine_type' => $type,
			'class' => $machine ? get_class($machine) : null,
		];
	}

	function afterReferenceCreated(AbstractBackend $backend = null, Reference $ref, array $properties = null)
	{
		$this->references_created_count++;
		$this->log[] = [
			'backend' => $backend ? get_class($backend) : null,
			'event' => 'afterReferenceCreated',
			'machine_type' => $ref->machine_type,
			'id' => $ref->id,
			'class' => $ref ? get_class($ref) : null,
			'properties' => $properties,
		];
	}

	function afterListingCreated(AbstractBackend $backend = null, IListing $listing, array $filters)
	{
		$this->listings_created_count++;
		$this->log[] = [
			'backend' => $backend ? get_class($backend) : null,
			'event' => 'afterListingCreated',
			'machine_type' => $filters['type'] ?? null,
			'class' => $listing ? get_class($listing) : null,
			'filters' => $filters,
			//'processed_filters' => $listing->getProcessedFilters(),
			//'unknown_filters' => $listing->getUnknownFilters(),
		];
	}

	function beforeTransition(AbstractMachine $machine, Reference $ref, string $old_state, string $transition_name, $args)
	{
		$this->transitions_invoked_count++;
		$this->log[] = [
			'backend' => null,
			'event' => 'beforeTransition',
			'machine_type' => $machine->getMachineType(),
			'id' => $ref->id,
			'class' => $machine ? get_class($machine) : null,
			'old_state' => $old_state,
			'transition' => $transition_name,
			'args' => $args,
		];
	}

	function afterTransition(AbstractMachine $machine, Reference $ref, string $old_state, string $transition_name, string $new_state, $return_value, $returns)
	{
		$this->log[] = [
			'backend' => null,
			'event' => 'afterTransition',
			'machine_type' => $machine->getMachineType(),
			'id' => $ref->id,
			'class' => $machine ? get_class($machine) : null,
			'old_state' => $old_state,
			'transition' => $transition_name,
			'new_state' => $new_state,
			'return_value' => $return_value,
		];
	}

}

