<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'config.php';
\Eleanorsoft\Util::output(sprintf("Eleanorsoft Utility, version %s\n", $version));
\Eleanorsoft\Util::output(sprintf("by Eleanorsoft (https://www.eleanorsoft.com/)\n\n"));

$aliases = array(
    'docker/' => 'Eleanorsoft\\Docker',
	'magento2/' => 'Eleanorsoft\\Magento2',
	'wordpress/' => 'Eleanorsoft\\Wordpress',
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

\Eleanorsoft\Util::output(
	sprintf(
		"\n\nFull command could be:\nphp esutil.phar %s %s\n\n",
		$argv[1],
		$args->getAskedArgumentsString()
	)
);

/**
 * Autoload function for files inside phar archive
 *
 * @param $name
 */
function __autoload($className) {
    require_once 'phar://esutil.phar/' . str_replace('\\', '/' , $className) . '.php';
}

__HALT_COMPILER();