<?php

namespace Eleanorsoft\Phar;

use Eleanorsoft\Util;

class ArgumentList
{

    protected $arguments = array();
    protected $stdin = null;

    /**
     * ArgumentList constructor.
     * @param array $args
     */
    public function __construct(array $args)
    {
        $this->stdin = fopen ("php://stdin","r");
        foreach ($args as $v) {
            $eq = strpos($v, '=');
            if ($eq !== false) {
                $name = trim(substr($v, 0, $eq), "- \t");
                $value = trim(trim(substr($v, $eq + 1)), '"');
                $this->set($name, $value);
            }
        }
    }

    public function set($k, $v)
    {
        $this->arguments[$k] = $v;
    }

    public function get($k, $default = null)
    {

        $value = null;
        if (isset($this->arguments[$k])) {
            $value = $this->arguments[$k];
        }

        while (is_null($value)) {
            $defValue = '';
            if ($default) {
                $defValue = sprintf(' [%s]', $default);
            }
            Util::output(sprintf('Enter "%s"%s: ', $k, $defValue));
            $value = trim(fgets($this->stdin));
            if (!$value) {
                $value = $default;
            }
        }

        $this->set($k, $value);

        return $value;
    }
}