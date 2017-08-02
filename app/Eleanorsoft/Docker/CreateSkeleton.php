<?php

namespace Eleanorsoft\Docker;
use Eleanorsoft\Phar\ArgumentList;
use Eleanorsoft\Phar\CommandAbstract;
use Eleanorsoft\Util;

class CreateSkeleton extends CommandAbstract
{

    const ZIP_URL = 'https://github.com/Eleanorsoft/web-server-docker-template/archive/master.zip';

    protected $options = [
        'name:',
        'port-prefix:',
    ];

    public function run(ArgumentList $argumentList)
    {
        print "Create skeleton ";
        $archive = file_get_contents(self::ZIP_URL);
        if (!$archive) {
            die("Can't download skeleton");
        }

        if (!class_exists('\ZipArchive')) {
            die("Zip extension for PHP not found");
        }

        $filename = uniqid();
        file_put_contents($filename, $archive);
        $zip = new \ZipArchive();
        $res = $zip->open($filename);
        if ($res === true) {
            $path = rtrim($argumentList->get('docker-skeleton-path', './'), '/');
            @mkdir($path, 0777, true);
            $zip->extractTo($path);
            $zip->close();
            @unlink($filename);

            Util::moveDir($path . '/web-server-docker-template-master', $path);

            $dockerComposeConfig = file_get_contents($path . '/docker-compose.yml');
            $dockerComposeConfig = str_replace(
                [
                    '__prefix__',
                    '501',
                    'secret_root_password',
                    '__dbname__',
                    'secret_db_password',
                    '__secret_sftp_password__',
                ],
                [
                    $argumentList->get('docker-skeleton-name', 'noname'),
                    $argumentList->get('docker-skeleton-port-prefix', '501'),
                    Util::getRandomString(),
                    $argumentList->get('docker-skeleton-name', 'noname'),
                    Util::getRandomString(),
                    Util::getRandomString(),
                ],
                $dockerComposeConfig
            );
            file_put_contents($path . '/docker-compose.yml', $dockerComposeConfig);
        } else {
            die("Can't open zip archive " . $res);
        }

    }
}