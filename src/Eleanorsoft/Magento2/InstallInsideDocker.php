<?php

namespace Eleanorsoft\Magento2;
use Eleanorsoft\Phar\ArgumentList;
use Eleanorsoft\Phar\CommandAbstract;
use Eleanorsoft\Util;

class InstallInsideDocker extends CommandAbstract
{

    public function run(ArgumentList $argumentList)
    {
        Util::output("Run container... \n");

        $path = rtrim($argumentList->get('docker-skeleton-path', './'), '/');
        $tmp = explode('/', str_replace(DIRECTORY_SEPARATOR, '/', realpath($path)));
        $lastDir = array_pop($tmp);
        if (!$lastDir) {
            $lastDir = array_pop($tmp);
        }
        $lastDir = preg_replace('/\W/', '', $lastDir);

        $docroot = rtrim($argumentList->get('magento2-docroot-path', './'), '/');

        $newOwner = trim($argumentList->get('magento2-set-owner', '')); // empty to skip
        if ($newOwner) {
            if (Util::isWindows()) {
                Util::output("Skip chown as working on Windows\n");
            } else {
                `chown -R $newOwner $docroot`;
            }
        }

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

        $cmd = 'docker-compose up -d';
        Util::output("Run `$cmd`\n");
        `$cmd`;

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

        $cmd = "docker exec -it --user 33 $containerId sh -c \"php bin/magento setup:install --admin-firstname=John --admin-lastname=Doe --admin-email=hello@eleanorsoft.com --admin-user=$adminName --admin-password=$adminPassword --base-url=$baseUrl --backend-frontname=$adminName --db-host={$projectName}_mysql --db-name={$projectName} --db-user={$projectName} --db-password=$mysqlPassword --use-rewrites=1 --language=en_US --currency=USD --timezone=America/Chicago\"";
        Util::output("Run `$cmd`\n");
        `$cmd`;

        if ($currentPath != realpath($path)) {
            chdir($currentPath);
        }

        Util::output("done\n");
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