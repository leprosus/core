<?php
namespace Core;

/**
 * Validate - Набор функций для проверки данных
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.3
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class Validate {
    public static $telephoneRegExp = '#^(\+7|8|)(\s|\(|)\d{3,5}(\s|\)|)\d{1,3}(\s|-|)\d{2}(\s|-|)\d{2}$#';
    public static $emailRegExp = '#^[^@]+@[^@]+\.[^@]+$#i';
    public static $urlRegExp = '#^(ht|f)tp(s?)\://[\da-z][\-\._\da-z]+\.[a-z]{2,6}(:\d+)?(?:/[a-z\d\-\.\?\,\'/\\\+&;%\$\#_]*)?$#i';
    public static $dateRegExp = '#^\d{4}-\d{2}-\d{2}$#';
    public static $timeRegExp = '#^\d{2}:\d{2}(?::\d{2})?$#';
    public static $datetimeRegExp = '#^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}$#';
    public static $md5RegExp = '#^[\dabcdef]{32}$#i';
    public static $base64RegExp = '#^(?:[a-z\d+/]{4})*(?:[a-z\d+/]{2}==|[a-z\d+/]{3}=)$#i';
    public static $ip4RegExp = '#^(25[0-5]|2[0-4]\d|1\d{2}|[1-9]\d?)\.((25[0-5]|2[0-4]\d|1\d{2}|[1-9]\d?|\d)\.){2}(25[0-5]|2[0-4]\d|1\d{2}|[1-9]\d?|\d)$#';
    public static $asciiRegExp = '#[^\x00-\x7F]#S';
    public static $rgbRegExp = '#^\#?[\dabcdef]{6}$#i';

    /**
     * Функция проверки номера телефона
     * @access public
     * @param string $text Выражение для проверки
     * @return boolean
     */
    public static function checkTelephone($text) {
        return preg_match(self::$telephoneRegExp, $text);
    }

    /**
     * Функция проверки номера Email`а
     *
     * @access public
     * @param string $text Выражение для проверки
     * @return boolean
     */
    public static function checkEmail($text) {
        return preg_match(self::$emailRegExp, \Core\Filter::unscreen($text));
    }

    /**
     * Функция проверки номера URL
     *
     * @access public
     * @param string $text Выражение для проверки
     * @return boolean
     */
    public static function checkUrl($text) {
        return preg_match(self::$urlRegExp, $text);
    }

    /**
     * Функция проверки даты в формате YYYY-MM-DD
     *
     * @access public
     * @param string $date Выражение для проверки
     * @return boolean
     */
    public static function checkDate($date) {
        return preg_match(self::$dateRegExp, $date);
    }

    /**
     * Функция проверки времени в формате HH:MM:SS или HH:MM
     *
     * @access public
     * @param string $time Выражение для проверки
     * @return boolean
     */
    public static function checkTime($time) {
        return preg_match(self::$timeRegExp, $time);
    }

    /**
     * Функция проверки даты и времени в формате YYYY-MM-DD HH:MM:SS
     *
     * @access public
     * @param string $dateTime Выражение для проверки
     * @return boolean
     */
    public static function checkDateTime($dateTime) {
        return preg_match(self::$datetimeRegExp, $dateTime);
    }

    /**
     * Функция проверки хэша md5
     *
     * @access public
     * @param string $text Выражение для проверки
     * @return boolean
     */
    public static function checkMD5($text) {
        return preg_match(self::$md5RegExp, $text);
    }

    /**
     * Функция проверки хэша base64
     *
     * @access public
     * @param string $text Выражение для проверки
     * @return boolean
     */
    public static function checkBase64($text) {
        return preg_match(self::$base64RegExp, $text);
    }

    /**
     * Функция проверки IPv4
     *
     * @access public
     * @param string $ip Выражение для проверки
     * @return boolean
     */
    public static function checkIP4($ip) {
        return preg_match(self::$ip4RegExp, $ip);
    }

    /**
     * Функция проверки ASCII
     *
     * @access public
     * @param string $ip Выражение для проверки
     * @return boolean
     */
    public static function checkASCII($ascii) {
        return preg_match(self::$asciiRegExp, $ascii);
    }

    /**
     * Функция проверки RGB цвета
     *
     * @access public
     * @param string $rgb RGB цвет
     * @return boolean
     */
    public static function checkRGB($rgb) {
        return preg_match(self::$rgbRegExp, $rgb);
    }
}

?>