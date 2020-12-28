<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Doctrine\Types\Enums;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use MyCLabs\Enum\Enum;

/**
 * Class StringEnumType
 * @package  Ticketer\Model\Database\Doctrine\Types
 * @template TEnum of Enum<string>
 * @extends  EnumType<string,TEnum>
 */
abstract class StringEnumType extends EnumType
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getVarcharTypeDeclarationSQL($column);
    }

    /**
     * @param Enum $enum
     * @phpstan-param TEnum $enum
     * @return string
     */
    protected function convertEnumValue(Enum $enum): string
    {
        return (string)$enum->getValue();
    }

    /**
     * @param mixed $value
     * @return string
     */
    protected function convertDatabaseValue($value): string
    {
        return (string)$value;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    protected function isValidDatabaseValueType($value): bool
    {
        return is_string($value);
    }
}
