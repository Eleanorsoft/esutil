<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function __autoload($name) {
	require_once 'phar://esutil.phar/' . str_replace('\\', '/' , $name) . '.php';
}

$aliases = array(
	'magento2/' => 'Eleanorsoft\\Magento2',
	'docker/' => 'Eleanorsoft\\Docker',
);

$cmd = $argv[1];

foreach ($aliases as $k => $v) {
	if (strpos($cmd, $k) !== false) {
		$cmd = str_replace($k, $v . '/', $cmd);
		break;
	}
}

$tmp = explode('/', $cmd);
$tmp = array_map('ucfirst', $tmp);
$class = implode('\\', $tmp);

if (!class_exists($class)) {
	throw new Exception('Unknown command');
}

/** @var $c Eleanorsoft\Phar\CommandInterface */
$args = new \Eleanorsoft\Phar\ArgumentList(array_slice($argv, 2));
$c = new $class();
$c->run($args);

__HALT_COMPILER();