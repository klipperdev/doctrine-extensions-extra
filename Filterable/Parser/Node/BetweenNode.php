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
 * Between node of filter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class BetweenNode extends RuleNode
{
    /**
     * {@inheritdoc}
     */
    public function getOperator(): string
    {
        return 'between';
    }

    /**
     * {@inheritdoc}
     */
    public function isCollectible(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getSizeCollection(): int
    {
        return 2;
    }

    /**
     * {@inheritdoc}
     */
    public function compile(CompileArgs $arguments): string
    {
        $value = $this->getQueryValue();

        return ParserUtil::getFieldName($arguments, $this)
            .' >= '
            .ParserUtil::setValue($arguments, $this->getField(), $value[0])
            .' AND '
            .ParserUtil::getFieldName($arguments, $this)
            .' <= '
            .ParserUtil::setValue($arguments, $this->getField(), $value[1])
        ;
    }
}
