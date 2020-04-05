<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Mapping\Driver;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo as OrmClassMetadata;
use Gedmo\Mapping\Driver\Xml as BaseXml;
use Klipper\Component\DoctrineExtensionsExtra\Exception\InvalidMappingException;

/**
 * The mapping XmlDriver abstract class, defines the
 * metadata extraction function common among all
 * all drivers used on these extensions by file based
 * drivers.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class Xml extends BaseXml
{
    use MergeConfigTrait;

    public const KLIPPER_NAMESPACE_URI = 'http://klipper.dev/schemas/orm/doctrine-extensions-mapping';

    /**
     * Validate the element type of parent.
     *
     * @param ClassMetadata     $meta             The class metadata
     * @param \SimpleXMLElement $mapping          The mapping
     * @param string            $type             The mapping type
     * @param array             $validParentTypes The valid parent types
     */
    protected function validateElementType(ClassMetadata $meta, \SimpleXMLElement $mapping, $type, array $validParentTypes): void
    {
        if (!\in_array($mapping->getName(), $validParentTypes, true)) {
            $types = implode(', \'', $validParentTypes);

            throw new InvalidMappingException("The {$type} of '{$meta->getName()}' must be included in the valid element: '{$types}'");
        }
    }

    /**
     * Get the field name.
     *
     * @param ClassMetadata     $meta        The class metadata
     * @param \SimpleXmlElement $mapping     The mapping
     * @param \SimpleXmlElement $element     The klipper mapping
     * @param bool              $association Check if the field is an association
     * @param string            $type        The type
     */
    protected function getFieldName(ClassMetadata $meta, \SimpleXmlElement $mapping, \SimpleXmlElement $element, bool $association, string $type): string
    {
        if ($association) {
            $nameTypes = ['one-to-one', 'one-to-many', 'many-to-one', 'many-to-many'];
            $fieldName = \in_array($mapping->getName(), $nameTypes, true) ? $this->_getAttribute($mapping, 'field') : null;
        } else {
            $fieldName = 'field' === $mapping->getName() ? $this->_getAttribute($mapping, 'name') : null;
        }

        $field = $fieldName ?? $this->_getAttribute($element, 'field');

        if (empty($field)) {
            throw new InvalidMappingException("The {$type} of '{$meta->getName()}' require the 'field' attribute");
        }

        return $field;
    }

    /**
     * Get the name.
     *
     * @param ClassMetadata     $meta      The class metadata
     * @param \SimpleXmlElement $element   The klipper mapping
     * @param string            $type      The type
     * @param string            $attribute The attribute name
     */
    protected function getName(ClassMetadata $meta, \SimpleXmlElement $element, string $type, string $attribute): string
    {
        $name = $this->_getAttribute($element, $attribute);

        if (empty($name)) {
            throw new InvalidMappingException("The {$type} of '{$meta->getName()}' require the '{$attribute}' attribute");
        }

        return $name;
    }

    /**
     * Validate the field.
     *
     * @param ClassMetadata|OrmClassMetadata $meta       The class metadata
     * @param string                         $field      The field name
     * @param string[]                       $validTypes The valid types
     *
     * @return string The field name
     */
    protected function validateField($meta, string $field, array $validTypes): string
    {
        if (!$this->isValidField($meta, $field, $validTypes)) {
            throw new InvalidMappingException("Field - [{$field}] type is not valid and must be '".implode('\', \'', $validTypes)."' in class - {$meta->getName()}");
        }

        return $field;
    }

    /**
     * Checks if field type is valid.
     *
     * @param ClassMetadata|OrmClassMetadata $meta       The class metadata
     * @param string                         $field      The field name
     * @param string[]                       $validTypes The valid types
     *
     * @throws
     */
    protected function isValidField($meta, string $field, array $validTypes): bool
    {
        $mapping = $meta->getFieldMapping($field);

        return $mapping && \in_array($mapping['type'], $validTypes, true);
    }

    /**
     * Get the boolean value of attribute.
     *
     * @param \SimpleXmlElement $data      The data element
     * @param string            $attribute The attribute name
     * @param null|bool         $default   The default value
     */
    protected function getBooleanAttribute(\SimpleXmlElement $data, string $attribute, ?bool $default = null): ?bool
    {
        return $this->_isAttributeSet($data, $attribute)
            ? 'true' === strtolower((string) $this->_getAttribute($data, $attribute))
            : $default;
    }

    /**
     * Get the string value of attribute.
     *
     * @param \SimpleXmlElement $data      The data element
     * @param string            $attribute The attribute name
     * @param null|string       $default   The default value
     */
    protected function getStringAttribute(\SimpleXmlElement $data, string $attribute, ?string $default = null): ?string
    {
        return $this->_isAttributeSet($data, $attribute)
            ? (string) $this->_getAttribute($data, $attribute)
            : $default;
    }

    /**
     * Get the string values of attribute.
     *
     * @param \SimpleXmlElement $data      The data element
     * @param string            $attribute The attribute name
     * @param null|string[]     $default   The default value
     */
    protected function getStringsAttribute(\SimpleXmlElement $data, string $attribute, ?array $default = []): ?array
    {
        return $this->_isAttributeSet($data, $attribute)
            ? array_map('trim', explode(',', (string) $this->_getAttribute($data, $attribute)))
            : $default;
    }

    /**
     * Get the array value of attribute.
     *
     * @param \SimpleXmlElement $data      The data element
     * @param string            $attribute The attribute name
     * @param null|array        $default   The default value
     */
    protected function getArrayAttribute(\SimpleXmlElement $data, string $attribute, ?array $default = null): ?array
    {
        return $this->_isAttributeSet($data, $attribute)
            ? (array) $this->_getAttribute($data, $attribute)
            : $default;
    }
}
