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

namespace Smalldb\SmalldbBundle\ArgumentResolver;

use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Smalldb\StateMachine\Reference;
use Smalldb\StateMachine\Smalldb;
use Smalldb\StateMachine\InvalidReferenceException;


/**
 * Resolve path to state machine Reference
 */
class ReferenceValueResolver implements ArgumentValueResolverInterface
{
	protected $smalldb;

	protected $referenceClassNames;


	public function __construct(Smalldb $smalldb)
	{
		$this->smalldb = $smalldb;

		// Collect reference class names for argument resolver
		$this->referenceClassNames = [];
		foreach ($this->smalldb->getBackends() as $backend) {
			foreach($backend->getKnownTypes() as $m) {
				$machine = $backend->getMachine($smalldb, $m);
				if (!$machine) {
					continue;
				}
				$referenceClassName = $machine->getReferenceClassName();
				if ($referenceClassName !== Reference::class) {
					if (isset($this->referenceClassNames[$referenceClassName])) {
						throw new \RuntimeException("Reference class $referenceClassName used by multiple machines.");
					} else {
						$this->referenceClassNames[$referenceClassName] = $machine;
					}
				}
			}
		}
	}


	/**
	 * {@inheritdoc}
	 */
	public function supports(Request $request, ArgumentMetadata $argument)
	{
		if (isset($this->referenceClassNames[$argument->getType()])) {
			return $request->attributes->has($argument->getName());
		} else {
			return false;
		}

		// $path = $request->attributes->get($argument->getName());
		// return $this->smalldb->inferMachineType(explode('/', $path), $m_type, $m_id);
	}


	/**
	 * {@inheritdoc}
	 */
	public function resolve(Request $request, ArgumentMetadata $argument)
	{
		$machine = $this->referenceClassNames[$argument->getType()];
		$arg = $request->attributes->get($argument->getName());
		yield $machine->ref($arg);

		/*
		$path = $arg;
		if (!is_array($path)) {
			$path = explode('/', $path);
		}
		yield $this->smalldb->ref($path);
		*/
	}

}

