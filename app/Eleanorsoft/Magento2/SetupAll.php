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
		]);
	}

	public function run(ArgumentList $argumentList)
	{
		parent::run($argumentList);
		print "Setting up Magento2...\n";
	}
}