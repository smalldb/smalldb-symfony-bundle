<?php
/*
 * Copyright (c) 2018, Josef Kufner  <josef@kufner.cz>
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


use Smalldb\StateMachine\Smalldb;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;


class ProfilerController implements ContainerAwareInterface
{
	use ContainerAwareTrait;


	public function machineAction(string $token, string $machine_type = null)
	{
		$smalldb = $this->container->get('smalldb');

		$machine = $smalldb->getMachine($machine_type);
		$statechart = $machine->exportJson(true);

		return new Response($this->container->get('twig')->render('@Smalldb/data_collector/machine.html.twig', array(
			'token' => $token,
			'machine_type' => $machine_type,
			'machine' => $machine,
			'statechart' => $statechart,
			'grafovatko_js' => file_get_contents(__DIR__.'/../Resources/grafovatko.js/grafovatko.min.js'), // FIXME
		)));
	}


	public function grafovatkoAction()
	{
		return new BinaryFileResponse(__DIR__.'/../Resources/grafovatko.js/grafovatko.min.js');
	}

}