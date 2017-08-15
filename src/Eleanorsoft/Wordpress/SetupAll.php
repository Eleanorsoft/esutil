<?php

namespace Eleanorsoft\Wordpress;
use Eleanorsoft\Docker\CleanDocroot;
use Eleanorsoft\Docker\CreateSkeleton;
use Eleanorsoft\Docker\CheckDocker;
use Eleanorsoft\Docker\RunContainer;
use Eleanorsoft\Docker\SetDocrootOwner;
use Eleanorsoft\Http\WaitForWebserverToGetOnline;
use Eleanorsoft\Phar\Argument\Formatter\IntegerFormatter;
use Eleanorsoft\Phar\Argument\Formatter\PathFormatter;
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
				$path = $argumentList->get('docker-skeleton-path', './', [PathFormatter::class]);
				$argumentList->set('wordpress-docroot-path', $path . '/www/html/');
			},
			CleanDocroot::class,
			Download::class,
			CreateWpConfig::class,
			SetDocrootOwner::class,
			RunContainer::class,
			function (ArgumentList $argumentList) {
				$domain = $argumentList->get('wordpress-domain'); // without protocol
				$portPrefix = $argumentList->get('docker-skeleton-port-prefix');
				$nginxPort = $portPrefix . '1';
				$baseUrl = "http://$domain:$nginxPort/";
				$argumentList->set('webserver-url', $baseUrl);
			},
			WaitForWebserverToGetOnline::class,
			InstallViaHttp::class,
			function (ArgumentList $argumentList) {
				$portPrefix = $argumentList->get('docker-skeleton-port-prefix');
				$dbPass = $argumentList->get('docker-skeleton-mysql-password');
				$sftpPass = $argumentList->get('docker-skeleton-sftp-password');
				$domain = $argumentList->get('wordpress-domain'); // without protocol

				$projectName = $argumentList->get('docker-skeleton-name', 'noname');
				$nginxPort = $portPrefix . '1';

				$adminName = $argumentList->get('worpress-admin-name');
				$adminPassword = $argumentList->get('worpress-admin-password');

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
                Util::output(sprintf(
                    "Admin:\n\tHost: %s\n\tLogin: %s\n\tPassword: %s\n\n",
                    $baseUrl . 'wp-admin/',
                    $adminName,
                    $adminPassword
                ));

				Util::output("\n\n" . str_repeat('*', 30) . "\n\n");
			}
		]);
	}

	public function run(ArgumentList $argumentList)
	{
		parent::run($argumentList);
	}

	/**
	 * Generate Wordpress admin password
	 * It must contain at least one alpha, digit and special char.
	 * @return string
	 */
	protected function generateAdminPassword()
	{
		return
			Util::getRandomString(1, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') .
			Util::getRandomString(1, '0123456789') .
			Util::getRandomString(1, '@*^%#()<>') .
			Util::getRandomString();
	}
}