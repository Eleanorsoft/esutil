<?php

namespace Eleanorsoft\Docker;
use Eleanorsoft\Phar\ArgumentList;
use Eleanorsoft\Phar\CommandAbstract;
use Eleanorsoft\Util;

class RunContainer extends CommandAbstract
{

    public function run(ArgumentList $argumentList)
    {
        $path = rtrim($argumentList->get('docker-skeleton-path', './'), '/');
        $projectName = $argumentList->get('docker-skeleton-name', 'noname');

        Util::output("Starting docker containers...\n");
        $currentPath = getcwd();
        if ($currentPath != realpath($path)) {
            chdir(realpath($path));
        }

        $cmd = 'docker-compose build';
        Util::output("Run `$cmd`\n");
        `$cmd`;

        $cmd = 'docker-compose up -d';
        Util::output("Run `$cmd`\n");
        `$cmd`;

        if ($currentPath != realpath($path)) {
            chdir($currentPath);
        }

        Util::output("done\n");
    }
}