<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Doctrine\Types\Enums;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use MyCLabs\Enum\Enum;
use UnexpectedValueException;

/**
 * Class EnumType
 * @package          Ticketer\Model\Database\Doctrine\Types
 * @template         TValue
 * @template         TEnum of Enum
 */
abstract class EnumType extends Type
{
    /**
     * @return string
     * @phpstan-return class-string<TEnum>
     */
    abstract protected function getEnumClassName(): string;

    /**
     * @param Enum $enum
     * @phpstan-param  TEnum $enum
     * @return mixed
     * @phpstan-return TValue
     */
    abstract protected function convertEnumValue(Enum $enum);

    /**
     * @param mixed $value
     * @return mixed
     * @phpstan-return TValue
     */
    abstract protected function convertDatabaseValue($value);

    /**
     * @param mixed $value
     * @return bool
     */
    abstract protected function isValidDatabaseValueType($value): bool;

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return mixed
     * @phpstan-return TValue|null
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }
        $className = $this->getEnumClassName();
        if ($value instanceof $className) {
            return $this->convertEnumValue($value);
        }

        throw ConversionException::conversionFailed($value, $this->getName());
    }

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return Enum|null
     * @phpstan-return TEnum|null
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Enum
    {
        if (null === $value) {
            return null;
        }
        if ($this->isValidDatabaseValueType($value)) {
            $className = $this->getEnumClassName();
            try {
                return new $className($this->convertDatabaseValue($value));
            } catch (UnexpectedValueException $exception) {
                throw ConversionException::conversionFailed($value, $this->getName(), $exception);
            }
        }

        throw ConversionException::conversionFailed($value, $this->getName());
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
