<?php

namespace Eleanorsoft\Wordpress;
use Eleanorsoft\Docker\NginxConfig\AbstractConfigureNginxInDockerContainer;
use Eleanorsoft\Phar\ArgumentList;
use Eleanorsoft\Phar\CommandAbstract;
use Eleanorsoft\Util;

class ConfigureNginxInDockerContainer extends AbstractConfigureNginxInDockerContainer
{

    public function run(ArgumentList $argumentList)
    {
        $this->configureNginxFromTemplate('default_wordpress.conf', $argumentList);
    }
}