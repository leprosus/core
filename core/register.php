<?php
namespace Core;

/**
 * Register - Класс для организации реестра со сквозным хранением переменных
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @method mixed getSomeName() Читает переменную someName
 * @method mixed setSomeName() Записывает переменную someName
 * @method mixed isSomeName() Чтение boolean переменной someName
 * @method mixed hasSomeName() Проверка наличия переменной someName
 * @method mixed removeSomeName() Удаляет переменную someName
 * @property mixed someName Читает/Записывае значение из/в переменную someName
 * @version 0.4
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class Register extends Common{
    private static $instance = null;
    private $params = array();

    /**
     * Получение объекта
     * @access public
     * @static
     * @return \Core\Config
     */
    public static function getInstance(){
        if(is_null(self::$instance)){
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Функция Запись/Чтение/Проверка/Удаление значений, формат (get|set|has|is|remove)Name
     *
     * @access public
     * @param string $name      Запись/Чтение/Проверка/Удаление параметра в формате (get|set|has|is|remove)Name
     * @param string $arguments В случае set значение параметра, при (get|has|is|remove) указывать не надо
     * @return mixed При записи значения возвращает результат, при чтении - возвращает значение параметра, при проверке TRUE или FALSE.
     */
    public function __call($name, $arguments){
        $result = null;

        if(preg_match('#^(get|is|set|has|remove)([A-Z][A-z\d]*)$#', $name, $matches)){
            $action = $matches[1];
            $name = $this->toCamelStyle($matches[2]);
            $result = $action == 'set' ? $this->$action($name, $arguments[0]) : $this->$action($name);
        }

        return $result;
    }

    /**
     * Читает переменную
     * @access public
     * @param string $name Название переменной в формате someName
     * @return mixed Значение переменной
     */
    public function __get($name){
        $name = $this->toCamelStyle($name);

        return $this->get($name);
    }

    /**
     * Записывает переменную
     * @access public
     * @param string $name      Название переменной в формате someName
     * @param string $arguments Значение переменной
     * @return mixed Значение переменной
     */
    public function __set($name, $arguments){
        $name = $this->toCamelStyle($name);

        return $this->set($name, $arguments);
    }

    /**
     * Проверяет наличие переменной
     * @access public
     * @param string $name Название переменной в формате someName
     * @return mixed Результат проверки
     */
    public function __isset($name){
        $name = $this->toCamelStyle($name);

        return $this->is($name);
    }

    /**
     * Удаляет переменную
     * @access public
     * @param string $name Название переменной в формате someName
     * @return \Core\Object
     */
    public function __unset($name){
        $name = $this->toCamelStyle($name);

        return $this->remove($name);
    }

    /**
     * Читает переменную
     * @access private
     * @param string $name Название переменной в формате someName
     * @return mixed
     */
    private function get($name){
        return isset($this->params[$name]) ? $this->params[$name] : null;
    }

    /**
     * Читает boolean переменной
     *
     * @access private
     * @param string $name Название переменной в формате someName
     * @return boolean
     */
    private function is($name){
        return isset($this->params[$name]) ? (boolean)$this->params[$name] : false;
    }

    /**
     * Проверяет наличие переменной
     * @access private
     * @param string $name Название переменной в формате someName
     * @return boolean
     */
    private function has($name){
        return isset($this->params[$name]);
    }

    /**
     * Записывает переменную
     * @access private
     * @param string $name     Название переменной в формате someName
     * @param mixed  $argument Значение переменной
     * @return \Core\Register
     */
    private function set($name, $argument){
        $this->params[$name] = $argument;

        return $this;
    }

    /**
     * Удаляет переменную
     * @access private
     * @param string $name Название переменной в формате someName
     * @return \Core\Register
     */
    private function remove($name){
        $result = $this->has($name);
        unset($this->params[$name]);

        return $result;
    }

    private function __clone(){
    }

    private function __wakeup(){
    }
}

?>