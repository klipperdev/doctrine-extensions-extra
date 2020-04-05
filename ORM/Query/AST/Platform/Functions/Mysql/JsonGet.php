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
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Functions\AbstractJsonBinaryFunctionNode;
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Platform\Functions\PlatformFunctionNode;

/**
 * JsonGet function for Mysql.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class JsonGet extends PlatformFunctionNode
{
    /**
     * {@inheritdoc}
     *
     * @throws
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        /** @var Node $leftNode */
        $leftNode = $this->parameters[AbstractJsonBinaryFunctionNode::JSON_DATA_KEY];
        $leftValue = $sqlWalker->walkStringPrimary($leftNode);
        /** @var Node $rightNode */
        $rightNode = $this->parameters[AbstractJsonBinaryFunctionNode::JSON_PATH_KEY];
        $rightValue = $rightNode->dispatch($sqlWalker);

        return 'JSON_EXTRACT('.$leftValue.', $.'.$rightValue.')';
    }
}
