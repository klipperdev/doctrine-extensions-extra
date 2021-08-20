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
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Functions\String\First as DqlFunction;
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Platform\Functions\PlatformFunctionNode;

/**
 * Add limit 1 for sub query for Mysql.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class First extends PlatformFunctionNode
{
    /**
     * @throws
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        /** @var Node $expression */
        $expression = $this->parameters[DqlFunction::SUB_SELECT];

        return sprintf(
            '(%s LIMIT 1)',
            $expression->dispatch($sqlWalker)
        );
    }
}
