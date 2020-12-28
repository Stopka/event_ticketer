<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Doctrine\Types\Enums;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use MyCLabs\Enum\Enum;

/**
 * Class IntegerEnumType
 * @package  Ticketer\Model\Database\Doctrine\Types
 * @template TEnum of Enum<int>
 * @extends  EnumType<int,TEnum>
 */
abstract class IntegerEnumType extends EnumType
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getSmallIntTypeDeclarationSQL($column);
    }

    /**
     * @param Enum $enum
     * @phpstan-param TEnum $enum
     * @return int
     */
    protected function convertEnumValue(Enum $enum): int
    {
        return (int)$enum->getValue();
    }

    /**
     * @param mixed $value
     * @return int
     */
    protected function convertDatabaseValue($value): int
    {
        return (int)$value;
    }


    /**
     * @param mixed $value
     * @return bool
     */
    protected function isValidDatabaseValueType($value): bool
    {
        return is_numeric($value);
    }
}
