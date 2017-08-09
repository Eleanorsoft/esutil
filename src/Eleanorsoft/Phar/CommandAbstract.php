<?php

namespace Eleanorsoft\Phar;

class CommandAbstract implements CommandInterface
{
	/**
	 * @var CommandChain
	 */
	protected $commandChain = null;

	protected $options = [];

	public function __construct($commandList = [])
	{
		$this->commandChain = new CommandChain($commandList);
	}

	public function run(ArgumentList $argumentList)
	{
		$chain = $this->commandChain;
		$chain->run($argumentList);
	}

	public function getOptionList()
	{
		$options = [];
		foreach ($this->commandChain->getCommands() as $cmd) {
			$options = array_merge($options, $cmd->getOptionList());
		}
		return array_unique($options);
	}
}