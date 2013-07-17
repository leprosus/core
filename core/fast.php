<?php
namespace Core;

/**
 * Fast - Класс для упрощения доступа к часто используемым классам и функциям ядра
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.3
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
abstract class Fast extends Redirection{
    private static $register;
    private static $session;
    private static $cookie;
    private static $router;
    private static $request;
    private static $locale;
    private static $access;
    private static $document;
    private static $header;
    private static $server;
    private static $log;

    /**
     * Возвращает синглтон Register
     *
     * @access protected
     * @final
     * @return \Core\Register
     */
    final protected function getRegister(){
        if(is_null(self::$register)){
            self::$register = Register::getInstance();
        }

        return self::$register;
    }

    /**
     * Возвращает синглтон Session
     *
     * @access protected
     * @final
     * @return \Core\Session
     */
    final protected function getSession(){
        if(is_null(self::$session)){
            self::$session = Session::getInstance();
        }

        return self::$session;
    }

    /**
     * Возвращает синглтон Cookie
     *
     * @access protected
     * @final
     * @return \Core\Cookie
     */
    final protected function getCookie(){
        if(is_null(self::$cookie)){
            self::$cookie = Cookie::getInstance();
        }

        return self::$cookie;
    }

    /**
     * Возвращает синглтон Router
     *
     * @access protected
     * @final
     * @return \Core\Router
     */
    final protected function getRouter(){
        if(is_null(self::$router)){
            self::$router = Router::getInstance();
        }

        return self::$router;
    }

    /**
     * Возвращает синглтон Request
     *
     * @access protected
     * @final
     * @return \Core\Request
     */
    final protected function getRequest(){
        if(is_null(self::$request)){
            self::$request = Request::getInstance();
        }

        return self::$request;
    }

    /**
     * Возвращает синглтон Locale
     *
     * @access protected
     * @final
     * @return \Core\Locale
     */
    final protected function getLocale(){
        if(is_null(self::$locale)){
            self::$locale = Locale::getInstance();
        }

        return self::$locale;
    }

    /**
     * Возвращает синглтон Access
     *
     * @access protected
     * @final
     * @return \Core\Access
     */
    final protected function getAccess(){
        if(is_null(self::$access)){
            self::$access = Access::getInstance();
        }

        return self::$access;
    }

    /**
     * Возвращает синглтон Document
     *
     * @access protected
     * @final
     * @return \Core\Document
     */
    final protected function getDocument(){
        if(is_null(self::$document)){
            self::$document = Document::getInstance();
        }

        return self::$document;
    }

    /**
     * Возвращает синглтон Header
     *
     * @access protected
     * @final
     * @return \Core\Header
     */
    final protected function getHeader(){
        if(is_null(self::$header)){
            self::$header = Header::getInstance();
        }

        return self::$header;
    }

    /**
     * Возвращает синглтон Server
     *
     * @access protected
     * @final
     * @return \Core\Log
     */
    final protected function getLog(){
        if(is_null(self::$log)){
            self::$log = Log::getInstance();
        }

        return self::$log;
    }

    /**
     * Возвращает синглтон Server
     *
     * @access protected
     * @final
     * @return \Core\Server
     */
    final protected function getServer(){
        if(is_null(self::$server)){
            self::$server = Server::getInstance();
        }

        return self::$server;
    }

    /**
     * Возвращает объект Config
     *
     * @access protected
     * @final
     * @return \Core\Config
     */
    final protected function getConfig(){
        return $this->getRegister()->getConfig();
    }

    /**
     * Возвращает объект DB
     *
     * @access protected
     * @final
     * @return \Core\DB
     */
    final protected function getDB(){
        return $this->getRegister()->getDB();
    }

    final public function __get($name){
        $name = 'get'.ucfirst($name);
        $reflection = new \ReflectionClass($this);

        return $reflection->hasMethod($name) ? $this->$name() : null;
    }
}

?>