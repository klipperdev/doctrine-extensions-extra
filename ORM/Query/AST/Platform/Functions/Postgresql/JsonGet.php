<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Platform\Functions\Postgresql;

/**
 * Json get function for Postgresql.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class JsonGet extends AbstractJsonBinaryOperator
{
    public static function getOperator(): string
    {
        return '->>';
    }
}
