<?php

namespace Eleanorsoft\Phar\Argument\Formatter;


use Eleanorsoft\Phar\Argument\FormatterInterface;

/**
 * Class BooleanFormatter
 * Format input value as boolean. "y"|"yes"|"1" = true, anything else = false
 *
 * @package Eleanorsoft\Phar\Argument\Formatter
 * @author Konstantin Esin <hello@eleanorsoft.com>
 * @copyright Copyright (c) 2017 Eleanorsoft (https://www.eleanorsoft.com/)
 */
class BooleanFormatter implements FormatterInterface
{

    /**
     * Format "y" or "yes" or "1" as true, anything else as false
     *
     * @param string $value
     * @return boolean
     * @author Konstantin Esin <hello@eleanorsoft.com>
     */
    public function format($value)
    {
        $vlower = strtolower($value);
        return in_array($vlower, ['y', 'yes', '1']);
    }
}