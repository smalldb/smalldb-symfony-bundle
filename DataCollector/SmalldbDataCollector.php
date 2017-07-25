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

namespace Smalldb\SmalldbBundle\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Smalldb\StateMachine\AbstractBackend;
use Smalldb\SmalldbBundle\DataCollector\DebugLogger;


class SmalldbDataCollector extends DataCollector
{
	protected $smalldb;

	protected $references_created_count = 0;

	public function __construct(AbstractBackend $smalldb, DebugLogger $debug_logger)
	{
		$this->smalldb = $smalldb;
		$this->debug_logger = $debug_logger;
	}


	public function collect(Request $request, Response $response, \Exception $exception = null)
	{
		$this->data = [
			'backend_class' => get_class($this->smalldb),
			'logger' => $this->debug_logger,
		];
	}


	public function getName()
	{
		return 'smalldb';
	}


	public function __get($key)
	{
		return $this->data[$key];
	}

	public function __isset($key)
	{
		return isset($this->data[$key]);
	}

}

