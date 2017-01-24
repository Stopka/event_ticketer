<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stopka\TableExporter;

/**
 * Třída pro práci se znakovými sadami a s konverzemi textu mezi UTF-8 a těmito sadami
 *
 * @author stopka
 */
class Charsets extends \Nette\Object {

    private static $charsets = Array(
        'big5',
        'euc-jp',
        'gb2312',
        'iso-8859-1',
        'iso-8859-10',
        'iso-8859-11',
        'iso-8859-12',
        'iso-8859-13',
        'iso-8859-14',
        'iso-8859-15',
        'iso-8859-2',
        'iso-8859-3',
        'iso-8859-4',
        'iso-8859-5',
        'iso-8859-6',
        'iso-8859-7',
        'iso-8859-8',
        'iso-8859-9',
        'koi8-r',
        'ks_c_5601-1987',
        'tis-620',
        'utf-16',
        'utf-7',
        'utf-8',
        'windows-1250',
        'windows-1251',
        'windows-1252',
        'windows-1256',
        'windows-1257'
    );

    /**
     * Vrací pole podporovaných znakových sad. Jména sad jsou klíčem i hodnotou pole
     * @return \string
     */
    public static function getAllWithKeys() {
        return array_combine(self::$charsets, self::$charsets);
    }

    /**
     * Vrátní pole všech podporovaných znakových sad.
     * @return type
     */
    public static function getAll() {
        return self::charsets;
    }

    private $charset;

    /**
     * Vytvoří koncertor znakových sad mezi UTF-8 a danou sadou
     * @param \string $charset
     */
    public function __construct($charset) {
        $this->charset = $charset;
    }

    /**
     * Převede text z UTF-8 do zvolené sady
     * @param \string $string
     * @return \string
     */
    public function convertStringTo($string) {
        //POZOR potlačený notice
        $result = @iconv('utf-8', $this->charset . "//TRANSLIT//IGNORE", $string);
        if ($result == NULL && $string != NULL) {
            throw new \Elearning\Exception\CharsetException("String converting error.");
        }
        return $result;
    }

    /**
     * Převede text ze zvolené sady do UTF-8
     * @param \string $string
     * @return \string
     */
    public function convertStringFrom($string) {
        if (!is_string($string)) {
            return $string;
        }
        //POZOR potlačený notice
        $result = @iconv($this->charset, "utf-8//TRANSLIT//IGNORE", $string);
        if ($string != NULL && $result == NULL) {
            throw new \Elearning\Exception\CharsetException("String converting error.");
        }
        return $result;
    }

    /**
     * Převede pole do znakové sady
     * @param \array $array
     * @return \array
     */
    public function convertArrayTo($array) {
        foreach ($array as $key => $value) {
            $array[$key] = $this->convertStringTo($value);
        }
        return $array;
    }

    /**
     * Převede pole ze znakové sady
     * @param \array $array
     * @return \array
     */
    public function convertArrayFrom($array) {
        foreach ($array as $key => $value) {
            $array[$key] = $this->convertStringFrom($value);
        }
        return $array;
    }

    /**
     * Převede 2D pole do znakové sady
     * @param \array $array
     * @return \array
     */
    public function convert2DArrayTo($array) {
        foreach ($array as $key => $value) {
            $array[$key] = $this->convertArrayTo($value);
        }
        return $array;
    }

    /**
     * Převede 2D pole ze znakové sady
     * @param \array $array
     * @return \array
     */
    public function convert2DArrayFrom($array) {
        foreach ($array as $key => $value) {
            $array[$key] = $this->convertArrayFrom($value);
        }
        return $array;
    }

    /**
     * Vrátí nastavenou znakovou sadu
     * @return \string
     */
    public function getName() {
        return $this->charset;
    }

}


