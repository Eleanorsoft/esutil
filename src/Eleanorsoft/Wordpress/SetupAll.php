<?php

namespace Eleanorsoft\Wordpress;
use Eleanorsoft\Docker\CreateSkeleton;
use Eleanorsoft\Docker\RunContainer;
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
				$argumentList->set('wordpress-docroot-path', $path . '/www/html/');
				$argumentList->set('wordpress-set-owner-after-download', 'www-data:www-data');
				@unlink($path . '/www/html/index.php');
			},
			Download::class,
			function (ArgumentList $argumentList) {
				$path = rtrim($argumentList->get('docker-skeleton-path', './'), '/');
				$name = $argumentList->get('docker-skeleton-name');
				$portPrefix = $argumentList->get('docker-skeleton-port-prefix');
				$dbPass = $argumentList->get('docker-skeleton-mysql-password');
				$sftpPass = $argumentList->get('docker-skeleton-sftp-password');
				$domain = $argumentList->get('wordpress-domain'); // without protocol
				$docroot = $path . '/www/html';
				$projectName = $argumentList->get('docker-skeleton-name', 'noname');
				$nginxPort = $portPrefix . '1';

				$adminName = $projectName . 'admin';
				$adminPassword = $this->generateAdminPassword();

				$baseUrl = "http://$domain:$nginxPort/";

				rename($docroot . '/wp-config-sample.php', $docroot . '/wp-config.php');
				$config = file_get_contents($docroot . '/wp-config.php');
				$config = str_replace(
					['database_name_here', 'username_here', 'password_here', 'localhost'],
					[$projectName, $projectName, $dbPass, $projectName . '_mysql'],
					$config
				);
				while (stripos($config, 'put your unique phrase here') !== false) {
					$config = preg_replace('/put your unique phrase here/', Util::getRandomString(30), $config);
				}
				$config = "<?php define('FS_METHOD', 'direct'); ?>" . $config;

				file_put_contents($docroot . '/wp-config.php', $config);



				$newOwner = trim($argumentList->get('wordpress-set-owner', '')); // empty to skip
				if ($newOwner) {
					if (Util::isWindows()) {
						Util::output("Skip chown as working on Windows\n");
					} else {
						`chown -R $newOwner $docroot`;
					}
				}

			},
			RunContainer::class,
			function (ArgumentList $argumentList) {

				$path = rtrim($argumentList->get('docker-skeleton-path', './'), '/');
				$name = $argumentList->get('docker-skeleton-name');
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
				$opts = array('http' =>
					array(
						'method'  => 'POST',
						'header' => 'Content-Type: application/x-www-form-urlencoded\r\n',
						'content' => http_build_query([
							'weblog_title' => $projectName,
							'user_name' => $adminName,
							'admin_email' => 'noreply@unknown.host',
							'admin_password' => $adminPassword,
							'admin_password2' => $adminPassword,
							'pw_weak' => 1,
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