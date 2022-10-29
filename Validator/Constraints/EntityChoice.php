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

use Symfony\Component\Validator\Constraints\Choice;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @Annotation
 *
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class EntityChoice extends Choice
{
    /**
     * The name of the entity manager to use for making the query. If it's left blank,
     * the correct entity manager will be determined for this class. For that reason,
     * this option should probably not need to be used.
     */
    public ?string $em = null;

    /**
     * Define the fully-qualified class name (FQCN) of the Doctrine entity associated with the
     * repository you want to use.
     */
    public ?string $entityClass = null;

    /**
     * The name of the repository method used to find the values. If it's left blank, findBy()
     * will be used. The method receives as its argument a fieldName => value associative
     * array (where fieldName is each of the fields configured in the fields option). The
     * method should return a countable PHP variable.
     */
    public ?string $repositoryMethod = null;

    /**
     * The property path of the choice name. If it's left blank, the doctrine identifier is used.
     */
    public ?string $namePath = null;

    /**
     * The static criteria for the doctrine repository method without the name path.
     *
     * @var string[]
     */
    public array $criteria = [];

    /**
     * The list of doctrine orm filter must be disabled.
     *
     * @var string[]
     */
    public array $filters = [];

    /**
     * Check if all doctrine orm filters must be disabled.
     */
    public bool $allFilters = false;

    /**
     * The service validator of entity choice.
     */
    public string $service = 'klipper_doctrine_extensions_extra.orm.validator.entity_choice';

    public function validatedBy(): string
    {
        return $this->service;
    }

    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }

    public function getDefaultOption(): string
    {
        return 'entityClass';
    }

    public function getRequiredOptions(): array
    {
        return [
            'entityClass',
        ];
    }
}
