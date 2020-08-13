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

namespace Smalldb\SmalldbBundle;

use Smalldb\ClassLocator\ComposerClassLocator;
use Smalldb\CodeCooker\Chef;
use Smalldb\CodeCooker\Recipe\ClassRecipe;
use Symfony\Component\Config\Resource\ReflectionClassResource;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;


/**
 * Symfony bundle for Smalldb
 */
class SmalldbBundle extends Bundle
{

	public function build(ContainerBuilder $container)
	{
		parent::build($container);
		$kernelDebug = $container->getParameter('kernel.debug');

		if ($kernelDebug) {
			$baseDir = $container->getParameter('kernel.project_dir');

			$classLocator = new ComposerClassLocator($baseDir, [], [], true);
			$chef = Chef::autoconfigure($classLocator);

			// Register source classes as container resources to reload the container when they change.
			foreach ($chef->getCookbook()->getRecipes() as $recipe) {
				if ($recipe instanceof ClassRecipe) {
					$container->addResource(new ReflectionClassResource($recipe->getSourceClass()));
				}
			}
		}
	}

}

