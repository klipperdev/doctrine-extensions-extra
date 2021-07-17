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
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Functions\String\GroupConcat as Base;
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Platform\Functions\PlatformFunctionNode;

/**
 * Group Concat function for Mysql.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class GroupConcat extends PlatformFunctionNode
{
    public function getSql(SqlWalker $sqlWalker): string
    {
        $isDistinct = !empty($this->parameters[Base::DISTINCT_KEY]);
        $result = 'GROUP_CONCAT('.($isDistinct ? 'DISTINCT ' : '');

        /** @var Node[] $pathExpressions */
        $pathExpressions = $this->parameters[Base::PARAMETER_KEY];
        $fields = [];

        foreach ($pathExpressions as $pathExp) {
            $fields[] = $pathExp->dispatch($sqlWalker);
        }

        $result .= sprintf('%s', implode(', ', $fields));

        if (!empty($this->parameters[Base::ORDER_KEY])) {
            $result .= ' '.$sqlWalker->walkOrderByClause($this->parameters[Base::ORDER_KEY]);
        }

        if (isset($this->parameters[Base::SEPARATOR_KEY])) {
            $result .= ' SEPARATOR '.$sqlWalker->walkStringPrimary($this->parameters[Base::SEPARATOR_KEY]);
        }

        $result .= ')';

        return $result;
    }
}
