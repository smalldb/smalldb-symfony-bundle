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

use Smalldb\StateMachine\AbstractBackend;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;


/**
 * Bridge between Smalldb IAuth and Symfony authentication.
 */
class SmalldbAuthenticationListener implements ListenerInterface
{
	protected $smalldb;
	protected $tokenStorage;


	/**
	 * Constructor.
	 */
	public function __construct(TokenStorageInterface $tokenStorage, AuthenticationProviderManager $manager, AbstractBackend $smalldb)
	{
		$this->smalldb = $smalldb;
		$this->tokenStorage = $tokenStorage;
	}


	/**
	 * Handle the authentication event.
	 */
	public function handle(GetResponseEvent $event)
	{
		$auth = $this->smalldb->getContext('auth');
		$auth->checkSession();	// FIXME: Duplicate call in DI container

		$session_machine = $auth->getSessionMachine();

		if ($session_machine->state != '') {
			$authToken = new SmalldbToken($session_machine);
			$this->tokenStorage->setToken($authToken);
		}

	}

}

