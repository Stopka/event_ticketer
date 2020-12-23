<?php

declare(strict_types=1);

namespace Ticketer\Utils\Charset;

use Nette\SmartObject;

class CharsetConverter
{
    use SmartObject;

    private CharsetEnum $charset;
    protected CharsetEnum $baseCharset;

    /**
     * @param CharsetEnum $charset
     * @param CharsetEnum|null $baseCharset defaults to utf8
     */
    public function __construct(CharsetEnum $charset, ?CharsetEnum $baseCharset = null)
    {
        $this->baseCharset = $baseCharset ?? CharsetEnum::UTF_8();
        $this->charset = $charset;
    }

    /**
     * @param string $string
     * @return string
     * @throws CharsetException
     */
    public function convertTo(string $string): string
    {
        return self::convert($this->baseCharset, $this->charset, $string);
    }

    /**
     * @param string $string
     * @return string
     * @throws CharsetException
     */
    public function convertStringFrom(string $string): string
    {
        return self::convert($this->charset, $this->baseCharset, $string);
    }

    /**
     * @return CharsetEnum
     */
    public function getCharset(): CharsetEnum
    {
        return $this->charset;
    }

    /**
     * @return CharsetEnum
     */
    public function getBaseCharset(): CharsetEnum
    {
        return $this->baseCharset;
    }


    /**
     * @param CharsetEnum $from
     * @param CharsetEnum $to
     * @param string $string
     * @return string
     * @throws CharsetException
     */
    public static function convert(CharsetEnum $from, CharsetEnum $to, string $string): string
    {
        $result = @iconv($from->getValue(), $to->getValue() . '//TRANSLIT//IGNORE', $string);
        if (false === $result) {
            throw new CharsetException('String converting error.');
        }

        return $result;
    }
}
