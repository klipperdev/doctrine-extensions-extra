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
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Functions\SimpleFunction;
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Platform\Functions\PlatformFunctionNode;

/**
 * Month function for Mysql.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class Month extends PlatformFunctionNode
{
    public function getSql(SqlWalker $sqlWalker): string
    {
        /** @var Node $expression */
        $expression = $this->parameters[SimpleFunction::PARAMETER_KEY];

        return sprintf(
            'MONTH(%s)',
            $this->getExpressionValue($expression, $sqlWalker)
        );
    }
}
