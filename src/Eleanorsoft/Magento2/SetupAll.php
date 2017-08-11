<?php

namespace Eleanorsoft\Wordpress;
use Eleanorsoft\Docker\CreateSkeleton;
use Eleanorsoft\Phar\ArgumentList;
use Eleanorsoft\Phar\CommandAbstract;
use Eleanorsoft\Util;

class SetupAll extends CommandAbstract
{

	public function __construct()
	{
		parent::__construct([
			CreateSkeleton::class,
			ConfigureNginxInDockerContainer::class,
			function (ArgumentList $argumentList) {
				$path = rtrim($argumentList->get('docker-skeleton-path', './'), '/');
				$argumentList->set('magento2-composer-path', $path);
				$argumentList->set('magento2-docroot-path', $path . '/www/html/');
				$argumentList->set('magento2-set-owner-after-download', 'www-data:www-data');
				@unlink($path . '/www/html/index.php');
			},
			Download::class,
			function (ArgumentList $argumentList) {
				$path = rtrim($argumentList->get('docker-skeleton-path', './'), '/');
				$name = $argumentList->get('docker-skeleton-name');
				$portPrefix = $argumentList->get('docker-skeleton-port-prefix');
				$dbPass = $argumentList->get('docker-skeleton-mysql-password');
				$sftpPass = $argumentList->get('docker-skeleton-sftp-password');
				$domain = $argumentList->get('magento2-domain'); // without protocol

				$projectName = $argumentList->get('docker-skeleton-name', 'noname');
				$nginxPort = $portPrefix . '1';

				$baseUrl = "http://$domain:$nginxPort/";

				Util::output("\n\n" . str_repeat('*', 30) . "\n\n");

				Util::output(sprintf(
					"Main URL: %s\nphpMyAdmin:\n\tURL: %s\n\tLogin: %s\n\tPassword: %s\n\n",
					$baseUrl,
					"http://$domain:{$portPrefix}3/",
					$projectName,
					$dbPass
				));
				Util::output(sprintf(
					"sFTP:\n\tHost: %s\n\tLogin: %s\n\tPassword: %s\n\n",
					"http://$domain:{$portPrefix}4/",
					$projectName,
					$sftpPass
				));

				Util::output("\n\n" . str_repeat('*', 30) . "\n\n");

				Util::output("To finish installation, run the following command under your root account (use sudo):\n");
				$cmd = "php esutil.phar magento2/installInsideDocker --docker-skeleton-path=\"$path\" --docker-skeleton-name=\"$name\" --docker-skeleton-port-prefix=\"$portPrefix\" --docker-skeleton-mysql-password=\"$dbPass\" --magento2-base-url=\"$baseUrl\" --magento2-docroot-path=\"$path/www/html/\" --magento2-set-owner=\"www-data:www-data\"";
				Util::output($cmd . "\n\n");
			}
		]);
	}

	public function run(ArgumentList $argumentList)
	{
		parent::run($argumentList);
		print "Setting up Magento2...\n";
	}
}