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

class DebugLogger implements IDebugLogger
{
	public $log = [];

	public $machines_created_count = 0;
	public $references_created_count = 0;
	public $transitions_invoked_count = 0;


	function afterMachineCreated($smalldb, $type, $machine)
	{
		$this->machines_created_count++;
		$this->log[] = [
			'event' => 'afterMachineCreated',
			'machine_type' => $type,
		];
	}

	function afterReferenceCreated($smalldb, $ref, $properties = null)
	{
		$this->references_created_count++;
		$this->log[] = [
			'event' => 'afterReferenceCreated',
			'machine_type' => $ref->machine_type,
			'id' => $ref->id,
			'properties' => $properties,
		];
	}

	function beforeTransition($ref, $old_state, $transition_name, $args)
	{
		$this->transitions_invoked_count++;
		$this->log[] = [
			'event' => 'beforeTransition',
			'machine_type' => $ref->machine_type,
			'id' => $ref->id,
			'old_state' => $old_state,
			'transition' => $transition_name,
			'args' => $args,
		];
	}

	function afterTransition($ref, $old_state, $transition_name, $new_state, $return_value, $returns)
	{
		$this->log[] = [
			'event' => 'afterTransition',
			'machine_type' => $ref->machine_type,
			'id' => $ref->id,
			'old_state' => $old_state,
			'transition' => $transition_name,
			'new_state' => $new_state,
			'return_value' => $return_value,
		];
	}

}

