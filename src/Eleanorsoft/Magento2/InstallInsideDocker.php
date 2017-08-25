<?php

namespace Eleanorsoft\Magento2;
use Eleanorsoft\Phar\ArgumentList;
use Eleanorsoft\Phar\CommandAbstract;
use Eleanorsoft\Util;

class InstallInsideDocker extends CommandAbstract
{

    public function run(ArgumentList $argumentList)
    {
        $path = rtrim($argumentList->get('docker-skeleton-path', './'), '/');
        $tmp = explode('/', str_replace(DIRECTORY_SEPARATOR, '/', realpath($path)));
        $lastDir = array_pop($tmp);
        if (!$lastDir) {
            $lastDir = array_pop($tmp);
        }
        $lastDir = preg_replace('/\W/', '', $lastDir);

        $projectName = $argumentList->get('docker-skeleton-name', 'noname');
        $adminName = $projectName . 'admin';
        $adminPassword = $this->generateAdminPassword();

        $portPrefix = $argumentList->get('docker-skeleton-port-prefix', '501');
        $nginxPort = $portPrefix . '1';

        $mysqlPassword = $argumentList->get('docker-skeleton-mysql-password');

        $baseUrl = $argumentList->get('magento2-base-url', 'http://127.0.0.1:' . $nginxPort);

        $phpContainerName = implode(
            '_',
            [
                $lastDir,
                $projectName,
                'php'
            ]
        );
        $currentPath = getcwd();
        if ($currentPath != realpath($path)) {
            chdir(realpath($path));
        }

        $output = `docker ps`;
        $outputRows = explode("\n", $output);
        $containerId = null;
        foreach ($outputRows as $row) {
            if (stripos($row, $phpContainerName) !== false) {
                $rowTmp = preg_split('/\s+/', $row);
                $containerId = array_shift($rowTmp);
                break;
            }
        }

        if (!$containerId) {
            die("Can't find container id for $phpContainerName");
        }

        // docker may take some time to start... we have to wait in case of an error
        $cmd = "docker exec -it --user 33 $containerId sh -c \"php bin/magento setup:install --admin-firstname='John' --admin-lastname='Doe' --admin-email='hello@eleanorsoft.com' --admin-user='$adminName' --admin-password='$adminPassword' --base-url='$baseUrl' --backend-frontname='$adminName' --db-host='{$projectName}_mysql' --db-name='{$projectName}' --db-user='{$projectName}' --db-password='$mysqlPassword' --use-rewrites=1 --language=en_US --currency=USD --timezone=America/Chicago\"";
        do {
            Util::output("Run `$cmd`\n");
            passthru($cmd, $err);
            if ($err) {
                Util::output("Got error. Wait for 5 seconds...\n");
                sleep(5);
            }
        } while ($err);

        if ($currentPath != realpath($path)) {
            chdir($currentPath);
        }

        Util::output("done\n");

        Util::output("\n\n" . str_repeat('*', 30) . "\n\n");

        Util::output(sprintf(
            "Admin Panel:\n\tURL: %s\n\tLogin: %s\n\tPassword: %s\n\n",
            "{$baseUrl}{$adminName}",
            $adminName,
            $adminPassword
        ));

        Util::output("\n\n" . str_repeat('*', 30) . "\n\n");
    }

    /**
     * Generate Magento admin password
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