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
use Smalldb\StateMachine\LogicException;
use Smalldb\StateMachine\MachineIdentifierInterface;
use Smalldb\StateMachine\Smalldb;
use Symfony\Component\Security\Core\User\UserInterface;


class UserProxy implements UserInterface, MachineIdentifierInterface
{

	private ?UserReferenceInterface $ref;
	private string $machineType;
	private $machineId;
	private string $username;
	private array $roles;


	public function __construct(UserReferenceInterface $ref)
	{
		$this->ref = $ref;
		$this->machineType = $ref->getMachineType();
		$this->machineId = $ref->getMachineId();
		$this->username = $ref->getUsername();
		$this->roles = $ref->getRoles();
	}


	public function __sleep()
	{
		// Detach from Smalldb
		return ['machineType', 'machineId', 'username', 'roles'];
	}


	public function reloadReference(Smalldb $smalldb)
	{
		$ref = $smalldb->ref($this->machineType, $this->machineId);
		if ($ref instanceof UserReferenceInterface) {
			$this->ref = $ref;
		} else {
			throw new InvalidArgumentException(UserReferenceInterface::class
				. " reference required, but got " . get_class($ref));
		}
	}


	public function getMachineType(): string
	{
		return $this->machineType;
	}


	public function getMachineId()
	{
		return $this->machineId;
	}


	public function getRef(): UserReferenceInterface
	{
		if ($this->ref) {
			return $this->ref;
		} else {
			throw new LogicException("Reference is not reloaded yet.");
		}
	}


	public function getRoles(): array
	{
		if ($this->ref) {
			return ($this->roles = $this->ref->getRoles());
		} else {
			return $this->roles;
		}
	}


	public function getPassword(): string
	{
		if ($this->ref) {
			return $this->ref->getPassword();
		} else {
			throw new LogicException("Reference is not reloaded yet.");
		}
	}


	public function getSalt(): ?string
	{
		if ($this->ref) {
			return $this->ref->getSalt();
		} else {
			throw new LogicException("Reference is not reloaded yet.");
		}
	}


	public function getUsername(): string
	{
		if ($this->ref) {
			return ($this->username = $this->ref->getUsername());
		} else {
			return $this->username;
		}
	}


	public function eraseCredentials()
	{
		$this->ref = null;
	}

}
