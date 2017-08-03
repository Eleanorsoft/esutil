<?php

namespace Eleanorsoft\Magento2;
use Eleanorsoft\Phar\ArgumentList;
use Eleanorsoft\Phar\CommandAbstract;
use Eleanorsoft\Util;

class ConfigureNginxInDockerContainer extends CommandAbstract
{

    public function run(ArgumentList $argumentList)
    {
        Util::output("Configure Nginx... ");
        $path = rtrim($argumentList->get('docker-skeleton-path', './'), '/');
        $nginxConfigFile = $path . '/nginx/default.conf';
        $renameResult = rename($path . '/nginx/default_magento.conf', $nginxConfigFile);
        if (!$renameResult) {
            die("Can't create nginx config");
        }
        $nginxConfig = file_get_contents($nginxConfigFile);
        $nginxConfig = str_replace(
            [
                '__prefix__',
                '501',
            ],
            [
                $argumentList->get('docker-skeleton-name', 'noname'),
                $argumentList->get('docker-skeleton-port-prefix', '501'),
            ],
            $nginxConfig
        );
        file_put_contents($nginxConfigFile, $nginxConfig);
        Util::output("done\n");
    }
}