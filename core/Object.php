<?php
namespace Core;

/**
 * Object - Класс для организации хранения данных для объектов
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @method mixed getSomeName() Читает переменную someName
 * @method mixed setSomeName() Записывает переменную someName
 * @method mixed isSomeName() Чтение boolean переменной someName
 * @method mixed hasSomeName() Проверка наличия переменной someName
 * @method mixed removeSomeName() Удаляет переменную someName
 * @property mixed someName Читает/Записывае значение из/в переменную someName
 * @version 0.5
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
class Object extends Common {

    /**
     * Выполняет процедуру Чтения/Записи/Проверки/Удаления значений, формат (get|set|is|has|remove)SomeName
     *
     * @access public
     * @param string $name Запись/Чтение/Проверка/Удаление параметра в формате (get|set|is|has|remove)SomeName
     * @param mixed $arguments В случае set значение параметра, при (get|is|has|remove) указывать не надо
     * @return mixed При записи значений возвращает результат, при чтении - возвращает значение параметра, при проверке TRUE или FALSE
     */
    public function __call($name, $arguments) {
        $result = null;

        if(preg_match('#^(get|set|is|has|remove)([A-Z][A-z\d]*)$#', $name, $matches)) {
            $action = $matches[1];
            $name = $matches[2];
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
    public function __get($name) {
        $name = $this->toCamelStyle($name);

        return $this->get($name);
    }

    /**
     * Записывает переменную
     * @access public
     * @param string $name Название переменной в формате someName
     * @param string $arguments Значение переменной
     * @return \Core\Object
     */
    public function __set($name, $arguments) {
        return $this->set($name, $arguments);
    }

    /**
     * Проверяет наличие переменной
     * @access public
     * @param string $name Название переменной в формате someName
     * @return mixed Результат проверки
     */
    public function __isset($name) {
        return $this->has($name);
    }

    /**
     * Удаляет переменную
     * @access public
     * @param string $name Название переменной в формате someName
     * @return \Core\Object
     */
    public function __unset($name) {
        return $this->remove($name);
    }

    /**
     * Получает переменную
     * @access private
     * @param string $name Название переменной в формате someName
     * @return mixed Значение переменной
     */
    private function get($name) {
        $name = $this->toCamelStyle($name);

        return isset($this->$name) ? $this->$name : null;
    }

    /**
     * Читает boolean переменной
     *
     * @access private
     * @param string $name Название переменной в формате someName
     * @return boolean Результат проверки
     */
    private function is($name) {
        $name = $this->toCamelStyle($name);

        return isset($this->$name) ? (boolean)$this->$name : false;
    }

    /**
     * Проверяет наличие переменной
     * @access private
     * @param string $name Название переменной в формате someName
     * @return mixed Результат проверки
     */
    private function has($name) {
        $name = $this->toCamelStyle($name);

        return isset($this->$name);
    }

    /**
     * Записывает переменную
     * @access private
     * @param string $name Название переменной в формате someName
     * @param string $arguments Значение переменной
     * @return \Core\Object
     */
    private function set($name, $argument) {
        $name = $this->toCamelStyle($name);
        $this->$name = $argument;

        return $this;
    }

    /**
     * Удаляет переменную
     * @access private
     * @param string $name Название переменной в формате someName
     * @return \Core\Object
     */
    private function remove($name) {
        unset($this->$name);

        return $this;
    }

    /**
     * Инициализирует объект из данных массива
     * @access public
     * @param array $array Массив с данными
     * @return \Core\Object
     */
    public function fromArray($array) {
        foreach($array as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }
}

?>