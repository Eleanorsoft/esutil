<?php

namespace Eleanorsoft\Wordpress;
use Eleanorsoft\Phar\Argument\Formatter\PathFormatter;
use Eleanorsoft\Phar\ArgumentList;
use Eleanorsoft\Phar\CommandAbstract;
use Eleanorsoft\Util;

/**
 * Class CreateWpConfig
 * Create file wp-config.php and put required info there.
 *
 * @package Eleanorsoft\Wordpress
 */
class CreateWpConfig extends CommandAbstract
{

    public function run(ArgumentList $argumentList)
    {
        Util::output("Creating wp-config.php... ");

        $path = $argumentList->get('docker-skeleton-path', './', [PathFormatter::class]);
        $dbPass = $argumentList->get('docker-skeleton-mysql-password');
        $docroot = $path . '/www/html';
        $projectName = $argumentList->get('docker-skeleton-name', 'noname');

        rename($docroot . '/wp-config-sample.php', $docroot . '/wp-config.php');
        $config = file_get_contents($docroot . '/wp-config.php');
        $config = str_replace(
            ['database_name_here', 'username_here', 'password_here', 'localhost'],
            [$projectName, $projectName, $dbPass, $projectName . '_mysql'],
            $config
        );
        while (stripos($config, 'put your unique phrase here') !== false) {
            $config = preg_replace('/put your unique phrase here/', Util::getRandomString(30), $config);
        }
        $config = "<?php define('FS_METHOD', 'direct'); ?>" . $config;

        file_put_contents($docroot . '/wp-config.php', $config);

        Util::output("done\n");
    }
}