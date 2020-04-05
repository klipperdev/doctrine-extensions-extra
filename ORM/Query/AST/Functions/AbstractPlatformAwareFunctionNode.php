<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\SqlWalker;
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\FunctionFactory;

/**
 * Base of platform aware function node.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractPlatformAwareFunctionNode extends FunctionNode
{
    /**
     * @var array
     */
    public $parameters = [];

    /**
     * {@inheritdoc}
     *
     * @throws
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        $function = FunctionFactory::create(
            $sqlWalker->getConnection()->getDatabasePlatform()->getName(),
            $this->name,
            $this->parameters
        );

        return $function->getSql($sqlWalker);
    }
}
