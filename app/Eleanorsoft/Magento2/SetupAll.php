<?php

namespace Eleanorsoft\Magento2;
use Eleanorsoft\Docker\CreateSkeleton;
use Eleanorsoft\Phar\ArgumentList;
use Eleanorsoft\Phar\CommandAbstract;

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
				@unlink($path . '/www/html/index.php');
			},
			Download::class,
		]);
	}

	public function run(ArgumentList $argumentList)
	{
		parent::run($argumentList);
		print "Setting up Magento2...\n";
	}
}