<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Platform\Functions\Mysql;

use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\SqlWalker;
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Functions\String\Cast as DqlFunction;
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Platform\Functions\PlatformFunctionNode;

/**
 * Cast function for Mysql.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class Cast extends PlatformFunctionNode
{
    /**
     * {@inheritdoc}
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        /** @var Node $value */
        $value = $this->parameters[DqlFunction::PARAMETER_KEY];
        $type = $this->parameters[DqlFunction::TYPE_KEY];

        $type = strtolower($type);
        $isBoolean = 'bool' === $type || 'boolean' === $type;

        if ('char' === $type) {
            $type = 'char(1)';
        } elseif ('string' === $type || 'text' === $type || 'json' === $type) {
            $type = 'char';
        } elseif ('int' === $type || 'integer' === $type || $isBoolean) {
            $type = 'signed';
        }

        $expression = 'CAST('.$this->getExpressionValue($value, $sqlWalker).' AS '.$type.')';

        if ($isBoolean) {
            $expression .= ' <> 0';
        }

        return $expression;
    }
}
