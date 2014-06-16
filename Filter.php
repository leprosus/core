<?php
namespace Core;

/**
 * Filter - Класс содержит функциии для фильтрации и экранирования текстовых данных
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.3
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class Filter {
    private static $badFileNameCharacters
        = array(
            '<!--',
            '-->',
            '<',
            '>',
            '\'',
            '"',
            '&',
            '$',
            '#',
            '{',
            '}',
            '[',
            ']',
            '=',
            ';',
            '?',
            '%20',
            '%22',
            '%3c',
            '%253c',
            '%3e',
            '%0e',
            '%28',
            '%29',
            '%2528',
            '%26',
            '%24',
            '%3f',
            '%3b',
            '%3d'
        );

    /**
     * Экранирует опасные символы
     * @access public
     * @static
     * @param string|array $content Контент, который необходимо заэкранировать
     * @return string Результат экранирования
     */
    public static function screen($content) {
        if(is_string($content)) {
            $trans = array(
                '&' => '&amp;',
                '\'' => '&#039;',
                '<' => '&lt;',
                '>' => '&gt;',
                '"' => '&quot;',
                '\\' => '&#092;',
                '/' => '&#047;',
                '~' => '&#126;',
                '+' => '&#043;',
                '-' => '&#045;',
                '«' => '&laquo;',
                '»' => '&raquo;',
                '—' => '&mdash;',
                '…' => '&hellip;',
                '%' => '&#37;'
            );
            if(get_magic_quotes_gpc()) {
                $content = stripslashes($content);
            }
            $content = str_replace(array_keys($trans), $trans, self::unscreen($content));
        } else if(is_array($content)) {
            foreach($content as &$value) {
                $value = self::unscreen($value);
            }
        }

        return $content;
    }

    /**
     * Снимает экранирование
     * @access public
     * @static
     * @param string|array $content Контент, который необходимо разэкранировать
     * @return string Результат экранирования
     */
    public static function unscreen($content) {
        if(is_string($content)) {
            $trans = array(
                '&amp;' => '&',
                '&#039;' => '\'',
                '&lt;' => '<',
                '&gt;' => '>',
                '&quot;' => '"',
                '&#092;' => '\\',
                '&#047;' => '/',
                '&#126;' => '~',
                '&#043;' => '+',
                '&#045;' => '-',
                '&laquo;' => '«',
                '&raquo;' => '»',
                '&mdash;' => '—',
                '&hellip;' => '…',
                '&#37;' => '%'
            );
            $content = str_replace(array_keys($trans), $trans, $content);
        } else if(is_array($content)) {
            foreach($content as &$value) {
                $value = self::unscreen($value);
            }
        }

        return $content;
    }

    /**
     * Очистка контента от тегов
     * $access public
     *
     * @static
     * @param string|array $content Контент для очистки
     * @param string|array $tags Тего исключения (строчкой через запятую или в виде массива)<br />Параметр необязательный, в случае, если параметр не указан, то будут удалены все теги
     * @return string Очищенны контент
     */
    public static function stripTags($content, $tags = array()) {
        if(is_string($content)) {
            if(is_string($tags)) {
                $tags = explode(',', $tags);
            }

            //Разэкранируем
            $content = self::unscreen($content);

            if(count($tags) > 0) {
                //Чистим от тегов
                $content = strip_tags($content, '<' . implode('><', $tags) . '>');

                //Чистим от XSS
                $content = self::clearXSS($content);
            } else {
                $content = strip_tags($content);
            }

            //Снова экранируем
            $content = self::screen($content);
        } else if(is_array($content)) {
            foreach($content as &$value) {
                $value = self::stripTags($value);
            }
        }

        return $content;
    }

    /**
     * Очистка контента от XSS
     * Контент должен быть разэкранироан
     * $access public
     *
     * @static
     * @param string|array $content Контент для очистки
     * @return string Очищенны контент
     * @todo http://ha.ckers.org/xss.html (см. source)
     */
    public static function clearXSS($content) {
        if(is_string($content)) {
            //Чистим от опасных атрибутов
            $content = preg_replace('#(\s*(?:on\w+|formaction|xmlns)\s*=\s*(?:(?:\042|\047)?[^\s]*(?:\042|\047)?))#iu', '', $content);
            $content
                = preg_replace('#<.*?(?:href|src|style)\s*=.*?(alert|javascript\s*\:|livescript\s*\:|mocha\s*\:|charset\s*\=|window\s*\.|document\s*\.|expression\s*(\(|&\#40;)|\.\s*cookie|<\s*script|<\s*xss|base64\s*,).*?>#iu',
                               '', $content);

            $patterns = array(
                '#document\s*.\s*cookie#iu' => '',
                '#document\s*.\s*write#iu' => '',
                '#.\s*parentNode#iu' => '',
                '#.\s*innerHTML#iu' => '',
                '#window\s*.\s*location#iu' => '',
                '#-moz-binding#iu' => '',
                '#<!--#iu' => '&lt;!--',
                '#-->#iu' => '--&gt;',
                '#<!\[CDATA\[#iu' => '&lt;![CDATA[',
                '#<comment>#iu' => '&lt;comment&gt;',
                '#javascript\s*:#iu' => '',
                '#expression\s*(\(|&\#40;)#iu' => '',
                '#vbscript\s*:#iu' => '',
                '#Redirect\s+302#iu' => ''
            );
            $content = preg_replace(array_keys($patterns), array_values($patterns), $content);
        } else if(is_array($content)) {
            foreach($content as &$value) {
                $value = self::clearXSS($value);
            }
        }

        return $content;
    }

    /**
     * Очистка контента от невидимых символов
     * $access public
     *
     * @static
     * @param string|array $content Контент для очистки
     * @return string Очищенны контент
     */
    public static function removeInvisibleASCII($content) {
        if(is_string($content)) {
            preg_replace(array(
                             '#%0[0-8bcef]#',
                             '#%1[0-9a-f]#',
                             '#[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+#S'
                         ), '', $content);
        } else if(is_array($content)) {
            foreach($content as &$value) {
                $value = self::removeInvisibleASCII($value);
            }
        }

        return $content;
    }

    /**
     * Очищает и подготавливает путь и/или название файла
     * $access public
     *
     * @static
     * @param string|array $content Контент для очистки
     * @return string Очищенны контент
     */
    public static function prepareFilePath($filePath) {
        return str_replace(self::$badFileNameCharacters, '', str_replace('\\', '/', self::removeInvisibleASCII($filePath)));
    }
}

?>
