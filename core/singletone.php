<?php
namespace Core;

/**
 * Singletone - Класс для создания синглтонов
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.1
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
abstract class Singletone{
    private static $instances = array();

    final protected function __construct(){
    }

    final protected function __clone(){
    }

    final protected function __wakeup(){
    }

    /**
     * Получение синглтон объекта
     * @access public
     * @final
     * @static
     * @return \Core\Singletone
     */
    final public static function getInstance(){
        $className = get_called_class();
        if(!isset(self::$instances[$className])){
            self::$instances[$className] = new static();
        }

        return self::$instances[$className];
    }
}
?>
