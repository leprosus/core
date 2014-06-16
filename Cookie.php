<?php
namespace Core;

/**
 * Cookie - Обёртка для работы с Cookie
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.2
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class Cookie {
    private $data = array();
    private static $cookie = null;

    /**
     * Получение объекта
     * @access public
     * @static
     * @return \Core\Cookie
     */
    public static function getInstance() {
        if(is_null(self::$cookie)) {
            self::$cookie = new self();
        }

        return self::$cookie;
    }

    /**
     * Добавление данных о Cookie в предварительный массив
     *
     * @access public
     * @param string $name Имя Cookie
     * @param string $value Значение Cookie
     * @param int $expire Время жизни Cookie (по умолчанию 0)
     * @param string $path Значение пути к Cookie (по умолчанию не указан)
     * @param string $domain Имя хоста (по умолчанию не указан)
     * @param boolean $secure Передача Cookie через защищенное HTTPS-соединение (по умолчанию false)
     * @param boolean $httponly Доступность Cookie тольк через протокол HTTP (по умолчанию true)
     * @return \Core\Cookie
     */
    public function add($name, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false) {
        if(Server::getInstance()
                 ->getParam('HTTP_HOST') == 'localhost'
        ) {
            $path = '/';
            $domain = false;
        }
        $this->data[$name] = array($name, $value, $expire, $path, $domain, $secure, $httponly);

        return $this;
    }

    /**
     * Удаление данных о Cookie из предварительного массива
     *
     * @access public
     * @param string $name Имя Cookie
     * @return \Core\Cookie
     */
    public function remove($name) {
        if(isset($_COOKIE[$name])) {
            unset($_COOKIE[$name]);
        }

        return $this->add($name, null, -1);
    }

    /**
     * Отправка всех данных из предварительного массива в Header
     *
     * @access public
     * @todo Реализовать всё через класс \Core\Header
     * @return void
     */
    public function send() {
        foreach($this->data as $current) {
            list($name, $value, $expire, $path, $domain, $secure, $httponly) = $current;

            if(!empty($domain)) {
                if(preg_match('#^www\.#i', $domain)) {
                    $domain = substr($domain, 4);
                }
                $domain = '.' . $domain;

                if(($position = strpos($domain, ':')) !== false) {
                    $domain = substr($domain, 0, $position);
                }
            }

            header('Set-Cookie: '
                   . rawurlencode($name)
                   . '='
                   . rawurlencode($value)
                   . (empty($expire) ? '' : '; expires=' . gmdate('D, d-M-Y H:i:s', $expire) . ' GMT')
                   . (empty($path) ? '' : '; path=' . $path)
                   . (empty($domain) ? '' : '; domain=' . $domain)
                   . ($secure ? '; secure' : '')
                   . ($httponly ? '; HttpOnly' : ''), false);
        }
    }

    /**
     * Получение значения Cookie в Header
     *
     * @access public
     * @param string $name Имя Cookie
     * @return string Возвращает значение Cookie
     */
    public function get($name) {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }

    /**
     * Проверка наличия Cookie в Header
     *
     * @access public
     * @param string $name Имя Cookie
     * @return boolean Возвращает true если проверка прошла успешно и false если проверка не прошла
     */
    public function has($name) {
        return isset($_COOKIE[$name]);
    }

    private function __clone() {
    }

    private function __wakeup() {
    }
}

?>