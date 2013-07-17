<?php
namespace Core;

/**
 * Session - Класс для работы с сессийе
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @method mixed getSomeName() Читает переменную someName
 * @method mixed setSomeName() Записывает переменную someName
 * @method mixed isSomeName() Чтение boolean переменной someName
 * @method mixed hasSomeName() Проверка наличия переменной someName
 * @method mixed removeSomeName() Удаляет переменную someName
 * @property mixed someName Читает/Записывае значение из/в переменную someName
 * @version 0.3
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class Session extends Common{
    private static $instance;

    /**
     * Получение объекта
     * @access public
     * @return \Core\Session
     */
    public static function getInstance(){
        return (self::$instance === null) ? self::$instance = new self() : self::$instance;
    }

    /**
     * Конструктор
     * @access private
     * @return void
     */
    private function __construct(){
        session_start();
    }

    /**
     * Функция для работы с содержимым сессии
     * @access public
     * @param string $name      Название переменной в формате someName
     * @param array  $arguments Аргументы функции
     * @return mix Возвращает значение параметра
     */
    public function __call($name, $arguments){
        $result = null;

        if(preg_match('#^(get|set|is|has|remove)([A-Z][A-z\d]*)$#', $name, $matches)){
            $action = $matches[1];
            $name = $matches[2];
            $result = $action == 'set' ? $this->$action($name, $arguments[0]) : $this->$action($name);
        }

        return $result;
    }

    /**
     * Функция для чтеня переменной
     * @access public
     * @param string $name Название переменной в формате someName
     * @return mixed Значение переменной
     */
    public function __get($name){
        return $this->get($name);
    }

    /**
     * Функция для записи переменной
     * @access public
     * @param string $name      Название переменной в формате someName
     * @param string $arguments Значение переменной
     * @return mixed Значение переменной
     */
    public function __set($name, $arguments){
        return $this->set($name, $arguments);
    }

    /**
     * Читает boolean переменной
     *
     * @access public
     * @param string $name Название переменной в формате someName
     * @return mixed Результат проверки
     */
    public function __isset($name){
        return $this->is($name);
    }

    /**
     * Удаляет все данные
     * @access public
     * @param string $name Название переменной в формате someName
     * @return \Core\Session
     */
    public function clear(){
        $_SESSION = array();

        return $this;
    }

    /**
     * Удаляет переменную
     * @access public
     * @param string $name Название переменной в формате someName
     * @return \Core\Session
     */
    public function __unset($name){
        return $this->remove($name);
    }

    private function getMap($name){
        $name = $this->toStandardStyle($name);

        return explode('_', $name);
    }

    /**
     * Получает переменную
     * @access private
     * @param string $name Название переменной в формате someName
     * @return mixed Значение переменной
     */
    private function get($name){
        $result = null;

        $map = $this->getMap($name);
        if($this->has($name)){
            eval('$result = $_SESSION[\''.implode('\'][\'', $map).'\'];');
        }

        return $result;
    }

    /**
     * Читает boolean переменной
     *
     * @access private
     * @param string $name Название переменной в формате someName
     * @return boolean Результат проверки
     */
    private function is($name){
        $result = false;

        $result = (boolean)$this->get($name);

        return $result;
    }

    /**
     * Проверяет наличие переменной
     * @access private
     * @param string $name Название переменной в формате someName
     * @return mixed Результат проверки
     */
    private function has($name){
        $map = $this->getMap($name);
        eval('$result = isset($_SESSION[\''.implode('\'][\'', $map).'\']);');

        return $result;
    }

    /**
     * Записывает переменную
     * @access private
     * @param string $name     Название переменной в формате someName
     * @param string $argument Значение переменной
     * @return \Core\Session
     */
    private function set($name, $argument){
        $map = $this->getMap($name);
        eval('$_SESSION[\''.implode('\'][\'', $map).'\'] = $argument;');

        return $this;
    }

    /**
     * Удаляет переменную
     * @access private
     * @param string $name Название переменной в формате someName
     * @return \Core\Session
     */
    private function remove($name){
        $map = $this->getMap($name);
        eval('unset($_SESSION[\''.implode('\'][\'', $map).'\']);');

        return $this;
    }

    /**
     * Записывает сессию и освобождает данные для чтений и записи для другого потока
     * @access public
     * @return void
     */
    public function __destroy(){
        session_write_close();
    }
}

?>