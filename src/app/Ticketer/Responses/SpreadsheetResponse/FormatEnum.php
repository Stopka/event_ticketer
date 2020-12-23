<?php

declare(strict_types=1);

namespace Ticketer\Responses\SpreadsheetResponse;

use MyCLabs\Enum\Enum;

/**
 * Class FormatEnum
 * @extends Enum<string>
 * @package Ticketer\Responses\SpreadsheetResponse
 * @method static self CSV()
 * @method static self XLS()
 * @method static self XLSX()
 * @method static self ODS()
 * @method static self HTML()
 * @method static self PDF()
 */
final class FormatEnum extends Enum
{
    private const CSV = 'Csv';
    private const XLS = 'Xls';
    private const XLSX = 'Xlsx';
    private const ODS = 'Ods';
    private const HTML = 'Html';
    private const PDF = 'Pdf';
}
