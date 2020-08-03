<?php declare(strict_types = 1);
/*
 * Copyright (c) 2020, Josef Kufner  <josef@kufner.cz>
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

namespace Smalldb\SmalldbBundle\Security;

use Smalldb\StateMachine\InvalidArgumentException;
use Smalldb\StateMachine\ReferenceInterface;
use Smalldb\StateMachine\RuntimeException;
use Smalldb\StateMachine\Smalldb;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;


class SmalldbVoter implements VoterInterface
{
	private Smalldb $smalldb;
	private bool $isAlreadyCalled = false;


	public function __construct(Smalldb $smalldb)
	{
		$this->smalldb = $smalldb;
	}


	protected function supports(string $attribute, $subject)
	{
		return $subject instanceof ReferenceInterface
			|| $attribute instanceof SmalldbRole
			|| (is_string($attribute) && strpos($attribute, '!') !== false);
	}


	public function vote(TokenInterface $token, $subject, array $attributes)
	{
		if ($this->isAlreadyCalled) {
			throw new RuntimeException("Recursive loop detected in " . __CLASS__ . ".");
		}

		$this->isAlreadyCalled = true;
		try {
			// At least one of the transitions must be allowed. This behavior is consistent with
			// Symfony\Component\Security\Core\Authorization\Voter\Voter::voteOnAttribute().
			foreach ($attributes as $attribute) {
				if ($attribute instanceof SmalldbRole) {
					return $this->voteOnTransition($attribute->machineType, $attribute->transition, $subject);
				}

				if (is_string($attribute) && ($p = strpos($attribute, '!')) !== false) {
					$machineType = substr($attribute, 0, $p);
					$transition = substr($attribute, $p + 1);
					return $this->voteOnTransition($machineType, $transition, $subject);
				}

				if ($subject instanceof ReferenceInterface) {
					return $this->voteOnTransition(null, $attribute, $subject);
				}
			}
			return self::ACCESS_ABSTAIN;
		}
		finally{
			$this->isAlreadyCalled = false;
		}
	}


	private function voteOnTransition(?string $machineType, string $transitionName, $subject)
	{
		if ($subject instanceof ReferenceInterface) {
			$ref = $subject;
			if ($machineType !== null) {
				if ($machineType !== $ref->getMachineType() && ! $ref instanceof $machineType) {
					return self::ACCESS_DENIED;
				}
			}
		} else if ($subject === null && $machineType !== null) {
			$ref = $this->smalldb->ref($machineType, null);
		} else {
			throw new InvalidArgumentException("Unsupported subject.");
		}

		return $ref->isTransitionAllowed($transitionName) ? self::ACCESS_GRANTED : self::ACCESS_DENIED;
	}

}
