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


	function afterDebugLoggerRegistered(AbstractBackend $smalldb)
	{
		$this->machines_defined_count = count($smalldb->getKnownTypes());
	}


	function afterMachineCreated(AbstractBackend $smalldb, string $type, AbstractMachine $machine)
	{
		$this->machines_created_count++;
		$this->log[] = [
			'event' => 'afterMachineCreated',
			'machine_type' => $type,
			'class' => get_class($machine),
		];
	}

	function afterReferenceCreated(AbstractBackend $smalldb, Reference $ref, array $properties = null)
	{
		$this->references_created_count++;
		$this->log[] = [
			'event' => 'afterReferenceCreated',
			'machine_type' => $ref->machine_type,
			'id' => $ref->id,
			'class' => get_class($ref),
			'properties' => $properties,
		];
	}

	function afterListingCreated(AbstractBackend $smalldb, IListing $listing, array $filters)
	{
		$this->listings_created_count++;
		$this->log[] = [
			'event' => 'afterListingCreated',
			'machine_type' => $filters['type'] ?? null,
			'class' => get_class($listing),
			'filters' => $filters,
			//'processed_filters' => $listing->getProcessedFilters(),
			//'unknown_filters' => $listing->getUnknownFilters(),
		];
	}

	function beforeTransition(AbstractBackend $smalldb, Reference $ref, string $old_state, string $transition_name, $args)
	{
		$this->transitions_invoked_count++;
		$this->log[] = [
			'event' => 'beforeTransition',
			'machine_type' => $ref->machine_type,
			'id' => $ref->id,
			'class' => get_class($ref->machine),
			'old_state' => $old_state,
			'transition' => $transition_name,
			'args' => $args,
		];
	}

	function afterTransition(AbstractBackend $smalldb, Reference $ref, string $old_state, string $transition_name, string $new_state, $return_value, $returns)
	{
		$this->log[] = [
			'event' => 'afterTransition',
			'machine_type' => $ref->machine_type,
			'id' => $ref->id,
			'class' => get_class($ref->machine),
			'old_state' => $old_state,
			'transition' => $transition_name,
			'new_state' => $new_state,
			'return_value' => $return_value,
		];
	}

}

