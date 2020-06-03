<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Klipper\Component\Metadata\MetadataManagerInterface;
use Klipper\Component\Metadata\ObjectMetadataInterface;

/**
 * Wraps errors in node.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class CompileArgs
{
    private ArrayCollection $parameters;

    private EntityManagerInterface $entityManager;

    private MetadataManagerInterface $metadataManager;

    private ObjectMetadataInterface $metadata;

    private string $alias;

    /**
     * @param ArrayCollection          $parameters      The query parameters
     * @param EntityManagerInterface   $entityManager   The entity manager
     * @param MetadataManagerInterface $metadataManager The metadata manager
     * @param ObjectMetadataInterface  $metadata        The metadata of query entity
     * @param string                   $alias           The alias of query entity
     */
    public function __construct(
        ArrayCollection $parameters,
        EntityManagerInterface $entityManager,
        MetadataManagerInterface $metadataManager,
        ObjectMetadataInterface $metadata,
        string $alias
    ) {
        $this->parameters = $parameters;
        $this->entityManager = $entityManager;
        $this->metadataManager = $metadataManager;
        $this->metadata = $metadata;
        $this->alias = $alias;
    }

    /**
     * Get the parameters of query.
     */
    public function getParameters(): ArrayCollection
    {
        return $this->parameters;
    }

    /**
     * Get the entity manager of query.
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * Get the metadata manager.
     */
    public function getMetadataManager(): MetadataManagerInterface
    {
        return $this->metadataManager;
    }

    /**
     * Get the object metadata of the query entity.
     */
    public function getObjectMetadata(): ObjectMetadataInterface
    {
        return $this->metadata;
    }

    /**
     * Get the alias of query entity.
     */
    public function getAlias(): string
    {
        return $this->alias;
    }
}
