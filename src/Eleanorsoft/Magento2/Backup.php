<?php

namespace Eleanorsoft\Magento2;
use Eleanorsoft\Phar\Argument\Formatter\BooleanFormatter;
use Eleanorsoft\Phar\Argument\Formatter\PathFormatter;
use Eleanorsoft\Phar\ArgumentList;
use Eleanorsoft\Phar\CommandAbstract;
use Eleanorsoft\Util;


/**
 * Class Backup
 * Backup magento2 project
 *
 * @package EleanorsoftUtil
 * @author Konstantin Esin <hello@eleanorsoft.com>
 * @copyright Copyright (c) 2017 Eleanorsoft (https://www.eleanorsoft.com/)
 */
class Backup extends CommandAbstract
{
    public function run(ArgumentList $argumentList)
    {
        $path = $argumentList->get('magento2-root-path', './', [PathFormatter::class]);

        $backupFiles = $argumentList->get('magento2-backup-files', 'y', [BooleanFormatter::class]);

        // backup files
        if ($backupFiles) {
            $backupMedia = $argumentList->get('magento2-backup-media-dir', 'n', [BooleanFormatter::class]);
            $backupVendor = $argumentList->get('magento2-backup-vendor-dir', 'n', [BooleanFormatter::class]);
            $outputFile = $argumentList->get('magento2-backup-output-tar-file', '../mag' . date('Ymd') . '.tar.gz');

            $include = [
                '/*.*',
                '/.htaccess',
                '/.htaccess.sample',
                '/.php_cs',
                '/.travis.yml',
                '/.user.ini',
                '/.gitignore',
                '/app/',
                '/bin/',
                '/dev/',
                '/lib/',
                '/phpserver/',
                '/setup/',
                '/update/',
                '/var/.htaccess',
                '/pub/errors/',
                '/pub/.htaccess',
                '/pub/.user.ini',
                '/pub/*.php',
                '/pub/opt/',
            ];
            if ($backupMedia) {
                $include[] = '/pub/media/';
            }
            if ($backupVendor) {
                $include[] = '/vendor/';
            }

            $includeString = $path . implode(" $path", $include);

            $cmd = "tar -zcvf $outputFile $includeString";
            `$cmd`;

            Util::output("Backed up files: $outputFile\n");
        }

        $backupDatabase = $argumentList->get('magento2-backup-database', 'y', [BooleanFormatter::class]);

        // backup database
        if ($backupDatabase) {
            $outputFile = $argumentList->get('magento2-backup-output-db-file', '../magdb' . date('Ymd') . '.sql.gz');
            $config = include("$path/app/etc/env.php");
            $dbName = $config['db']['connection']['default']['dbname'];
            $dbUser = $config['db']['connection']['default']['username'];
            $dbPass = $config['db']['connection']['default']['password'];
            $dbHost = $config['db']['connection']['default']['host'];

            if (!$dbName or !$dbPass or !$dbUser or !$dbHost) {
                throw new \Exception("Can't read config (empty values)");
            }

            $cmd = "mysqldump -u $dbUser -p$dbPass -h$dbHost $dbName | gzip > $outputFile";
            `$cmd`;

            Util::output("Backed up DB: $outputFile\n");
        }
    }
}