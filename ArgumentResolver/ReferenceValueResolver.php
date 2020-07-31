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

namespace Smalldb\SmalldbBundle\ArgumentResolver;

use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Smalldb\StateMachine\Smalldb;


/**
 * Resolve path to state machine Reference
 */
class ReferenceValueResolver implements ArgumentValueResolverInterface
{
	protected Smalldb $smalldb;
	protected array $referenceClassMap;


	/**
	 * ReferenceValueResolver constructor.
	 */
	public function __construct(Smalldb $smalldb, ?array $referenceClassMap = null)
	{
		$this->smalldb = $smalldb;
		$this->referenceClassMap = $referenceClassMap ?? static::buildReferenceClassMap($this->smalldb);
	}


	/**
	 * Use this to build the second argument for the constructor.
	 */
	public static function buildReferenceClassMap(Smalldb $smalldb): array
	{
		// Collect reference class names for argument resolver
		$referenceClassMap = [];
		foreach ($smalldb->getMachineTypes() as $machineType) {
			// Register the reference class
			$referenceClassName = $smalldb->getReferenceClass($machineType);
			if (isset($referenceClassMap[$referenceClassName])) {
				throw new \RuntimeException("Reference class $referenceClassName used by multiple machines: "
					. $referenceClassMap[$referenceClassName] . " and " . $machineType);
			} else {
				$referenceClassMap[$referenceClassName] = $machineType;
			}

			// Register also the parent class (FIXME)
			$referenceClassReflection = new \ReflectionClass($referenceClassName);
			$parentClassReflection = $referenceClassReflection->getParentClass();
			if ($parentClassReflection) {
				$parentClassName = $parentClassReflection->getName();
				if (!isset($referenceClassMap[$parentClassName])) {
					// Only register if unique
					$referenceClassMap[$parentClassName] = $machineType;
				}
			}
		}
		return $referenceClassMap;
	}


	/**
	 * {@inheritdoc}
	 */
	public function supports(Request $request, ArgumentMetadata $argument)
	{
		if (isset($this->referenceClassMap[$argument->getType()])) {
			return $request->attributes->has($argument->getName()) || $request->attributes->has('id');
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
		$machineType = $this->referenceClassMap[$argument->getType()];
		$id = $request->attributes->get($argument->getName()) ?? $request->attributes->get('id');
		if ($id === null) {
			throw new \LogicException("Resolved reference should not be a null reference: " . $argument->getType());
		}
		yield $this->smalldb->ref($machineType, $id);

		/*
		$path = $arg;
		if (!is_array($path)) {
			$path = explode('/', $path);
		}
		yield $this->smalldb->ref($path);
		*/
	}

}

