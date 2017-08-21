<?php

namespace Eleanorsoft\Magento2;
use Eleanorsoft\Phar\ArgumentList;
use Eleanorsoft\Phar\CommandAbstract;
use Eleanorsoft\Util;

class Download extends CommandAbstract
{

    public function run(ArgumentList $argumentList)
    {
        Util::output("Download Magento 2... ");

        if (!class_exists('\Locale')) {
            die("intl extension for PHP not found");
        }

        $installer = file_get_contents('https://getcomposer.org/installer');
        $path = rtrim($argumentList->get('magento2-composer-path', './'), '/');
        $docroot = rtrim($argumentList->get('magento2-docroot-path', './'), '/');
        file_put_contents($path . '/installer', $installer);
        $currentPath = getcwd();
        chdir($path);
        `php installer`;
        chdir($currentPath);
        @unlink($path . '/installer');

        `php $path/composer.phar create-project --repository-url=https://repo.magento.com/ magento/project-community-edition $docroot`;

        Util::output("done\n");
    }
}