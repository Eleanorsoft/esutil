<?php

namespace Eleanorsoft\Wordpress;
use Eleanorsoft\Docker\CleanDocroot;
use Eleanorsoft\Docker\CreateSkeleton;
use Eleanorsoft\Docker\CheckDocker;
use Eleanorsoft\Docker\RunContainer;
use Eleanorsoft\Docker\SetDocrootOwner;
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
				$portPrefix = $argumentList->get('docker-skeleton-port-prefix');
				$dbPass = $argumentList->get('docker-skeleton-mysql-password');
				$sftpPass = $argumentList->get('docker-skeleton-sftp-password');
				$domain = $argumentList->get('wordpress-domain'); // without protocol

				$projectName = $argumentList->get('docker-skeleton-name', 'noname');
				$nginxPort = $portPrefix . '1';

				$adminName = $projectName . 'admin';
				$adminPassword = $this->generateAdminPassword();

				$baseUrl = "http://$domain:$nginxPort/";

				$url = $baseUrl . 'wp-admin/install.php?step=2';

                $delayBeforeInstall = $argumentList->get(
                    'wordpress-delay-before-install-in-seconds',
                    10,
                    [IntegerFormatter::class]
                );
				sleep($delayBeforeInstall);

				$opts = array('http' =>
					array(
						'method'  => 'POST',
						'header' => 'Content-Type: application/x-www-form-urlencoded',
						'content' => http_build_query([
							'weblog_title' => $projectName,
							'user_name' => $adminName,
							'admin_email' => 'noreply@gmail.com',
							'admin_password' => $adminPassword,
							'admin_password2' => $adminPassword,
							'pw_weak' => 'on',
							'language' => '',
							'pass1-text' => $adminPassword,
							'Submit' => 'Install WordPress',
						]),
						'timeout' => 60
					)
				);
				$context  = stream_context_create($opts);
				file_get_contents($url, false, $context, -1, 40000);;

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