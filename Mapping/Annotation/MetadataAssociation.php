<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Mapping\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Metadata field annotation for Metadata behavioral extension.
 *
 * @Annotation
 *
 * @Target({"PROPERTY", "METHOD", "CLASS", "ANNOTATION"})
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class MetadataAssociation extends Annotation
{
    public ?string $association = null;

    public ?string $name = null;

    public ?string $type = null;

    public ?string $target = null;

    public ?string $label = null;

    public ?string $description = null;

    public ?string $translationDomain = null;

    public ?bool $public = null;

    public ?bool $readOnly = null;

    public ?bool $required = null;

    public ?string $input = null;

    public array $inputConfig = [];

    public ?string $formType = null;

    public array $formOptions = [];

    /**
     * @var string[]
     */
    public array $groups = [];
}
