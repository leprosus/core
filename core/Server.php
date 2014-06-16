<?php
namespace Core;

/**
 * Document - Класс для работы с SERVER
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.2
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class Server extends Common {
    private static $instance = null;
    private $params = array();

    /**
     * Получение объекта
     * @access public
     * @static
     * @return \Core\Server
     */
    public static function getInstance() {
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Конструктор
     * @access private
     * @return void
     */
    private function __construct() {
        $this->params = $_SERVER;
        $_SERVER = array();
    }

    /**
     * Определяет, есть ли параметр или нет
     * @access public
     * @param string $name Название параметра
     * @param string $regexp Проверка регулярным выражением (<b>параметр необязательный</b>)
     * @see Filter::screen
     * @return boolean
     */
    public function hasParam($name, $regexp = null) {
        return isset($this->params[$name]) && (is_null($regexp) || preg_match($regexp, $this->params[$name]));
    }

    /**
     * Возвращает значение параметра, если неопределён, то null
     *
     * @access public
     * @param string $name Название параметра
     * @param string $regexp Проверка регулярным выражением (по-умолчанию null)<br />Без регулярного выражения параметр по выходу экранируется
     * @param string $default Значение по-умолчанию, в случае, если параметр не соответствует регулярному выражению (по-умолчанию null)
     * @see Filter::screen
     * @return object
     */
    public function getParam($name, $regexp = null, $default = null) {
        return $this->hasParam($name, $regexp) ? (is_null($regexp) ? Filter::screen($this->params[$name]) : $this->params[$name]) : $default;
    }

    /**
     * Возвращает ассоциированный массив с параметрами
     * @access public
     * @return array Массив с параметрами
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * Возвращает язык браузера
     * @access public
     * @return string $return Язык
     */
    public function getBrowserLang() {
        $result = 'ru';

        if(isset($this->params['HTTP_ACCEPT_LANGUAGE'])) {
            $explode = explode(',', $this->params['HTTP_ACCEPT_LANGUAGE']);
            $array = array();
            foreach($explode as $value) {
                if(strpos($value, ';') !== false) {
                    list($value,) = explode(';', $value);
                } else if(strpos($value, '-') !== false) {
                    list($value,) = explode('-', $value);
                }
                $array[] = $value;
            }
            $result = $array[0];
        }

        return $result;
    }

    /**
     * Возвращает IP адрес клиента
     *
     * @access public
     * @return string $return IP адрес
     */
    public function getIPAddress() {
        $ip = null;

        if($this->hasParam('HTTP_CLIENT_IP')) {
            $ip = $this->getParam('HTTP_CLIENT_IP');
        } else if($this->hasParam('REMOTE_ADDR')) {
            $ip = $this->getParam('REMOTE_ADDR');
        } else if($this->hasParam('HTTP_X_FORWARDED_FOR')) {
            $ip = $this->getParam('HTTP_X_FORWARDED_FOR');
        }

        return $ip;
    }

    private function __clone() {
    }

    private function __wakeup() {
    }
}

?>
