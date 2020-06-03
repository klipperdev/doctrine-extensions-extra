<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\Transformers;

use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\CompileArgs;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\ContainsNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\NotContainsNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\RuleNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\ParserUtil;

/**
 * Filter node transformer of object.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FindInSetTransformer implements NodeTransformerInterface
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @param mixed $value The value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function compile(CompileArgs $arguments, RuleNode $node): string
    {
        if ($node instanceof ContainsNode) {
            return 'FIND_IN_SET('.ParserUtil::setValue($arguments, $node->getField(), $this->value)
                .', '.ParserUtil::getFieldName($arguments, $node).') = true';
        }
        if ($node instanceof NotContainsNode) {
            return 'FIND_IN_SET('.ParserUtil::setValue($arguments, $node->getField(), $this->value)
                .', '.ParserUtil::getFieldName($arguments, $node).') = false';
        }

        return $node->compile($arguments);
    }
}
