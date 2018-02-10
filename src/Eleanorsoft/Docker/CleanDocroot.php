<?php

namespace Eleanorsoft\Docker;
use Eleanorsoft\Phar\Argument\Formatter\PathFormatter;
use Eleanorsoft\Phar\ArgumentList;
use Eleanorsoft\Phar\CommandAbstract;
use Eleanorsoft\Util;

/**
 * Class CleanDocroot
 * Remove test files from docroot (www/html)
 *
 * @package Eleanorsoft\Docker
 */
class CleanDocroot extends CommandAbstract
{

    public function run(ArgumentList $argumentList)
    {
        $path = $argumentList->get('docker-skeleton-path', './', [PathFormatter::class]);
        @unlink($path . '/www/html/index.php');
    }
}