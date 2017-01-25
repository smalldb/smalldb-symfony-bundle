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
use Smalldb\StateMachine\AbstractBackend;
use Smalldb\StateMachine\InvalidReferenceException;


/**
 * Resolve path to state machine Reference
 */
class ReferenceValueResolver implements ArgumentValueResolverInterface
{
	protected $smalldb;

	
	public function __construct(AbstractBackend $smalldb)
	{
		$this->smalldb = $smalldb;
	}


	/**
	 * {@inheritdoc}
	 */
	public function supports(Request $request, ArgumentMetadata $argument)
	{
		return Reference::class == $argument->getType() && $request->attributes->has($argument->getName());

		// $path = $request->attributes->get($argument->getName());
		// return $this->smalldb->inferMachineType(split('/', $path), $m_type, $m_id);
	}


	/**
	 * {@inheritdoc}
	 */
	public function resolve(Request $request, ArgumentMetadata $argument)
	{
		$path = $request->attributes->get($argument->getName());
		if (!is_array($path)) {
			$path = split('/', $path);
		}
		yield $this->smalldb->ref($path);
	}

}

