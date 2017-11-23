<?php

namespace Eleanorsoft\Magento1;
use Eleanorsoft\Phar\Argument\Formatter\BooleanFormatter;
use Eleanorsoft\Phar\Argument\Formatter\PathFormatter;
use Eleanorsoft\Phar\ArgumentList;
use Eleanorsoft\Phar\CommandAbstract;
use Eleanorsoft\Util;


/**
 * Class Backup
 * Backup magento1 project
 *
 * @package EleanorsoftUtil
 * @author Konstantin Esin <hello@eleanorsoft.com>
 * @copyright Copyright (c) 2017 Eleanorsoft (https://www.eleanorsoft.com/)
 */
class Backup extends CommandAbstract
{
    public function run(ArgumentList $argumentList)
    {
        $path = $argumentList->get('magento1-root-path', './', array(PathFormatter::class));

        $backupFiles = $argumentList->get('magento1-backup-files', 'y', array(BooleanFormatter::class));

        // backup files
        if ($backupFiles) {
            $backupMedia = $argumentList->get('magento1-backup-media-dir', 'n', array(BooleanFormatter::class));
            $outputFile = $argumentList->get('magento1-backup-output-tar-file', '../mag' . date('Ymd') . '.tar.gz');

            $include = array(
                '/*.*',
                '/.htaccess',
                '/.htaccess.sample',
                '/app/',
                '/downloader/',
                '/errors/',
                '/includes/',
                '/js/',
                '/lib/',
                '/pkginfo/',
                '/shell/',
                '/skin/',
                '/var/.htaccess',
                '/var/package/',
            );
            if ($backupMedia) {
                $include[] = '/media/';
            }

            $includeString = $path . implode(" $path", $include);

            $cmd = "tar -zcvf $outputFile $includeString";
            `$cmd`;

            Util::output("Backed up files: $outputFile\n");
        }

        $backupDatabase = $argumentList->get('magento1-backup-database', 'y', array(BooleanFormatter::class));

        // backup database
        if ($backupDatabase) {
            $outputFile = $argumentList->get('magento1-backup-output-db-file', '../magdb' . date('Ymd') . '.sql.gz');
            $config = simplexml_load_file("$path/app/etc/local.xml");
            $dbName = ((string)$config->global->resources->default_setup->connection->dbname);
            $dbUser = ((string)$config->global->resources->default_setup->connection->username);
            $dbPass = ((string)$config->global->resources->default_setup->connection->password);
            $dbHost = ((string)$config->global->resources->default_setup->connection->host);

            if (!$dbName or !$dbPass or !$dbUser or !$dbHost) {
                throw new \Exception("Can't read config (empty values)");
            }

            $cmd = "mysqldump -u $dbUser -p$dbPass -h$dbHost $dbName | gzip > $outputFile";
            `$cmd`;

            Util::output("Backed up DB: $outputFile\n");
        }
    }
}