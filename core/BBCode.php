<?php
namespace Core;

/**
 * Bbcode - Набор функций для работы с BB кодами
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.2
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class BBCode {

    /**
     * Конвертирует BBCode в HTML
     *
     * @access public
     * @param string $text Конвертируемый текст
     * @return string Результат конвертации текста
     */
    public static function convertBbcodeToHtml($text) {
        $text = preg_replace('#<[^>]*>#', '', $text);
        $text = preg_replace('#\n#is', '<br />', $text);
        $text = preg_replace('#\[b\]#is', '<strong>', $text);
        $text = preg_replace('#\[\/b\]#is', '</strong>', $text);
        $text = preg_replace('#\[i\]#is', '<em>', $text);
        $text = preg_replace('#\[\/i\]#is', '</em>', $text);
        $text = preg_replace('#\[u\]#is', '<u>', $text);
        $text = preg_replace('#\[\/u\]#is', '</u>', $text);
        $text = preg_replace('#\[url=([^\]]+)\](.*?)\[\/url\]#is', '<a href="$1">$2</a>', $text);
        $text = preg_replace('#\[url\](.*?)\[\/url\]#is', '<a href="$1">$1</a>', $text);
        $text = preg_replace('#\[img\](.*?)\[\/img\]#is', '<img src="$1" />', $text);
        $text = preg_replace('#\[color=(.*?)\]#is', '<span style="color: $1;">', $text);
        $text = preg_replace('#\[\/color\]#is', '</span>', $text);
        $text = preg_replace('#\[size=(.*?)\]#is', '<small style="font-size: $1;">', $text);
        $text = preg_replace('#\[\/size\]#is', '</small>', $text);
        $text = preg_replace('#\[code\]#is', '<code>', $text);
        $text = preg_replace('#\[\/code\]#is', '</code>', $text);
        $text = preg_replace('#\[quote\]#is', '<blockquote>', $text);
        $text = preg_replace('#\[\/quote\]#is', '</blockquote>', $text);

        return $text;
    }

    /**
     * Конвертирует HTML в BBCode
     *
     * @access public
     * @param string $text Конвертируемый текст
     * @return string Результат конвертации текста
     */
    public static function convertHtmlToBbcode($text) {
        $text = preg_replace('#<b>#is', '[b]', $text);
        $text = preg_replace('#<\/b>#is', '[/b]', $text);
        $text = preg_replace('#<strong>#is', '[b]', $text);
        $text = preg_replace('#<\/strong>#is', '[/b]', $text);
        $text = preg_replace('#<i>#is', '[i]', $text);
        $text = preg_replace('#<\/i>#is', '[/i]', $text);
        $text = preg_replace('#<em>#is', '[i]', $text);
        $text = preg_replace('#<\/em>#is', '[/i]', $text);
        $text = preg_replace('#<u>#is', '[u]', $text);
        $text = preg_replace('#<\/u>#is', '[/u]', $text);
        $text = preg_replace('#<s>#is', '[s]', $text);
        $text = preg_replace('#<\/s>#is', '[/s]', $text);
        $text = preg_replace('#(<img src=["|\'|])(\S*)(["|\'|]{1})[a-z=\'"\s]{1,}(\/>|>)#is', '[img]$2[/img]', $text);
        $text = preg_replace('#(<a href=["|\'|])(\S*)(["|\'|]{1})[a-z=\'"\s]{0,}(\/>|>)(\S*)(<\/a>)#is', '[url=$2]$5[/url]', $text);
        $text = preg_replace('#<blockquote>#is', '[quote]', $text);
        $text = preg_replace('#<\/blockquote>#is', '[/quote]', $text);
        $text = preg_replace('#<pre>#is', '[code]', $text);
        $text = preg_replace('#<\/pre>#is', '[/code]', $text);
        $text = preg_replace('#<span style=("|\'|)font-size:\s?([\d]+)(px|pt|%|em|);?("|\'|)>([^<]*)<\/span>#is', '[size=$2]$5[/size]', $text);
        $text = preg_replace('#<span style=("|\'|)color:\s?(\#[a-z\d]{3,15});?("|\'|)>([^<]*)<\/span>#is', '[color=$2]$4[/color]', $text);

        return $text;
    }
}

?>