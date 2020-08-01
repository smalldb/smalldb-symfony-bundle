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

use Smalldb\StateMachine\ReferenceInterface;
use Smalldb\StateMachine\RuntimeException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;


class SmalldbVoter implements VoterInterface
{
	private bool $isAlreadyCalled = false;


	protected function supports(string $attribute, $subject)
	{
		return $subject instanceof ReferenceInterface;
	}

	public function vote(TokenInterface $token, $subject, array $attributes)
	{
		if (!$subject instanceof ReferenceInterface) {
			return self::ACCESS_ABSTAIN;
		}

		if ($this->isAlreadyCalled) {
			throw new RuntimeException("Recursive loop detected in " . __CLASS__ . ".");
		}

		$this->isAlreadyCalled = true;
		try {
			// At least one of the transitions must be allowed. This behavior is consistent with
			// Symfony\Component\Security\Core\Authorization\Voter\Voter::voteOnAttribute().
			foreach ($attributes as $attribute) {
				if ($subject->isTransitionAllowed($attribute)) {
					return self::ACCESS_GRANTED;
				}
			}
			return self::ACCESS_DENIED;
		}
		finally{
			$this->isAlreadyCalled = false;
		}
	}

}
