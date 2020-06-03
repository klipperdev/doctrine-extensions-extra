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
use Klipper\Component\DoctrineExtensionsExtra\Mapping\Annotation as Klipper;

/**
 * Foo model fixture.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @ORM\Entity
 */
class Foo
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected ?int $id = null;

    /**
     * @ORM\Column(name="number", type="string", length=20)
     * @Klipper\AutoNumberable(pattern="I{YYYY}{MM}-{000000}")
     */
    protected ?string $number = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }
}
