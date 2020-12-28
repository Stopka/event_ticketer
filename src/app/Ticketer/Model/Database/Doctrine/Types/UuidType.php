<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;
use Ticketer\Model\Dtos\Uuid;

class UuidType extends Type
{
    /**
     * @var string
     */
    private const NAME = 'uuid';


    /**
     * {@inheritdoc}
     *
     * @param array<mixed> $column
     * @param AbstractPlatform $platform
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $column['length'] = Uuid::getStringLength();
        $column['fixed'] = true;

        return $platform->getVarcharTypeDeclarationSQL($column);
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $value
     * @param AbstractPlatform $platform
     *
     * @return Uuid|null
     *
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Uuid
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof Uuid) {
            return $value;
        }

        if (is_string($value)) {
            try {
                return Uuid::fromString($value);
            } catch (InvalidArgumentException $e) {
                throw ConversionException::conversionFailed($value, static::NAME, $e);
            }
        }

        throw ConversionException::conversionFailed((string)$value, static::NAME);
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $value
     * @param AbstractPlatform $platform
     *
     * @return string|null
     *
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }
        if ($value instanceof Uuid) {
            return $value->toString();
        }

        throw ConversionException::conversionFailed($value, static::NAME);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getName(): string
    {
        return static::NAME;
    }

    /**
     * {@inheritdoc}
     *
     * @param AbstractPlatform $platform
     *
     * @return bool
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
