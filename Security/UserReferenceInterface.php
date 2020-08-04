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


/**
 * Interface UserReferenceInterface
 *
 * This interface is a subset of Symfony's UserInterface.
 *
 * @see \Symfony\Component\Security\Core\User\UserInterface
 */
interface UserReferenceInterface extends ReferenceInterface
{

	/** @return string[] */
	public function getRoles(): array;

	public function getPassword(): ?string;

	public function getSalt(): ?string;

	public function getUsername(): string;

}
