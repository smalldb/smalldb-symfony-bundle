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
use Smalldb\StateMachine\Smalldb;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;


class SmalldbUserProvider implements UserProviderInterface
{
	/** @var UserRepositoryInterface[] */
	private array $userRepositories;
	private Smalldb $smalldb;


	public function setSmalldb(Smalldb $smalldb): void
	{
		$this->smalldb = $smalldb;
	}


	public function addUserRepositories(iterable $userRepositories)
	{
		foreach ($userRepositories as $userRepository) {
			$this->userRepositories[] = $userRepository;
		}
	}


	public function loadUserByUsername(string $username): UserProxy
	{
		if (empty($this->userRepositories)) {
			throw new \RuntimeException("User repository not found. "
				. "Configure a service implementing " . UserRepositoryInterface::class . ".");
		}

		foreach ($this->userRepositories as $userRepository) {
			$user = $userRepository->findByUsername($username);
			if ($user instanceof UserReferenceInterface && $user->getState() !== ReferenceInterface::NOT_EXISTS) {
				return new UserProxy($user);
			}
		}
		throw new UsernameNotFoundException("User not found: " . $username);
	}


	public function refreshUser(UserInterface $user)
	{
		if ($user instanceof UserProxy) {
			$user->reloadReference($this->smalldb);
			return $user;
		} else {
			throw new UnsupportedUserException("Unsupported user object: " . get_class($user));
		}
	}


	public function supportsClass(string $class)
	{
		return $class === UserProxy::class;
	}

}
