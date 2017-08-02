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
            $this->commands[] = new $cmd();
        }
    }

    public function run(ArgumentList $argumentList)
    {

        foreach ($this->commands as $command) {
            if (is_object($command)) {
                $command->run($argumentList);
            } else {
                $command($argumentList);
            }
        }
    }

    public function getCommands()
    {
        return $this->commands;
    }
}