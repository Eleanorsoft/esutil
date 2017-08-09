<?php
namespace Eleanorsoft;

/**
 * Class Util
 *
 * @package Eleanorsoft Utility
 * @author Konstantin Esin <kostofffan@gmail.com>
 * @since 1.0.0
 */
class Util
{
    public static function copyDir($src, $dst)
    {
        $dst = rtrim($dst, '/') . '/';
        @mkdir($dst, 0777, true);
        $di = new \DirectoryIterator($src);
        if ($di) {
            foreach ($di as $f) {
                if (!$f->isDot()) {
                    if ($f->isDir()) {
                        @mkdir($dst . $f->getBasename());
                        self::copyDir($f->getPathname(), $dst . $f->getBasename());
                    } else {
                        copy($f->getPathname(), $dst . $f->getBasename());
                    }
                }
            }
        } else {
            throw new \Exception("Can't read $src");
        }
    }

    public static function moveDir($src, $dst)
    {
        $dst = rtrim($dst, '/') . '/';
        @mkdir($dst, 0777, true);
        $di = new \DirectoryIterator($src);
        if ($di) {
            foreach ($di as $f) {
                if (!$f->isDot()) {
                    if ($f->isDir()) {
                        @mkdir($dst . $f->getBasename());
                        self::moveDir($f->getPathname(), $dst . $f->getBasename());
                    } else {
                        rename($f->getPathname(), $dst . $f->getBasename());
                    }
                }
            }
            rmdir($src);
        } else {
            throw new \Exception("Can't read $src");
        }
    }

    public static function getRandomString($length = 10, $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ@*^%#()<>')
    {
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function output($msg)
    {
        print $msg;
    }

    public static function log($msg)
    {
        print '[log] ' . $msg . "\n";
    }

    /**
     * Is the application working on Windows
     * @return bool
     */
    public static function isWindows()
    {
        return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
    }
}