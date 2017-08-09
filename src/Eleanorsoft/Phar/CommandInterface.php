<?php

namespace Eleanorsoft\Phar;

interface CommandInterface
{
	public function run(ArgumentList $args);
	public function getOptionList(); // todo: remove
}