<?php

namespace Eleanorsoft\Phar;

use Eleanorsoft\Phar\Argument\FormatterInterface;
use Eleanorsoft\Util;

class ArgumentList
{

    const EMPTY_TO_SKIP = '(leave blank to skip action)';

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

    /**
     * Get command argument.
     * Formatters allow preprocessing of user input.
     *
     * @param $k
     * @param mixed $default
     * @param FormatterInterface[]|FormatterInterface $formatters
     * @return mixed
     */
    public function get($k, $default = null, $formatters = [])
    {
        if (!is_array($formatters)) {
            $formatters = [$formatters];
        }
        $defaultValue = $default;
        if ($default == self::EMPTY_TO_SKIP) {
            $defaultValue = false;
        }

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
                $value = $defaultValue;
            }

            foreach ($formatters as $formatterClass) {
                /** @var FormatterInterface $formatter */
                $formatter = new $formatterClass();
                $value = $formatter->format($value);
            }
        }

        $this->set($k, $value);

        return $value;
    }
}