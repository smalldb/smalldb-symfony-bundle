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

namespace Smalldb\SmalldbBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Smalldb\StateMachine\Smalldb;
use Smalldb\StateMachine\Compiler\ReferenceFactoryCompiler;


/**
 * Compile generated Smalldb classes
 */
class CompileSmalldbCommand extends Command
{
	private $smalldb;
	private $dest_dir;


	/**
	 * CompileSmalldbCommand constructor.
	 */
	public function __construct(Smalldb $smalldb, string $dest_dir)
	{
		parent::__construct();
		$this->smalldb = $smalldb;
		$this->dest_dir = $dest_dir;
	}


	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this
			->setName('smalldb:compile')
			->setDescription('Compiles all generated Smalldb classes.')
		;
	}


	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$compiler = new ReferenceFactoryCompiler($this->smalldb);

		if (!file_exists($this->dest_dir)) {
			mkdir($this->dest_dir);
		}
		$compiler->compile($this->dest_dir);
	}

}
