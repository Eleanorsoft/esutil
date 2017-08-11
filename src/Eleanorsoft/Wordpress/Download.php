<?php

namespace Eleanorsoft\Wordpress;
use Eleanorsoft\Phar\ArgumentList;
use Eleanorsoft\Phar\CommandAbstract;
use Eleanorsoft\Util;

class Download extends CommandAbstract
{

    public function run(ArgumentList $argumentList)
    {
        Util::output("Download Wordpress... ");

        if (!class_exists('\Locale')) {
            die("intl extension for PHP not found");
        }

        if (!class_exists('\ZipArchive')) {
            die("Zip extension for PHP not found");
        }

        $url = 'http://wordpress.org/latest.zip';
        $archive = file_get_contents($url);
        $filename = uniqid();
        file_put_contents($filename, $archive);

        $zip = new \ZipArchive();
        $res = $zip->open($filename);
        if ($res === true) {
            $path = rtrim($argumentList->get('wordpress-docroot-path', './'), '/');
            @mkdir($path, 0777, true);
            $zip->extractTo($path);
            $zip->close();
            @unlink($filename);
            Util::moveDir($path . '/wordpress', $path);
            Util::output("done\n");
        } else {
            die("Can't open zip archive " . $res);
        }
    }
}