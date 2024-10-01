<?php

namespace IPS\rules\Secure;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

abstract class _Application extends \IPS\Application
{
    final public function isProtected()
    {
        return false;
    }

    public static function classCompliant($class, $classes)
    {
        $compliant = false;

        foreach ((array)$classes as $_class) {
            if (ltrim($_class, '\\') === ltrim($class, '\\')) {
                $compliant = true;
                break;
            }

            if (is_subclass_of($class, $_class)) {
                $compliant = true;
                break;
            }
        }

        return $compliant;
    }
}

const RULELIMIT = 10;