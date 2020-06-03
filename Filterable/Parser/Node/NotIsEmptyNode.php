<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node;

use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\CompileArgs;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\ParserUtil;

/**
 * Not is empty node of filter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class NotIsEmptyNode extends RuleNode
{
    public function getOperator(): string
    {
        return 'is_not_empty';
    }

    public function isRequiredValue(): bool
    {
        return false;
    }

    public function compile(CompileArgs $arguments): string
    {
        return ParserUtil::getFieldName($arguments, $this)
            .' != '
            .ParserUtil::setValue($arguments, $this->getField(), '')
            ;
    }
}
