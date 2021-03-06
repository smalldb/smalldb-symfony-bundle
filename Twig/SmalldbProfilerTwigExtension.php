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

namespace Smalldb\SmalldbBundle\Twig;

use Smalldb\StateMachine\Definition\ExtensibleDefinition;
use Smalldb\StateMachine\StyleExtension\Definition\StyleExtension;
use Symfony\Component\VarDumper\Caster\ClassStub;
use Symfony\Component\VarDumper\Cloner\Data;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;


class SmalldbProfilerTwigExtension extends AbstractExtension
{

	public function getFunctions()
	{
		return [
			// TODO: Replace this function with a filter that dumps the class name nicely
			new TwigFunction('fqcn', class_exists(ClassStub::class)
				? fn(?string $fqcn) => new Data([0 => [0 => $fqcn === null ? null : new ClassStub($fqcn)]])
				: fn($fqcn) => $fqcn),
			new TwigFunction('get_class', fn($obj) => (fn() => get_class($this))->call($obj)),
			new TwigFunction('get_object_vars', fn($obj) => (fn() => get_object_vars($this))->call($obj)),
			new TwigFunction('styleExt', function(ExtensibleDefinition $definition): ?StyleExtension {
				if ($definition->hasExtension(StyleExtension::class)) {
					/** @var StyleExtension $ext */
					$ext = $definition->getExtension(StyleExtension::class);
					return $ext;
				} else {
					return null;
				}
			})
		];
	}

}
