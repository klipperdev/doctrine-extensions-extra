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

/**
 * OR node of filter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrNode extends ConditionNode
{
    /**
     * {@inheritdoc}
     */
    public function getCondition(): string
    {
        return 'OR';
    }
}
