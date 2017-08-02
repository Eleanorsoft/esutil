<?php
$phar = new Phar('esutil.phar');
$phar->buildFromDirectory(dirname(__FILE__) . '/app','/\.php$/');
$phar->compressFiles(Phar::GZ);
$phar->stopBuffering();
$phar->setDefaultStub('index.php');