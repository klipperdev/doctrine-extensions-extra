<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\AutoNumberable\Model;

/**
 * Auto number config model.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AutoNumberConfig implements AutoNumberConfigInterface
{
    /**
     * @var null|int|string
     */
    protected $id;

    protected ?string $type = null;

    protected ?string $pattern = null;

    protected int $number = 0;

    public function getId()
    {
        return $this->id;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setPattern(?string $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getNumber(): int
    {
        return $this->number;
    }
}
