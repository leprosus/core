<?php
namespace Core;

/**
 * Router - Парсер URI запросов
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 1.0
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class Router extends Common {
    private static $instance;
    private $query;
    private $handler;
    private $action;
    private $class;
    private $method;
    private $params;
    private $extensions = array();
    private $namespaces = array();

    /**
     * Получение объекта
     * @access public
     * @static
     * @return \Core\Router
     */
    public static function getInstance() {
        return (Type::isNull(self::$instance)) ? self::$instance = new self() : self::$instance;
    }

    /**
     * Конструктор
     * @access private
     * @return void
     */
    private function __construct() {
        $_GET = array();
    }

    /**
     * Добавляет пакет для анализа пути
     * @access public
     *
     * @param string $packet Имя пакета
     *
     * @return \Core\Router
     */
    public function addNamespace($packet) {
        $this->namespaces[] = '\\' . preg_replace('#(^\\\\|\\\\$)#', '', $packet);

        return $this;
    }

    /**
     * Возвращает список пакетов
     * @access public
     * @return array Список пакетов
     */
    public function getNamespaces() {
        return $this->namespaces;
    }

    /**
     * Возвращает список обработчиков
     * @access private
     * @return array Список обработчиков
     */
    private function getExtensions() {
        return $this->extensions;
    }

    /**
     * Добавляет обработчик
     * @access public
     *
     * @param \Core\Extension\Router $extension Обработчик
     *
     * @return \Core\Router
     */
    public function addExtension(\Core\Extension\Router $extension) {
        $this->extensions[] = $extension;

        return $this;
    }

    /**
     * Обрабатывает запрос
     * @access public
     *
     * @param string $query
     *
     * @return void
     */
    public function apply($query) {
        $query = preg_replace('#&amp;|&\#38;|&\#x26;|&#', '&', $query);

        $this->query = mb_strtolower($query, 'UTF-8');
        $this->handler = null;
        $this->action = null;
        $this->class = null;
        $this->method = null;
        $this->params = array();

        foreach($this->getExtensions() as $extension) {
            if($extension->apply($query, $this)) {
                //Обрабатываем параметры запроса
                if(preg_match('#\?(.*)?$#', $query, $matches)) {
                    $params = $this->splitParams($matches[1]);
                    foreach($params as $key => $value) {
                        $this->addParam($key, $value);
                    }
                }

                break;
            }
        }

        if(Type::isNull($this->handler)) {
            if(preg_match('#^(?:(?:([a-z]|[a-z][a-z\d_-]*[a-z\d]+)(?:/([a-z]|[a-z][a-z\d_-]*[a-z\d]+))?(?:/?\?(.*))?))?$#u', $query, $matches)) {
                //Определяем обработчик, проверяем его существование
                $handler = isset($matches[1]) ? $matches[1] : 'index';
                $this->setHandler($handler);

                //Проверяем действие, проверяем его существование
                $action = isset($matches[2]) && mb_strlen($matches[2], 'UTF-8') > 0 ? $matches[2] : 'index';
                $this->setAction($action);

                //Обрабатываем параметры запроса
                if(isset($matches[3])) {
                    $params = $this->splitParams($matches[3]);
                    foreach($params as $key => $value) {
                        $this->addParam($key, $value);
                    }
                }
            } else {
                Header::sendStatus(404);
            }
        }
    }

    public function splitParams($params) {
        $result = array();

        $params = preg_split('#&#', $params);
        foreach($params as $line) {
            if(preg_match('#^([a-z]|[a-z][a-z\d_-]*[a-z\d]+)(?:=(.*))?$#u', $line, $parts)) {
                $result[$parts[1]] = isset($parts[2]) ? $parts[2] : null;
            }
        }

        return $result;
    }

    /**
     * Устанавливает обработчик и имя класса, если класс существует в пакетах
     * @access public
     *
     * @param string $handler
     *
     * @return \Core\Router
     */
    public function setHandler($handler) {
        $class = '\\' . $this->toCamelStyle($handler, true);
        foreach($this->getNamespaces() as $packet) {
            try {
                $reflection = new \ReflectionClass($packet . $class);
                if(!$reflection->isUserDefined() || !$reflection->isSubclassOf('\\Core\\Content')) {
                    throw new \ReflectionException();
                } else {
                    $this->handler = $handler;
                    $this->class = $packet . $class;
                    break;
                }
            } catch(\ReflectionException $error) {
            }
        }
        if(Type::isNull($this->handler)) {
            Header::sendStatus(404);
        }

        return $this;
    }

    /**
     * Устанавливает имя класса и обработчик, если класс существует
     * @access public
     *
     * @param string $class
     *
     * @return \Core\Router
     */
    public function setClass($class) {
        try {
            $reflection = new \ReflectionClass($class);
            if(!$reflection->isUserDefined() || !$reflection->isSubclassOf('\\Core\\Content')) {
                throw new \ReflectionException();
            } else if(preg_match('#([^\\\\]+)$#i', $class, $matches)) {
                $this->handler = $this->toStandardStyle($matches[1]);
                $this->class = $class;
            }
        } catch(\ReflectionException $error) {
        }
        if(Type::isNull($this->handler)) {
            Header::sendStatus(404);
        }

        return $this;
    }

    /**
     * Устанавливает действие и метод класса обработчика для него
     * @access public
     *
     * @param string $action
     *
     * @return \Core\Router
     */
    public function setAction($action) {
        $method = $this->toCamelStyle($action);
        try {
            $reflection = new \ReflectionMethod($this->class, $method);
            if(!$reflection->isUserDefined() || !$reflection->isPublic() || $reflection->isFinal()) {
                throw new \ReflectionException();
            } else {
                $this->action = $action;
                $this->method = $method;
            }
        } catch(\ReflectionException $error) {
            Header::sendStatus(404);
        }

        return $this;
    }

    /**
     * Устанавливает метод и действие класса обработчика для него
     * @access public
     *
     * @param string $method
     *
     * @return \Core\Router
     */
    public function setMethod($method) {
        try {
            $reflection = new \ReflectionMethod($this->class, $method);
            if(!$reflection->isUserDefined() || !$reflection->isPublic() || $reflection->isFinal()) {
                throw new \ReflectionException();
            } else {
                $this->action = $this->toStandardStyle($method);
                $this->method = $method;
            }
        } catch(\ReflectionException $error) {
            Header::sendStatus(404);
        }

        return $this;
    }

    /**
     * Очищает параметры
     * @return void
     */
    public function clearParams() {
        $this->params = array();

        return $this;
    }

    /**
     * Добавляет параметр
     * @param string $key
     * @param string $value
     *
     * @return void
     */
    public function addParam($key, $value) {
        if(!Type::isNull($this->handler)) {
            $this->params[$key] = $value;
        }

        return $this;
    }

    /**
     * Формирует запрос
     * @access public
     * @static
     *
     * @param string $handler Страница (обязательный параметр)
     * @param string|null $action Действие (необязательный параметр)
     * @param array $params Ассоциированный массив с дополнительными параметрами (необязательный параметр)
     * @param array $anchor Ассоциированный массив с параметрами для якоря (необязательный параметр)<br />Параметры разделяются символом ,
     *
     * @return string Сформированный запрос
     */
    public static function createUrl($handler, $action = null, $params = array(), $anchor = array()) {
        $url = array();
        if((Type::isNull($action) && $handler == 'index')) {
            $handler = null;
        }
        if(Type::isString($handler) && preg_match('#^(?:[a-z]|[a-z][a-z\d_-]*[a-z\d]+)$#u', $handler)) {
            $url[] = mb_strtolower($handler, 'UTF-8');
            if(Type::isString($action) && preg_match('#^(?:[a-z]|[a-z][a-z\d_-]*[a-z\d]+)$#u', $action)) {
                $url[] = '/' . mb_strtolower($action, 'UTF-8');
            }
        }

        if(Type::isArray($params) && count($params) > 0) {
            $list = array();
            foreach($params as $key => $value) {
                if(preg_match('#^(?:[a-z]|[a-z][a-z\d_-]*[a-z\d]+)$#u', $key)) {
                    $list[] = mb_strtolower($key, 'UTF-8') . '=' . rawurlencode($value);
                }
            }
            $url[] = '?' . implode('&', $list);
        }

        if(Type::isArray($anchor) && count($anchor) > 0) {
            $list = array();
            foreach($anchor as $key => $value) {
                if(preg_match('#^(?:[a-z]|[a-z][a-z\d_-]*[a-z\d]+)$#u', $key)) {
                    $list[] = mb_strtolower($key, 'UTF-8') . '=' . rawurlencode($value);
                }
            }
            $url[] = '#' . implode('&', $list);
        }

        return implode('', $url);
    }

    /**
     * Возвращает запрос
     * @access public
     * @return string
     */
    public function getQuery() {
        return $this->query;
    }

    /**
     * Возвращает URL текущей страницы
     * @access public
     * @static
     * @return string URL текущей страницы
     */
    public static function getDomainUrl() {
        $server = Server::getInstance();
        $prefix = $server->getParam('SERVER_NAME', '#.+#', 'localhost');
        $suffix = dirname($server->getParam('SCRIPT_NAME', '#.+#', ''));
        $protocol = 'http' . ($server->hasParam('HTTPS', '#.+#', '') ? 's' : '');
        if(strlen($suffix) > 1) {
            $suffix .= '/';
        }

        return $protocol . '://' . $prefix . $suffix;
    }

    /**
     * Возвращает домен текущей страницы
     * @access public
     * @static
     * @return string Текущее доменное имя
     */
    public static function getDomainName() {
        return Server::getInstance()
                     ->getParam('SERVER_NAME', '#.+#', 'localhost');
    }

    /**
     * Возвращает handler
     * @access public
     *
     * @param string|null $regexp Проверка регулярным выражением (по-умолчанию null)
     * @param string|null $default Значение по-умолчанию, в случае, если параметр не соответствует регулярному выражению (по-умолчанию null)
     *
     * @return string Переменная handler
     */
    public function getHandler($regexp = null, $default = null) {
        return Type::isNull($regexp) ? Filter::screen($this->handler) : (preg_match($regexp, $this->handler) ? $this->handler : $default);
    }

    /**
     * Возвращает action
     * @access public
     *
     * @param string|null $regexp Проверка регулярным выражением (по-умолчанию null)
     * @param string|null $default Значение по-умолчанию, в случае, если параметр не соответствует регулярному выражению (по-умолчанию null)
     *
     * @return string Переменная action
     */
    public function getAction($regexp = null, $default = null) {
        return Type::isNull($regexp) ? Filter::screen($this->action) : (preg_match($regexp, $this->action) ? $this->action : $default);
    }

    /**
     * Возвращает ассоциированный массив с параметрами запроса
     * @access public
     * @return array Массив с параметрами запроса
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * Возвращает название класса обработчика
     * @return string
     */
    public function getClass() {
        return $this->class;
    }

    /**
     * Возвращает название метода класса обработчика
     * @return string
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * Определяет, есть ли параметр в запросе или нет
     * @access public
     *
     * @param string $name Название параметра
     * @param string|null $regexp Проверка регулярным выражением (по-умолчанию null)
     *
     * @return boolean
     */
    function hasParam($name, $regexp = null) {
        return isset($this->params[$name]) && (Type::isNull($regexp) || preg_match($regexp, $this->params[$name]));
    }

    /**
     * Возвращает значение параметра, если неопределён, то null
     * @access public
     *
     * @param string $name Название параметра
     * @param string|null $regexp Проверка регулярным выражением (по-умолчанию null)<br />Без регулярного выражения параметр по выходу экранируется
     * @param string|null $default Значение по-умолчанию, в случае, если параметр не соответствует регулярному выражению (по-умолчанию null)
     *
     * @see    Filter::screen
     * @return object
     */
    function getParam($name, $regexp = null, $default = null) {
        return $this->hasParam($name, $regexp) ? (Type::isNull($regexp) ? Filter::screen($this->params[$name]) : $this->params[$name]) : $default;
    }

    private function __clone() {
    }

    private function __wakeup() {
    }
}

?>