<?php
namespace Core;

/**
 * Text - Класс содержит функциии для работы с текстом
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.1
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class Text{

    /**
     * Вырезает фрагмент текста
     * $access public
     *
     * @static
     * @param string|array $content Контент для манипуляций
     * @param int          $length  Предельная длинна результирующего текста
     * @param string       $tail    Строка, которой замещается вырезанный текст
     * @throws \Core\Exception\InvalidArgument
     * @return string|array Результирующий контент
     */
    public static function cut($content, $length, $tail = ''){
        if(is_numeric($length)){
            if(is_string($content)){
                if(self::getLength($content) > $length){
                    $content = mb_substr($content, 0, $length, 'UTF-8').$tail;
                    if(($position = mb_strrpos($content, ' ', 0, 'UTF-8')) !== false && $position > $length * 2 / 3){
                        $content = mb_substr($content, 0, $position, 'UTF-8').$tail;
                    }
                }
            } else if(is_array($content)){
                foreach($content as &$value){
                    $value = self::cut($value, $length, $tail);
                }
            }
        } else{
            throw new Exception\InvalidArgument('Set incorrect Length');
        }

        return $content;
    }

    /**
     * Превращает ссылки и Email`ы в действующие ссылки
     *
     * @param string|array $content Контент для манипуляций
     * @return string|array Результирующий контент
     */
    public static function highlightLinks($content){
        if(is_string($content)){
            $textFlag = true;

            $content = preg_split('#(<[^>]+>|\s|&[a-z\d]{2,10};|&\#\d{2,4};)#i', \Filter::unscreen($content), null, PREG_SPLIT_DELIM_CAPTURE + PREG_SPLIT_NO_EMPTY);

            $pattern = array(
                '#([\da-z][\-\._\da-z]+\.[a-z]{2,6}(?::\d+)?(?:/[a-z\d\-\.\?\,\'/\\\+&;%\$\#_]*)?)#i' => 'http://\\1',
                '#((?:ht|f)tp(?:s?)\://[\da-z][\-\._\da-z]+\.[a-z]{2,6}(?::\d+)?(?:/[a-z\d\-\.\?\,\'/\\\+&;%\$\#_]*)?)#i' => '<a href=\'\\1\'>\\1</a>'
            );
            $search = array_keys($pattern);
            $replace = array_values($pattern);

            foreach($content as &$token){
                if($textFlag && preg_match('#^<a[^>]+>$#i', $token)){
                    $textFlag = false;
                }

                if($textFlag && preg_match('#((?:(ht|f)tp(s?)\://)?[\da-z][\-\._\da-z]+\.[a-z]{2,6}(:\d+)?(?:/[a-z\d\-\.\?\,\'/\\\+&;%\$\#_]*)?)#i', $token)){
                    $token = preg_replace($search, $replace, $token);
                }

                if(!$textFlag && preg_match('#^</a>$#i', $token)){
                    $textFlag = true;
                }
            }

            $content = \Filter::screen(implode(' ', $content));
        } else if(is_array($content)){
            foreach($content as &$value){
                $value = self::highlightLinks($value);
            }
        }

        return $content;
    }

    /**
     * Возаращает реальную длинну текста в кодировке UTF-8, разэкранируя её
     *
     * @param string $content Текст
     * @return int Длинна текста
     */
    public static function getLength($content){
        return is_string($content) ? mb_strlen(Filter::unscreen($content), 'UTF-8') : 0;
    }
}
?>
