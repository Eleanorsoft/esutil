<?php

namespace Eleanorsoft\Magento2;
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

				$projectName = $argumentList->get('docker-skeleton-name', 'noname');
				$nginxPort = $portPrefix . '1';

				$baseUrl = "http://$projectName.dev.eleanorsoft.com:$nginxPort/";

				Util::output(sprintf(
					"Main URL: %s\nphpMyAdmin:\n\tURL: %s\n\tLogin: %s\n\tPassword: %s\n\n",
					$baseUrl,
					"http://$projectName.dev.eleanorsoft.com:{$portPrefix}3/",
					$projectName,
					$dbPass
				));
				Util::output(sprintf(
					"sFTP:\n\tHost: %s\n\tLogin: %s\n\tPassword: %s\n\n",
					"http://$projectName.dev.eleanorsoft.com:{$portPrefix}4/",
					$projectName,
					$sftpPass
				));

				Util::output("To finish installation, run the following command under your root account (use sudo):\n");
				$cmd = "php esutil.phar magento2/installInsideDocker --docker-skeleton-path='$path' --docker-skeleton-name='$name' --docker-skeleton-port-prefix='$portPrefix' --docker-skeleton-mysql-password='$dbPass' --magento2-base-url='$baseUrl''";
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