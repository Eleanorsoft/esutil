<?php

namespace Eleanorsoft\Docker\DockerComposeCofnig;

use Eleanorsoft\Phar\CommandInterface;
use Eleanorsoft\Util;

class Prepare implements CommandInterface
{

    public function run()
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
            $zip->extractTo('./');
            $zip->close();
            @unlink($filename);

            Util::moveDir('web-server-docker-template-master', '.');
        } else {
            die("Can't open zip archive " . $res);
        }

    }
}