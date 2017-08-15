<?php

namespace Eleanorsoft\Wordpress;
use Eleanorsoft\Phar\ArgumentList;
use Eleanorsoft\Phar\CommandAbstract;
use Eleanorsoft\Util;

/**
 * Class InstallViaHttp
 * Run Wordpress installation using HTTP request
 *
 * @package Eleanorsoft\Wordpress
 */
class InstallViaHttp extends CommandAbstract
{

    public function run(ArgumentList $argumentList)
    {
        Util::output("Install Wordpress via HTTP... ");
        $portPrefix = $argumentList->get('docker-skeleton-port-prefix');
        $domain = $argumentList->get('wordpress-domain'); // without protocol

        $projectName = $argumentList->get('docker-skeleton-name', 'noname');
        $nginxPort = $portPrefix . '1';

        $adminName = $projectName . 'admin';
        $adminPassword = $this->generateAdminPassword();
        $argumentList->set('worpress-admin-name', $adminName);
        $argumentList->set('worpress-admin-password', $adminPassword);

        $url = sprintf('http://%s:%s/wp-admin/install.php?step=2', $domain, $nginxPort);

        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query([
                    'weblog_title' => $projectName,
                    'user_name' => $adminName,
                    'admin_email' => 'noreply@gmail.com',
                    'admin_password' => $adminPassword,
                    'admin_password2' => $adminPassword,
                    'pw_weak' => 'on',
                    'language' => '',
                    'pass1-text' => $adminPassword,
                    'Submit' => 'Install WordPress',
                ]),
                'timeout' => 60
            )
        );
        $context  = stream_context_create($opts);

        file_get_contents($url, false, $context, -1, 40000);

        Util::output("done\n");
    }

    /**
     * Generate Wordpress admin password
     * It must contain at least one alpha, digit and special char.
     * @return string
     */
    protected function generateAdminPassword()
    {
        return
            Util::getRandomString(1, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') .
            Util::getRandomString(1, '0123456789') .
            Util::getRandomString(1, '@*^%#()<>') .
            Util::getRandomString();
    }
}