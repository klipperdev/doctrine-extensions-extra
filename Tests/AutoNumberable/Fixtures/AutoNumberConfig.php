<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Tests\AutoNumberable\Fixtures;

use Doctrine\ORM\Mapping as ORM;
use Klipper\Component\DoctrineExtensionsExtra\AutoNumberable\Model\AutoNumberConfig as BaseAutoNumberConfig;

/**
 * Auto number config model fixture.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @ORM\Entity
 */
class AutoNumberConfig extends BaseAutoNumberConfig
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(name="type", type="string", length=255, unique=true)
     */
    protected ?string $type = null;

    /**
     * @ORM\Column(name="pattern", type="string", length=20)
     */
    protected ?string $pattern = null;

    /**
     * @ORM\Column(name="number", type="integer")
     */
    protected int $number = 0;
}
