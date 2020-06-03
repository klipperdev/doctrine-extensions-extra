<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Platform\Functions;

use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Base of platform function node.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class PlatformFunctionNode
{
    public array $parameters;

    /**
     * @param array $parameters The parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Get the sql.
     *
     * @param SqlWalker $sqlWalker The sql walker
     */
    abstract public function getSql(SqlWalker $sqlWalker): string;

    /**
     * Get expression value string.
     *
     * @param Node|string $expression The expression
     * @param SqlWalker   $sqlWalker  The sql walker
     *
     * @throws
     */
    protected function getExpressionValue($expression, SqlWalker $sqlWalker): string
    {
        if ($expression instanceof Node) {
            $expression = $expression->dispatch($sqlWalker);
        }

        return $expression;
    }
}
