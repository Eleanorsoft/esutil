<?php

namespace Eleanorsoft\Wordpress;
use Eleanorsoft\Phar\Argument\Formatter\BooleanFormatter;
use Eleanorsoft\Phar\Argument\Formatter\PathFormatter;
use Eleanorsoft\Phar\ArgumentList;
use Eleanorsoft\Phar\CommandAbstract;
use Eleanorsoft\Util;


/**
 * Class Backup
 * Backup wordpress project
 *
 * @package EleanorsoftUtil
 * @author Konstantin Esin <hello@eleanorsoft.com>
 * @copyright Copyright (c) 2017 Eleanorsoft (https://www.eleanorsoft.com/)
 */
class Backup extends CommandAbstract
{
    public function run(ArgumentList $argumentList)
    {
        $path = $argumentList->get('wordpress-root-path', './', [PathFormatter::class]);

        $backupFiles = $argumentList->get('wordpress-backup-files', 'y', [BooleanFormatter::class]);

        // backup files
        if ($backupFiles) {
            $backupUploads = $argumentList->get('wordpress-backup-uploads-dir', 'n', [BooleanFormatter::class]);
            $outputFile = $argumentList->get('wordpress-backup-output-tar-file', '../wp' . date('Ymd') . '.tar.gz');

            $exclude = '';
            if (!$backupUploads) {
                $exclude = "--exclude='wp-content/uploads/*'";
            }

            $cmd = "tar -zcvf $outputFile $exclude $path/*.php $path/.htaccess $path/wp-admin/ $path/wp-includes/ $path/wp-content/";
            `$cmd`;

            Util::output("Backed up files: $outputFile\n");
        }

        $backupDatabase = $argumentList->get('wordpress-backup-database', 'y', [BooleanFormatter::class]);

        // backup database
        if ($backupDatabase) {
            $outputFile = $argumentList->get('wordpress-backup-output-db-file', '../wpdb' . date('Ymd') . '.sql.gz');
            $config = file_get_contents("$path/wp-config.php");
            $match = [];
            $dbName = null;
            $dbUser = null;
            $dbPass = null;
            $dbHost = null;
            preg_match('/define\(\s*[\'|"]DB_NAME[\'|"]\s*,\s*[\'|"]([^\'"]+)[\'|"]\)/Usim', $config, $match);
            $dbName = $match[1];
            preg_match('/define\(\s*[\'|"]DB_USER[\'|"]\s*,\s*[\'|"]([^\'"]+)[\'|"]\)/Usim', $config, $match);
            $dbUser = $match[1];
            preg_match('/define\(\s*[\'|"]DB_PASSWORD[\'|"]\s*,\s*[\'|"]([^\'"]+)[\'|"]\)/Usim', $config, $match);
            $dbPass = $match[1];
            preg_match('/define\(\s*[\'|"]DB_HOST[\'|"]\s*,\s*[\'|"]([^\'"]+)[\'|"]\)/Usim', $config, $match);
            $dbHost = $match[1];

            if (!$dbName or !$dbPass or !$dbUser or !$dbHost) {
                throw new \Exception("Can't read config (empty values)");
            }

            $cmd = "mysqldump -u $dbUser -p$dbPass -h$dbHost $dbName | gzip > $outputFile";
            `$cmd`;

            Util::output("Backed up DB: $outputFile\n");
        }
    }
}