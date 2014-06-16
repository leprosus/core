<?php
namespace Core;

/**
 * Type - Набор функций для проверки типов
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.1
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class Type {
    public static $decimalRegExp = '#^([1-9]\d*|0)$#';
    public static $naturalRegExp = '#^([1-9]\d*)$#';
    public static $binaryRegExp = '#^[01]+$#';
    public static $integerRegExp = '#^([\+\-]?[1-9]\d*|0)$#';
    public static $doubleRegExp = '#^(([1-9]\d*|0)\.\d*)$#';
    public static $floatRegExp = '#^([\+\-]?([1-9]\d*|0)(\.\d*)?([eE][\+\-]?([1-9]\d*|0))?)$#';
    public static $booleanRegExp = '#^(0|1|false|true)$#';

    public static function isDecimal($var) {
        return preg_match(self::$decimalRegExp, strval($var)) != false;
    }

    public static function isNatural($var) {
        return preg_match(self::$naturalRegExp, strval($var)) != false;
    }

    public static function isBinary($var) {
        return preg_match(self::$binaryRegExp, strval($var)) != false;
    }

    public static function isInteger($var) {
        return preg_match(self::$integerRegExp, strval($var)) != false;
    }

    public static function isDouble($var) {
        return preg_match(self::$doubleRegExp, strval($var)) != false;
    }

    public static function isFloat($var) {
        return preg_match(self::$floatRegExp, strval($var)) != false;
    }

    public static function isBoolean($var) {
        return is_bool($var) || preg_match(self::$booleanRegExp, strval($var)) != false;
    }

    public static function isString($var) {
        return is_string($var);
    }

    public static function isArray($var) {
        return is_array($var);
    }

    public static function isObject($var) {
        return is_object($var);
    }

    public static function isNull($var) {
        return is_null($var);
    }
}

?>