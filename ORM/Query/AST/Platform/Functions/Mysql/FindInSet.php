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
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Functions\String\FindInSet as FindInSetFunction;
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Platform\Functions\PlatformFunctionNode;

/**
 * FindInSet function for Mysql.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FindInSet extends PlatformFunctionNode
{
    /**
     * @throws
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        /** @var Node $leftNode */
        $leftNode = $this->parameters[FindInSetFunction::DATA_KEY];
        $leftValue = $sqlWalker->walkStringPrimary($leftNode);

        /** @var Node $rightNode */
        $rightNode = $this->parameters[FindInSetFunction::DATA_VALUE];
        $rightValue = $rightNode->dispatch($sqlWalker);

        return 'FIND_IN_SET('.$leftValue.', '.$rightValue.')';
    }
}
