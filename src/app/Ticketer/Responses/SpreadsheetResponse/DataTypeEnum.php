<?php

declare(strict_types=1);

namespace Ticketer\Responses\SpreadsheetResponse;

use MyCLabs\Enum\Enum;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

/**
 * Class DataTypeEnum
 * @package Ticketer\Responses\SpreadsheetResponse
 * @extends Enum<string>
 * @method static self STRING2()
 * @method static self STRING()
 * @method static self FORMULA()
 * @method static self NUMERIC()
 * @method static self BOOL()
 * @method static self NULL()
 * @method static self INLINE()
 * @method static self ERROR()
 */
final class DataTypeEnum extends Enum
{
    private const STRING2 = DataType::TYPE_STRING2;
    private const STRING = DataType::TYPE_STRING;
    private const FORMULA = DataType::TYPE_FORMULA;
    private const NUMERIC = DataType::TYPE_NUMERIC;
    private const BOOL = DataType::TYPE_BOOL;
    private const NULL = DataType::TYPE_NULL;
    private const INLINE = DataType::TYPE_INLINE;
    private const ERROR = DataType::TYPE_ERROR;
}
