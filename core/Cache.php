<?php
namespace Core;

/**
 * Cache - Класс для работы с кэшированием контента
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.2
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class Cache {
    private static $path = null;

    /**
     * Инициализация кэша
     * @access public
     * @static
     * @param int $path Директория хранения кэша
     * @return void
     */
    static function init($path) {
        self::$path = $path;
    }

    /**
     * Сохраняет контент
     * @access public
     * @static
     * @param string $hash Хеш сохраняемого контента
     * @param string $content Сохраняемый контент
     * @return void
     */
    static function push($hash, $content) {
        if(!is_null(self::$path)) {
            $file = new File(self::$path . '/' . $hash . '.tmp');
            $file->write($content);
        }
    }

    /**
     * Получает контент по хешу
     * @access public
     * @static
     * @param type $hash Хеш требуемого контента
     * @param int $lifetime Время жизни кэша (параметр необязательный)
     * @return string|null Контент, либо null, если ничего не найдено
     */
    static function pull($hash, $lifetime = null) {
        $content = null;

        if(!is_null(self::$path)) {
            $file = new File(self::$path . '/' . $hash . '.tmp');
            if($file->isExist()) {
                if(is_null($lifetime) || $file->getModified() > mktime() - $lifetime) {
                    $content = $file->read();
                } else {
                    $file->remove();
                }
            }
        }

        return $content;
    }

    /**
     * Генерит уникальный хеш для объекта
     * @access public
     * @static
     * @param Object $object Объект, для которого нужно получить хеш
     * @return string Хеш для объекта
     */
    static function getObjectHash($object) {
        return md5(serialize($object));
    }

    /**
     * Удаляет кэш по хешу
     * @access public
     * @static
     * @param string $hash Хеш удаляемого контента
     * @return void
     */
    static function remove($hash) {
        if(!is_null(self::$path)) {
            $file = new File(self::$path . '/' . $hash . '.tmp');
            $file->remove();
        }
    }
}

?>
