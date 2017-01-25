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

	protected function jsonResponse($data, $http_code = 200)
	{
		$response = new JsonResponse($data, 200);
		$response->setEncodingOptions(JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		return $response;
	}


	protected function jsonException(\Exception $ex)
	{
		$class = get_class($ex);
		switch ($class) {
			case 'Smalldb\\StateMachine\\TransitionAccessException':
				$http_code = '403';
				break;
			case 'Smalldb\\StateMachine\\InstanceDoesNotExistException':
				$http_code = '404';
				break;
			default:
				$http_code = '500';
				break;
		}

                $data = array(
                        'exception' => get_class($ex),
                        'message' => $ex->getMessage(),
                        'code' => $ex->getCode(),
                );

		return $this->jsonResponse($data, $http_code);
	}


	public function readStateAction(Request $request, Reference $machine_ref)
	{
		try {
			$view_list = $request->query->keys();
			if (empty($view_list)) {
				// Read state and properties
				return $this->jsonResponse([
					'id' => $machine_ref->id,
					'properties' => $machine_ref->properties,
					'state' => $machine_ref->state,
				]);
			} else {
				// Read views
				$response = [
					'id' => $machine_ref->id,
				];
				foreach ($view_list as $view) {
					$response[$view] = $machine_ref->$view;
				}
				return $this->jsonResponse($response);
			}
		}
		catch(\Exception $ex) {
			return $this->jsonException($ex);
		}
	}


	public function listingAction(Request $request)
	{
		try {
			$filters = $request->query->all();
			$listing = $this->container->get('smalldb')->createListing($filters);
			return $this->jsonResponse([
				'items' => array_values($listing->fetchAll()),  // Order of object's properties not guaranteed in JS
				'processed_filters' => $listing->getProcessedFilters(),
			]);

		}
		catch(\Exception $ex) {
			return $this->jsonException($ex);
		}
	}


	public function invokeTransitionAction(Request $request, Reference $machine_ref, $action)
	{
		$args = $request->request->get('args');

		try {
			if ($request->getMethod() == 'POST') {
				return $this->jsonResponse([
					'id' => $machine_ref->id,
					'action' => $action,
					'result' => call_user_func_array(array($machine_ref, $action), empty($args) ? [] : $args),
				]);
			} else {
				return $this->jsonResponse([
					'id' => $machine_ref->id,
					'action' => $action,
					'allowed' => $machine_ref->machine->isTransitionAllowed($machine_ref, $action),
				]);
			}
		}
		catch(\Exception $ex) {
			return $this->jsonException($ex);
		}
	}


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

