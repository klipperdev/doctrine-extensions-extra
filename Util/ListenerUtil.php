<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Util;

use Klipper\Component\DoctrineExtra\Util\ClassUtils;
use Klipper\Component\Resource\Exception\ConstraintViolationException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Util for doctrine listeners and subscribers.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class ListenerUtil
{
    /**
     * Create a constraint violation.
     */
    public static function createViolation(string $message, ?string $root = null, ?string $propertyPath = null): ConstraintViolation
    {
        return new ConstraintViolation($message, $message, [], $root, $propertyPath, null);
    }

    /**
     * Thrown a constraint violation exception.
     */
    public static function thrownError(string $message, ?string $root = null, ?string $propertyPath = null): void
    {
        $error = static::createViolation($message, $root, $propertyPath);

        static::thrownErrors([$error]);
    }

    /**
     * Thrown a constraint violation exception.
     *
     * @param ConstraintViolationInterface[]|ConstraintViolationListInterface $errors
     */
    public static function thrownErrors($errors): void
    {
        if (!$errors instanceof ConstraintViolationListInterface) {
            $errors = new ConstraintViolationList($errors);
        }

        throw new ConstraintViolationException($errors);
    }

    /**
     * Get the name of entity.
     *
     * @param object $entity The entity
     */
    public static function getEntityName(object $entity): string
    {
        $name = ClassUtils::getClass($entity);
        $pos = strrpos($name, '\\') + 1;

        return substr($name, $pos);
    }

    /**
     * Validate the entity.
     *
     * @param ValidatorInterface $validator The validator
     * @param object             $entity    The entity instance
     * @param null|string[]      $groups    The validator groups
     */
    public static function validateEntity(ValidatorInterface $validator, object $entity, ?array $groups = null): void
    {
        $errors = $validator->validate($entity, null, $groups);

        if ($errors->count() > 0) {
            static::thrownErrors($errors);
        }
    }

    /**
     * Merge the field values with object.
     *
     * @param PropertyAccessorInterface $accessor The property accessor
     * @param object                    $object   The object
     * @param array                     $values   The map of fields and values
     */
    public static function mergeValues(PropertyAccessorInterface $accessor, object $object, array $values): void
    {
        foreach ($values as $field => $value) {
            $prevValue = $accessor->getValue($object, $field);

            if (\is_array($value) && \is_array($prevValue)) {
                $accessor->setValue($object, $field, array_merge($prevValue, $value));
            } elseif (null !== $value && null === $prevValue) {
                $accessor->setValue($object, $field, $value);
            }
        }
    }
}
