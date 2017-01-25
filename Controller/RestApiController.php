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

namespace Smalldb\SmalldbBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Smalldb\StateMachine\Reference;

/**
 * Implementation of Smalldb-REST API
 *
 * @see https://smalldb.org/smalldb-rest/
 */
class RestApiController extends Controller
{

	public function dumpRequestAction(Request $request, Reference $machine_ref, $action = null)
	{
		//$smalldb = $this->container->get('smalldb');

		$data = [];
		$accept = $request->getAcceptableContentTypes();
		$data['method'] = $request->getMethod();
		$data['accept'] = $accept;
		$data['machine_type'] = $machine_ref->machine_type;
		$data['machine_id'] = $machine_ref->id;
		$data['machine_state'] = $machine_ref->state;
		if ($machine_ref->state) {
			$data['machine_properties'] = $machine_ref->properties;
		}
		$data['available_transitions'] = $machine_ref->machine->getAvailableTransitions($machine_ref);
		$data['action'] = $action;


		$data['use_json_api'] = (isset($accept[0]) && $accept[0] == 'application/json');
		switch ($request->getMethod()) {
			case 'POST': $data['todo'] = 'Invoke transition'; break;
			case 'GET': $data['todo'] = 'Read state'; break;
			default: $data['todo'] = '?'; break;
		}

		$response = new JsonResponse($data, 200);
		$response->setEncodingOptions(JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		return $response;
	}

}

