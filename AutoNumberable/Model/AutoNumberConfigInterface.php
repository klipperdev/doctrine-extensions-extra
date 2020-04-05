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
 * Interface of auto number config model.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface AutoNumberConfigInterface
{
    /**
     * Get id.
     *
     * @return null|int|string
     */
    public function getId();

    /**
     * Set the type.
     *
     * @param null|string $type The type
     *
     * @return static
     */
    public function setType(?string $type);

    /**
     * Get the type.
     */
    public function getType(): ?string;

    /**
     * Set the auto number pattern.
     *
     * @param null|string $pattern The auto number pattern
     *
     * @return static
     */
    public function setPattern(?string $pattern);

    /**
     * Get the auto number pattern.
     */
    public function getPattern(): ?string;

    /**
     * Set the last number.
     *
     * @param int $number The last number
     *
     * @return static
     */
    public function setNumber(int $number);

    /**
     * Get the last number.
     */
    public function getNumber(): int;
}
