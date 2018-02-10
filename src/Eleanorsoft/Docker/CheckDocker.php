<?php

namespace Eleanorsoft\Docker;
use Eleanorsoft\Phar\ArgumentList;
use Eleanorsoft\Phar\CommandAbstract;
use Eleanorsoft\Util;

/**
 * Class CheckDocker
 * Check if docker is installed
 *
 * @package Eleanorsoft\Docker
 */
class CheckDocker extends CommandAbstract
{

    public function run(ArgumentList $argumentList)
    {
        $a = `docker --version`;
        if (!$a) {
            die("Please, install docker first.\n");
        }

        $a = `docker ps`;
        if (!$a) {
            die("Please, run utility as root (sudo).\n");
        }
    }
}