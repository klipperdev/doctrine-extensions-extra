<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\DefaultValue\Extension;

use Klipper\Component\DefaultValue\AbstractTypeExtension;
use Klipper\Component\DefaultValue\ObjectBuilderInterface;
use Klipper\Component\DoctrineExtensionsExtra\DefaultValue\Loader\LoaderInterface;
use Klipper\Component\DoctrineExtensionsExtra\DefaultValue\Metadata\ClassMetadataInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * The default value type extension for doctrine mapping.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class DoctrineDefaultValueTypeExtension extends AbstractTypeExtension
{
    /**
     * @var LoaderInterface[]
     */
    protected array $loaders = [];

    protected ExpressionLanguage $expressionLanguage;

    protected PropertyAccessorInterface $accessor;

    /**
     * @var null|ClassMetadataInterface[]
     */
    protected ?array $metadatas;

    /**
     * @param LoaderInterface[]              $loaders            The loaders
     * @param ExpressionLanguage             $expressionLanguage The expression language
     * @param null|PropertyAccessorInterface $accessor           The property accessor
     */
    public function __construct(
        array $loaders,
        ExpressionLanguage $expressionLanguage,
        PropertyAccessorInterface $accessor = null
    ) {
        foreach ($loaders as $loader) {
            $this->addLoader($loader);
        }

        $this->expressionLanguage = $expressionLanguage;
        $this->accessor = $accessor ?? PropertyAccess::createPropertyAccessor();
    }

    /**
     * Add the loader of default value.
     *
     * @param LoaderInterface $loader The loader
     *
     * @return static
     */
    public function addLoader(LoaderInterface $loader)
    {
        $this->loaders[] = $loader;

        return $this;
    }

    public function buildObject(ObjectBuilderInterface $builder, array $options): void
    {
        $meta = $this->getMetadata($builder->getDataClass());

        if (null === $meta) {
            return;
        }

        $data = $builder->getData();

        foreach ($meta->getFields() as $fieldMeta) {
            if (null !== $expression = $fieldMeta->getExpression()) {
                $value = $this->expressionLanguage->evaluate($fieldMeta->getExpression());
                $this->accessor->setValue($data, $fieldMeta->getField(), $value);
            }
        }
    }

    public function getExtendedType(): string
    {
        return 'default';
    }

    /**
     * Get the metadata.
     *
     * @param string $class The class name
     */
    protected function getMetadata(string $class): ?ClassMetadataInterface
    {
        $metadatas = $this->getMetadatas();

        return $metadatas[$class] ?? null;
    }

    /**
     * Get the class metadatas.
     *
     * @return ClassMetadataInterface[]
     */
    protected function getMetadatas(): array
    {
        if (null === $this->metadatas) {
            $this->metadatas = [];

            foreach ($this->loaders as $loader) {
                foreach ($loader->load() as $class => $classMetadata) {
                    if (isset($this->metadatas[$class])) {
                        $this->metadatas[$class]->merge($classMetadata);
                    } else {
                        $this->metadatas[$class] = $classMetadata;
                    }
                }
            }
        }

        return $this->metadatas;
    }
}
