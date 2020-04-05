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

use Doctrine\ORM\Query\SqlWalker;
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Functions\Numeric\ConvertSurface as Base;
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Platform\Functions\PlatformFunctionNode;

/**
 * Convert surface function for Mysql.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ConvertSurface extends PlatformFunctionNode
{
    /**
     * {@inheritdoc}
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        $unit = trim($this->getExpressionValue($this->parameters[Base::UNIT_CONVERSION], $sqlWalker), '"\'');

        if ('metric' === $unit) {
            $unitType = 'imperial';
            $unitValue = 0.83612736;
        } else {
            $unitType = 'metric';
            $unitValue = 1.19599005;
        }

        return sprintf(
            '%s * (CASE WHEN %s = \'%s\' THEN %s ELSE 1 END)',
            $this->getExpressionValue($this->parameters[Base::SURFACE_FIELD], $sqlWalker),
            $this->getExpressionValue($this->parameters[Base::SURFACE_UNIT_FIELD], $sqlWalker),
            $unitType,
            $unitValue
        );
    }
}
