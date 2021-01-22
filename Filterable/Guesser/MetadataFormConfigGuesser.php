<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Filterable\Guesser;

use Klipper\Component\DoctrineExtensionsExtra\Filterable\FilterFormConfig;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\FilterFormGuesserInterface;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\RuleNode;
use Klipper\Component\Metadata\FieldMetadataInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class MetadataFormConfigGuesser implements FilterFormGuesserInterface
{
    public function guess(FilterFormConfig $config, RuleNode $node, FieldMetadataInterface $fieldMeta): void
    {
        $config->setType($fieldMeta->getFormType() ?? $config->getType());
        $config->addOptions($fieldMeta->getFormOptions());
    }
}
