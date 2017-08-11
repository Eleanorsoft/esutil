<?php

namespace Eleanorsoft\Wordpress;
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

        $docroot = rtrim($argumentList->get('wordpress-docroot-path', './'), '/');

        $newOwner = trim($argumentList->get('wordpress-set-owner', '')); // empty to skip
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

        $baseUrl = $argumentList->get('wordpress-base-url', 'http://127.0.0.1:' . $nginxPort);

        @unlink($docroot . 'index.php');

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



        $installCmd = sprintf('wget http://wordpress.org/latest.tar.gz ' .
            '&& tar xfz latest.tar.gz ' .
            '&& mv wordpress/* ./ ' .
            '&& rmdir ./wordpress/ ' .
            '&& rm -f latest.tar.gz ' .
            '&& mv wp-config-sample.php wp-config.php ' .
            '&& sed -i s/database_name_here/%s/ wp-config.php ' .
            '&& sed -i s/username_here/%s/ wp-config.php ' .
            '&& sed -i s/password_here/%s/ wp-config.php ' .
            '&& echo "define(\'FS_METHOD\', \'direct\');" >> wp-config.php ' .
            '&& curl "%s/wp-admin/install.php?step=2" ' .
                '--data-urlencode "weblog_title=%s" ' .
                '--data-urlencode "user_name=%s" ' .
                '--data-urlencode "admin_email=%s" ' .
                '--data-urlencode "admin_password=%s" ' .
                '--data-urlencode "admin_password2=%s" ' .
                '--data-urlencode "pw_weak=1"',
            $projectName,
            $projectName,
            $mysqlPassword,
            $baseUrl,
            $projectName,
            $adminName,
            'noreply@unknown.host',
            $adminPassword,
            $adminPassword
        );

        $escapedCmd = str_replace(['"', "\n"], ['\\\\"', ' '], $installCmd);

        $cmd = "docker exec -it --user 33 $containerId sh -c \"$escapedCmd\"";
        Util::output("Run `$cmd`\n");
        `$cmd`;

        if ($currentPath != realpath($path)) {
            chdir($currentPath);
        }

        Util::output("done\n");

        Util::output("\n\n" . str_repeat('*', 30) . "\n\n");

        Util::output(sprintf(
            "Admin Panel:\n\tURL: %s\n\tLogin: %s\n\tPassword: %s\n\n",
            "{$baseUrl}/wp-admin/",
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