<?php

namespace Eleanorsoft\Docker;
use Eleanorsoft\Phar\Argument\Formatter\PathFormatter;
use Eleanorsoft\Phar\ArgumentList;
use Eleanorsoft\Phar\CommandAbstract;
use Eleanorsoft\Util;

/**
 * Class SetDocrootOwner
 * Change owner of the docroot. Leave it empty to skip this step
 *
 * @package Eleanorsoft\Docker
 */
class SetDocrootOwner extends CommandAbstract
{

    public function run(ArgumentList $argumentList)
    {
        $path = $argumentList->get('docker-skeleton-path', './', [PathFormatter::class]);
        $docroot = $path . '/www/html';

        $newOwner = $argumentList->get('docker-docroot-owner', ArgumentList::EMPTY_TO_SKIP);
        if ($newOwner) {
            if (Util::isWindows()) {
                Util::output("Skip chown as working on Windows\n");
            } else {
                `chown -R $newOwner $docroot`;
            }
        }
    }
}