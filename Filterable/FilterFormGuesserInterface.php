<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Filterable;

use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\RuleNode;
use Klipper\Component\Metadata\FieldMetadataInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface FilterFormGuesserInterface
{
    /**
     * Guess the form config.
     */
    public function guess(FilterFormConfig $config, RuleNode $node, FieldMetadataInterface $fieldMeta): void;
}
