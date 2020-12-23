<?php

declare(strict_types=1);

namespace Ticketer\Utils\Charset;

use MyCLabs\Enum\Enum;

/**
 * Class CharsetEnum
 * @package  Ticketer\Responses\SpreadsheetResponse
 * @extends  Enum<string>
 * @method static self BIG_5()
 * @method static self EUC_JP()
 * @method static self GB_2312()
 * @method static self ISO_8859_1()
 * @method static self ISO_8859_10()
 * @method static self ISO_8859_11()
 * @method static self ISO_8859_12()
 * @method static self ISO_8859_13()
 * @method static self ISO_8859_14()
 * @method static self ISO_8859_15()
 * @method static self ISO_8859_2()
 * @method static self ISO_8859_3()
 * @method static self ISO_8859_4()
 * @method static self ISO_8859_5()
 * @method static self ISO_8859_6()
 * @method static self ISO_8859_7()
 * @method static self ISO_8859_8()
 * @method static self ISO_8859_9()
 * @method static self KOI_8_R()
 * @method static self KS_C_5601_1987()
 * @method static self TIS_620()
 * @method static self UTF_16()
 * @method static self UTF_7()
 * @method static self UTF_8()
 * @method static self WINDOWS_1250()
 * @method static self WINDOWS_1251()
 * @method static self WINDOWS_1252()
 * @method static self WINDOWS_1256()
 * @method static self WINDOWS_1257()
 */
final class CharsetEnum extends Enum
{
    private const BIG_5 = 'big5';
    private const EUC_JP = 'euc-jp';
    private const GB_2312 = 'gb2312';
    private const ISO_8859_1 = 'iso-8859-1';
    private const ISO_8859_10 = 'iso-8859-10';
    private const ISO_8859_11 = 'iso-8859-11';
    private const ISO_8859_12 = 'iso-8859-12';
    private const ISO_8859_13 = 'iso-8859-13';
    private const ISO_8859_14 = 'iso-8859-14';
    private const ISO_8859_15 = 'iso-8859-15';
    private const ISO_8859_2 = 'iso-8859-2';
    private const ISO_8859_3 = 'iso-8859-3';
    private const ISO_8859_4 = 'iso-8859-4';
    private const ISO_8859_5 = 'iso-8859-5';
    private const ISO_8859_6 = 'iso-8859-6';
    private const ISO_8859_7 = 'iso-8859-7';
    private const ISO_8859_8 = 'iso-8859-8';
    private const ISO_8859_9 = 'iso-8859-9';
    private const KOI_8_R = 'koi8-r';
    private const KS_C_5601_1987 = 'ks_c_5601-1987';
    private const TIS_620 = 'tis-620';
    private const UTF_16 = 'utf-16';
    private const UTF_7 = 'utf-7';
    private const UTF_8 = 'utf-8';
    private const WINDOWS_1250 = 'windows-1250';
    private const WINDOWS_1251 = 'windows-1251';
    private const WINDOWS_1252 = 'windows-1252';
    private const WINDOWS_1256 = 'windows-1256';
    private const WINDOWS_1257 = 'windows-1257';
}
