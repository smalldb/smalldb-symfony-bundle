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


use DateTimeImmutable;
use Smalldb\SmalldbBundle\DataCollector\SmalldbDataCollector;
use Smalldb\StateMachine\BpmnExtension\Definition\BpmnExtension;
use Smalldb\StateMachine\BpmnExtension\GrafovatkoProcessor;
use Smalldb\StateMachine\BpmnExtension\SvgPainter;
use Smalldb\StateMachine\Definition\Renderer\StateMachineExporter;
use Smalldb\Graph\Grafovatko\GrafovatkoExporter;
use Smalldb\StateMachine\Smalldb;
use Smalldb\StateMachine\SourcesExtension\Definition\SourcesExtension;
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
		$grafovatkoAttrs = [
			"class" => "grafovatko",
			"style" => "margin: auto; display: block; overflow: visible;"
		];

		$machineType = $request->attributes->get('machine');

		/** @var Profiler $profiler */
		$profiler = $this->container->get('profiler');
		$profile = $profiler->loadProfile($token);
		/** @var SmalldbDataCollector $collector */
		$collector = $profile->getCollector('smalldb');

		if ($collector->hasDefinitions()) {
			$definition = $collector->getDefinition($machineType);
			$machineTypes = array_keys($collector->getDefinitions());
		} else {
			/** @var Smalldb $smalldb */
			$smalldb = $this->container->get(Smalldb::class);
			$definition = $smalldb->getDefinition($machineType);
			$machineTypes = $smalldb->getMachineTypes();
		}

		$exporter = new StateMachineExporter($definition);
		$stateChart = $exporter->exportSvgElement($grafovatkoAttrs);

		$sourceDiagrams = [];

		if ($definition->hasExtension(BpmnExtension::class)) {
			/** @var BpmnExtension $ext */
			$ext = $definition->getExtension(BpmnExtension::class);
			$diagramInfoList = $ext->getDiagramInfo();
			foreach ($diagramInfoList as $diagramInfo) {
				$bpmnGraph = $diagramInfo->getBpmnGraph();

				$svgFileName = $diagramInfo->getSvgFileName();
				if ($svgFileName && file_exists($svgFileName)) {
					$svgContent = file_get_contents($svgFileName);
					$svgPainter = new SvgPainter();
					$colorizedSvgContent = $svgPainter->colorizeSvgFile($svgContent, $bpmnGraph, $diagramInfo->getTargetParticipant(), [], md5($svgFileName));
					$sourceDiagrams[] = [
						"heading" => basename($diagramInfo->getBpmnFileName()) . " (BPMN as colorized SVG)",
						"svg" => $colorizedSvgContent,
					];
				}

				$renderer = new GrafovatkoExporter($bpmnGraph);
				$renderer->setPrefix(md5($diagramInfo->getBpmnFileName()));
				$renderer->addProcessor(new GrafovatkoProcessor($diagramInfo->getTargetParticipant()));
				$sourceDiagrams[] = [
					"heading" => basename($diagramInfo->getBpmnFileName()) . " (BPMN as interpreted)",
					"svg" => $renderer->exportSvgElement($grafovatkoAttrs),
				];
			}
		}

		if ($definition->hasExtension(SourcesExtension::class)) {
			/** @var SourcesExtension $sourcesExt */
			$sourcesExt = $definition->getExtension(SourcesExtension::class);
			$sourceFiles = $sourcesExt->getSourceFiles();
		} else {
			$sourceFiles = null;
		}

		return new Response($this->container->get('twig')->render('@Smalldb/data_collector/machine.html.twig', array(
			'token' => $token,
			'panel' => $request->attributes->get('panel'),
			'machineType' => $machineType,
			'machineTypes' => $machineTypes,
			'definition' => $definition,
			'sourceFiles' => $sourceFiles,
			'stateChart' => $stateChart,
			'sourceDiagrams' => $sourceDiagrams,
			'mtime' => (new DateTimeImmutable())->setTimestamp($definition->getMTime()),
			'grafovatko_js' => file_get_contents(__DIR__.'/../Resources/grafovatko.js/grafovatko.min.js'), // FIXME
		)));
	}


	public function grafovatkoAction()
	{
		return new BinaryFileResponse(__DIR__.'/../Resources/grafovatko.js/grafovatko.min.js');
	}

}
