<?php

namespace Eleanorsoft\Phar\Argument\Formatter;


use Eleanorsoft\Phar\Argument\FormatterInterface;

/**
 * Class IntegerFormatter
 * Format input as integer
 *
 * @package Eleanorsoft\Phar\Argument\Formatter
 */
class IntegerFormatter implements FormatterInterface
{

    /**
     * Force integer value
     * @param string $value
     * @return string
     */
    public function format($value)
    {
        return intval($value);
    }
}