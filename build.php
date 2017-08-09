<?php

/**
 * Build the phar file.
 * Use the following command:
 * php build.php
 *
 * It will create (rewrite) file esutil.phar in the same directory
 *
 * @author Konstantin Esin <kostofffan@gmail.com>
 */

$srcDir = dirname(__FILE__) . '/src';
copy(dirname(__FILE__) . '/config.php', $srcDir . '/config.php');
$phar = new Phar('esutil.phar');
$phar->buildFromDirectory($srcDir, '/\.php$/');
$phar->compressFiles(Phar::GZ);
$phar->stopBuffering();
$phar->setDefaultStub('index.php');