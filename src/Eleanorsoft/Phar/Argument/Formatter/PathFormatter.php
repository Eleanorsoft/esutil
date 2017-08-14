<?php

namespace Eleanorsoft\Phar\Argument\Formatter;


use Eleanorsoft\Phar\Argument\FormatterInterface;

/**
 * Class Path
 * Format string as file system path
 *
 * @package Eleanorsoft\Phar\Argument\Formatter
 */
class PathFormatter implements FormatterInterface
{

    /**
     * Remove trailing slash from path
     * @param string $value
     * @return string
     */
    public function format($value)
    {
        return rtrim($value, '/');
    }
}