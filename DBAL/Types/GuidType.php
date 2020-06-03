<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\GuidType as BaseGuidType;
use Klipper\Component\Uuid\Util\UuidUtil;
use Ramsey\Uuid\Uuid;

/**
 * Guid Type.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class GuidType extends BaseGuidType
{
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        if ('mysql' === $platform->getName()) {
            return $platform->getBinaryTypeDeclarationSQL([
                'length' => '16',
                'fixed' => true,
            ]);
        }

        return parent::getSQLDeclaration($fieldDeclaration, $platform);
    }

    /**
     * @param mixed $value
     *
     * @throws
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ('mysql' === $platform->getName()) {
            if ('' === $value) {
                $value = null;
            }

            if (null === $value || (\is_string($value) && \strlen($value) > 16)) {
                return $value;
            }

            try {
                return Uuid::fromBytes($value)->toString();
            } catch (\InvalidArgumentException $e) {
                // Ignore the exception
            }

            throw ConversionException::conversionFailed($value, $this->getName());
        }

        return parent::convertToPHPValue($value, $platform);
    }

    /**
     * @param mixed $value
     *
     * @throws
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ('' === $value) {
            $value = null;
        }

        if (null === $value) {
            return $value;
        }

        if ('mysql' === $platform->getName()) {
            try {
                if (\is_string($value) || method_exists($value, '__toString')) {
                    return Uuid::fromString((string) $value)->getBytes();
                }
            } catch (\InvalidArgumentException $e) {
                // Ignore the exception
            }

            throw ConversionException::conversionFailed($value, $this->getName());
        }

        return UuidUtil::validate($value);
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return 'mysql' === $platform->getName() ? true : parent::requiresSQLCommentHint($platform);
    }
}
