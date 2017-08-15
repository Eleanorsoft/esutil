<?php

namespace Eleanorsoft\Http;
use Eleanorsoft\Phar\ArgumentList;
use Eleanorsoft\Phar\CommandAbstract;
use Eleanorsoft\Util;

/**
 * Class WaitForWebserverToGetOnline
 * Wait while webserver get back online.
 *
 * @package Eleanorsoft\Http
 */
class WaitForWebserverToGetOnline extends CommandAbstract
{

    public function run(ArgumentList $argumentList)
    {
        $baseUrl = $argumentList->get('webserver-url', ArgumentList::EMPTY_TO_SKIP);
        if ($baseUrl) {
            while (!@file_get_contents($baseUrl)) {
                Util::output("Can't connect to $baseUrl. Waiting for 5 seconds...\n");
                sleep(5);
            }
            Util::output("Webserver $baseUrl is online\n");
        }
    }
}