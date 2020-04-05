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

use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\SqlWalker;
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Functions\SimpleFunction;
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Functions\String\Cast as DqlFunction;
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Platform\Functions\PlatformFunctionNode;

/**
 * Cast function for Postgresql.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class Cast extends PlatformFunctionNode
{
    /**
     * {@inheritdoc}
     *
     * @throws
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        /** @var Node $value */
        $value = $this->parameters[DqlFunction::PARAMETER_KEY];
        $type = $this->parameters[DqlFunction::TYPE_KEY];

        $type = strtolower($type);

        if ('datetime' === $type) {
            $timestampFunction = new Timestamp(
                [SimpleFunction::PARAMETER_KEY => $value]
            );

            return $timestampFunction->getSql($sqlWalker);
        }

        if ('json' === $type && !$sqlWalker->getConnection()->getDatabasePlatform()->hasNativeJsonType()) {
            $type = 'text';
        }

        if ('bool' === $type) {
            $type = 'boolean';
        }

        /*
         * The notations varchar(n) and char(n) are aliases for character varying(n) and character(n), respectively.
         * character without length specifier is equivalent to character(1). If character varying is used
         * without length specifier, the type accepts strings of any size. The latter is a PostgreSQL extension.
         * http://www.postgresql.org/docs/9.2/static/datatype-character.html
         */
        if ('string' === $type) {
            $type = 'varchar';
        }

        return 'CAST('.$this->getExpressionValue($value, $sqlWalker).' AS '.$type.')';
    }
}
