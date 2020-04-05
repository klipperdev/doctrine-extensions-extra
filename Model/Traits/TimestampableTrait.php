<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Model\Traits;

/**
 * Trait of translatable.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait TimestampableTrait
{
    /**
     * @var null|\DateTime
     */
    protected $createdAt;

    /**
     * @var null|\DateTime
     */
    protected $updatedAt;

    /**
     * {@inheritdoc}
     *
     * @see TimestampableInterface::setCreatedAt()
     */
    public function setCreatedAt(?\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see TimestampableInterface::getCreatedAt()
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     *
     * @see TimestampableInterface::setUpdatedAt()
     */
    public function setUpdatedAt(?\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see TimestampableInterface::getUpdatedAt()
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }
}
