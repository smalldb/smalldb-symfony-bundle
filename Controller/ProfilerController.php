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


use Doctrine\Common\Annotations\AnnotationReader;
use Smalldb\SmalldbBundle\DataCollector\SmalldbDataCollector;
use Smalldb\StateMachine\Definition\Renderer\StateMachineExporter;
use Smalldb\StateMachine\Smalldb;
use Symfony\Component\Debug\Debug;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profiler;


class ProfilerController implements ContainerAwareInterface
{
	use ContainerAwareTrait;


	public function overviewAction(string $token)
	{
		return new Response($this->container->get('twig')->render('@Smalldb/data_collector/overview.html.twig', array(
			'grafovatko_js' => file_get_contents(__DIR__.'/../Resources/grafovatko.js/grafovatko.min.js'), // FIXME
		)));
	}

	public function machineAction(string $token, Request $request)
	{
		$machineType = $request->attributes->get('machine');

		/** @var Profiler $profiler */
		$profiler = $this->container->get('profiler');
		$profile = $profiler->loadProfile($token);
		/** @var SmalldbDataCollector $collector */
		$collector = $profile->getCollector('smalldb');

		$definition = $collector->getDefinition($machineType);

		$exporter = new StateMachineExporter($definition);

		return new Response($this->container->get('twig')->render('@Smalldb/data_collector/machine.html.twig', array(
			'token' => $token,
			'panel' => $request->attributes->get('panel'),
			'machineType' => $machineType,
			'collector' => $collector,
			'definition' => $definition,
			'stateChartExporter' => $exporter,
			'grafovatko_js' => file_get_contents(__DIR__.'/../Resources/grafovatko.js/grafovatko.min.js'), // FIXME
		)));
	}


	public function grafovatkoAction()
	{
		return new BinaryFileResponse(__DIR__.'/../Resources/grafovatko.js/grafovatko.min.js');
	}

}
