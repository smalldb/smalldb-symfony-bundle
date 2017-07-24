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

namespace Smalldb\SmalldbBundle\Security;

use Smalldb\StateMachine\Reference;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class SmalldbToken extends AbstractToken
{
	protected $session_machine;


	public function __construct(Reference $session_machine)
	{
		$this->session_machine = $session_machine;
		$roles = explode(',', $session_machine->user['roles']);

		parent::__construct($roles);

		$this->setAuthenticated($session_machine->state != '');
	}


	public function getCredentials()
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUsername()
	{
		return $this->session_machine->user_login;
	}

}

