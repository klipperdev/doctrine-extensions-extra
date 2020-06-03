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
 * Ends with node of filter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class EndsWithNode extends RuleNode
{
    public function getOperator(): string
    {
        return 'ends_with';
    }

    public function compile(CompileArgs $arguments): string
    {
        return 'UNACCENT(LOWER('.ParserUtil::getFieldName($arguments, $this).'))'
            .' LIKE '
            .'UNACCENT(LOWER('.ParserUtil::setValue($arguments, $this->getField(), '%'.$this->getQueryValue()).'))'
        ;
    }
}
