<?php

namespace Eleanorsoft\Phar;

class CommandChain
{
    /**
     * @var CommandInterface[]|callable[]
     */
    protected $commands = [];

    /**
     * CommandChain constructor.
     * @param CommandInterface[]|callable[] $commands
     */
    public function __construct(array $commands = [])
    {
        foreach ($commands as $cmd) {
            if (is_callable($cmd)) {
                $this->commands[] = $cmd;
            } else {
                $this->commands[] = new $cmd();
            }
        }
    }

    public function run(ArgumentList $argumentList)
    {

        foreach ($this->commands as $command) {
            if (is_callable($command)) {
                $command($argumentList);
            } else {
                $command->run($argumentList);
            }
        }
    }

    public function getCommands()
    {
        return $this->commands;
    }
}