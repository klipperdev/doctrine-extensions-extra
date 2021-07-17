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
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Functions\String\GroupConcat as Base;
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Platform\Functions\PlatformFunctionNode;

/**
 * Group Concat function for Postgresql.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class GroupConcat extends PlatformFunctionNode
{
    public function getSql(SqlWalker $sqlWalker): string
    {
        $isDistinct = !empty($this->parameters[Base::DISTINCT_KEY]);
        $result = 'ARRAY_TO_STRING(ARRAY_AGG('.($isDistinct ? 'DISTINCT ' : '');

        /** @var Node[] $pathExpressions */
        $pathExpressions = $this->parameters[Base::PARAMETER_KEY];
        $fields = [];

        foreach ($pathExpressions as $pathExp) {
            $fields[] = $pathExp->dispatch($sqlWalker);
        }

        if (1 === \count($fields)) {
            $concatenatedFields = reset($fields);
        } else {
            $platform = $sqlWalker->getConnection()->getDatabasePlatform();
            $concatenatedFields = \call_user_func_array([$platform, 'getConcatExpression'], $fields);
        }

        $result .= $concatenatedFields;

        if (!empty($this->parameters[Base::ORDER_KEY])) {
            $result .= ' '.$sqlWalker->walkOrderByClause($this->parameters[Base::ORDER_KEY]);
        }

        $result .= ')';

        if (isset($this->parameters[Base::SEPARATOR_KEY])) {
            $separator = $this->parameters[Base::SEPARATOR_KEY];
        } else {
            $separator = ',';
        }

        $result .= ', '.$sqlWalker->walkStringPrimary($separator);

        $result .= ')';

        return $result;
    }
}
