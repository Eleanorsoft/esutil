<?php

namespace Eleanorsoft\Magento2;
use Eleanorsoft\Docker\CheckDocker;
use Eleanorsoft\Docker\CreateSkeleton;
use Eleanorsoft\Docker\RunContainer;
use Eleanorsoft\Docker\SetDocrootOwner;
use Eleanorsoft\Http\WaitForWebserverToGetOnline;
use Eleanorsoft\Phar\ArgumentList;
use Eleanorsoft\Phar\CommandAbstract;
use Eleanorsoft\Util;

class SetupAll extends CommandAbstract
{

	public function __construct()
	{
		parent::__construct([
			CheckDocker::class,
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
			SetDocrootOwner::class,
            RunContainer::class,
			function (ArgumentList $argumentList) {
				$path = rtrim($argumentList->get('docker-skeleton-path', './'), '/');
				$portPrefix = $argumentList->get('docker-skeleton-port-prefix');
				$domain = $argumentList->get('magento2-domain'); // without protocol
				$nginxPort = $portPrefix . '1';
				$baseUrl = "http://$domain:$nginxPort/";

                $argumentList->set('webserver-url', $baseUrl);
				$argumentList->set('magento2-base-url', $baseUrl);
				$argumentList->set('magento2-docroot-path', "$path/www/html/");
			},
            WaitForWebserverToGetOnline::class,
			InstallInsideDocker::class,
			function (ArgumentList $argumentList)
			{
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
			}
		]);
	}

	public function run(ArgumentList $argumentList)
	{
		parent::run($argumentList);
	}
}