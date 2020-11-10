<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class RequestQueryFilter extends Constraint
{
    public string $metadataNamePath = 'type';

    /**
     * Check if the condition node must be added in root.
     */
    public bool $forceFirstCondition = false;
}
