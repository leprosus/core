<?php
namespace Core;

/**
 * Header - Класс для организации работы с HTTP заголовокми
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.5
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 * @todo    Добавить дополнительные заголовки
 * @see     http://ru.wikipedia.org/wiki/Список_заголовков_HTTP
 */
final class Header{
    private static $instance;
    private $headers = array();

    /**
     * Возвращает объекта
     * @access public
     * @return \Core\Header
     */
    public static function getInstance(){
        return (is_null(self::$instance)) ? self::$instance = new self() : self::$instance;
    }

    /**
     * Конструктор
     * @access private
     * @return void
     */
    private function __construct(){
    }

    /**
     * Добавляет значение заголовка в массив $headers
     * @access private
     * @param string $name  Название тега
     * @param string $value Значение заголовок
     * @return \Core\Header
     */
    private function addHeader($name, $value){
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * Возвращает значение установленного заголовка
     * @access private
     * @param string $name Название тега
     * @return string
     */
    private function getHeader($name){
        return isset($this->headers[$name]) ? $this->headers[$name] : null;
    }

    /**
     * Удаляет значение ранее установленного заголовка
     * @access private
     * @param string $name Название тега
     * @return \Core\Header
     */
    private function removeHeader($name){
        if(isset($this->headers[$name])){
            unset($this->headers[$name]);
        }

        return $this;
    }

    /**
     * Устанавливает заголовок Expires
     *
     * @access public
     * @param int $expires Время, через которое предположительно изменится документ
     * @return \Core\Header
     */
    public function setExpires($expires = 0){
        return $this->addHeader('Expires', gmdate('D, d M Y H:i:s', time() + $expires).' GMT');
    }

    /**
     * Возвращает значение заголовка Expires, если он был установлен
     *
     * @access public
     * @return string
     */
    public function getExpires(){
        return $this->getHeader('Expires');
    }

    /**
     * Удаляет заголовок Expires, если он был установлен
     *
     * @access public
     * @return \Core\Header
     */
    public function removeExpires(){
        return $this->removeHeader('Expires');
    }

    /**
     * Устанавливает заголовок Last-Modified
     *
     * @access public
     * @param int $timestamp Время последнего изменения (по-умолчанию null)
     * @return \Core\Header
     */
    public function setLastModified($timestamp = null){
        return $this->addHeader('Last-Modified', gmdate('D, d M Y H:i:s', $timestamp).' GMT');
    }

    /**
     * Возвращает значение заголовка Last-Modified, если он был установлен
     *
     * @access public
     * @return string
     */
    public function getLastModified(){
        return $this->getHeader('Last-Modified');
    }

    /**
     * Удаляет заголовок Last-Modified, если он был установлен
     *
     * @access public
     * @return \Core\Header
     */
    public function removeLastModified(){
        return $this->removeHeader('Last-Modified');
    }

    /**
     * Устанавливает заголовок Date
     *
     * @access public
     * @return \Core\Header
     */
    public function setDate(){
        return $this->addHeader('Date', gmdate('D, d M Y H:i:s').' GMT');
    }

    /**
     * Возвращает значение заголовка Date, если он был установлен
     *
     * @access public
     * @return string
     */
    public function getDate(){
        return $this->getHeader('Date');
    }

    /**
     * Удаляет заголовок Date, если он был установлен
     *
     * @access public
     * @return \Core\Header
     */
    public function removeDate(){
        return $this->removeHeader('Date');
    }

    /**
     * Устанавливает заголовок Cache-Control
     *
     * @param string $type Тип кэширования (public, private, no-cache, no-store)
     * @access public
     * @return \Core\Header
     */
    public function setCacheControl($type = 'public'){
        return $this->addHeader('Cache-Control', $type);
    }

    /**
     * Возвращает значение заголовка Cache-Control, если он был установлен
     *
     * @access public
     * @return string
     */
    public function getCacheControl(){
        return $this->getHeader('Cache-Control');
    }

    /**
     * Удаляет заголовок Cache-Control, если он был установлен
     *
     * @access public
     * @return \Core\Header
     */
    public function removeCacheControl(){
        return $this->removeHeader('Cache-Control');
    }

    /**
     * Устанавливает заголовок Pragma
     *
     * @param string $type Опция операции (public, no-cache)
     * @access public
     * @return \Core\Header
     */
    public function setPragma($value = 'public'){
        return $this->addHeader('Pragma', $value);
    }

    /**
     * Возвращает значение заголовка Pragma, если он был установлен
     *
     * @access public
     * @return string
     */
    public function getPragma(){
        return $this->getHeader('Pragma');
    }

    /**
     * Удаляет заголовок Pragma, если он был установлен
     *
     * @access public
     * @return \Core\Header
     */
    public function removePragma(){
        return $this->removeHeader('Pragma');
    }

    /**
     * Устанавливает заголовок Content-type
     *
     * @param string $type Тип контента (по-умолчанию text/html)
     * @param string $type Тип кодировки (по-умолчанию UTF-8)
     * @access public
     * @return \Core\Header
     */
    public function setContentType($type = 'text/html', $encoding = 'UTF-8'){
        return $this->addHeader('Content-Type', $type.'; charset='.$encoding);
    }

    /**
     * Возвращает значение заголовка Content-Type, если он был установлен
     *
     * @access public
     * @return string
     */
    public function getContentType(){
        return $this->getHeader('Content-Type');
    }

    /**
     * Удаляет заголовок Content-Type, если он был установлен
     *
     * @access public
     * @return \Core\Header
     */
    public function removeContentType(){
        return $this->getHeader('Content-Type');
    }

    /**
     * Устанавливает заголовок Content-Disposition
     *
     * @access public
     * @param array  $data Массив опций
     * @param string $type Тип содержимого (attachment, form-data)
     * @return \Core\Header
     */
    public function setContentDisposition($data, $type = 'attachment'){
        foreach($data as $key => &$value){
            $value = $key.'="'.$value.'"';
        }

        return $this->addHeader('Content-Disposition', $type.'; '.implode('; ', $data));
    }

    /**
     * Возвращает значение заголовка Content-Disposition, если он был установлен
     *
     * @access public
     * @return string
     */
    public function getContentDisposition(){
        return $this->getHeader('Content-Disposition');
    }

    /**
     * Удаляет заголовок Content-Disposition, если он был установлен
     *
     * @access public
     * @return \Core\Header
     */
    public function removeContentDisposition(){
        return $this->removeHeader('Content-Disposition');
    }

    /**
     * Устанавливает заголовок X-Frame-Options
     * Который запрещает сайту загружать контент через frame, iframe
     *
     * @access public
     * @param string $mode Варианты значений DENY;SAMEORIGIN;ALLOW-FROM http:://domain.com
     * @return \Core\Header
     */
    public function setXFrameOptions($mode){
        return preg_match('#^(deny|sameorigin|allow\-from(\shttp(s?)\://[\da-z][\-\._\da-z]+\.[a-z]{2,6})?)$#i', $mode)
            ? $this->addHeader('X-Frame-Options', $mode) : $this;
    }

    /**
     * Возвращает значение заголовка X-Frame-Options, если он был установлен
     *
     * @access public
     * @return string
     */
    public function getXFrameOptions(){
        return $this->getHeader('X-Frame-Options');
    }

    /**
     * Удаляет заголовок X-Frame-Options, если он был установлен
     *
     * @access public
     * @return \Core\Header
     */
    public function removeXFrameOptions(){
        return $this->removeHeader('X-Frame-Options');
    }

    /**
     * Устанавливает заголовок X-Content-Type-Options
     *
     * @access public
     * @return \Core\Header
     */
    public function setXContentTypeOptions(){
        return $this->addHeader('X-Content-Type-Options', 'nosniff');
    }

    /**
     * Возвращает значение заголовка X-Content-Type-Options, если он был установлен
     *
     * @access public
     * @return string
     */
    public function getXContentTypeOptions(){
        return $this->getHeader('X-Content-Type-Options');
    }

    /**
     * Удаляет заголовок X-Content-Type-Options, если он был установлен
     *
     * @access public
     * @return \Core\Header
     */
    public function removeXContentTypeOptions(){
        return $this->removeHeader('X-Content-Type-Options');
    }

    /**
     * Включение встроенной защиты от XSS в браузерах MSIE
     *
     * @return \Core\Header
     */
    public function setXXSSProtection(){
        return $this->addHeader('X-XSS-Protection', '1; mode=block');
    }

    /**
     * Возвращает значение заголовка X-XSS-Protection, если он был установлен
     *
     * @access public
     * @return string
     */
    public function getXXSSProtection(){
        return $this->getHeader('X-XSS-Protection');
    }

    /**
     * Удаляет заголовок X-XSS-Protection, если он был установлен
     *
     * @access public
     * @return \Core\Header
     */
    public function removeXXSSProtection(){
        return $this->removeHeader('X-XSS-Protection');
    }

    /**
     * Указывает, как контент взаимодействует с сайтом
     * @access public
     * @param array $data Массив опций
     * @see https://dvcs.w3.org/hg/content-security-policy/raw-file/tip/csp-specification.dev.html
     * @return \Core\Header
     */
    public function setXContentSecurityPolicy($data){
        return $this->addHeader('X-Content-Security-Policy', implode('; ', $data));
    }

    /**
     * Возвращает значение заголовка X-Content-Security-Policy, если он был установлен
     *
     * @access public
     * @return string
     */
    public function getXContentSecurityPolicy(){
        return $this->getHeader('X-Content-Security-Policy');
    }

    /**
     * Удаляет заголовок X-Content-Security-Policy, если он был установлен
     *
     * @access public
     * @return \Core\Header
     */
    public function removeXContentSecurityPolicy(){
        return $this->removeHeader('X-Content-Security-Policy');
    }

    /**
     * Очищает установленные заголовки
     * @access public
     * @return \Core\Header
     */
    public function clear(){
        $this->headers = array();

        return $this;
    }

    /**
     * Отправляет заголовок статуса и останавливат выполнение
     * @access public
     * @static
     * @param int $code Код статуса
     * @return void
     */
    public static function sendStatus($code = 200){
        \Core\Cookie::getInstance()->send();

        $list = array(
            200 => 'OK', 201 => 'Created', 202 => 'Accepted', 203 => 'Non-Authoritative Information',
            204 => 'No Content', 205 => 'Reset Content', 206 => 'Partial Content', 300 => 'Multiple Choices',
            301 => 'Moved Permanently', 302 => 'Found', 304 => 'Not Modified', 305 => 'Use Proxy',
            307 => 'Temporary Redirect', 400 => 'Bad Request', 401 => 'Unauthorized', 403 => 'Forbidden',
            404 => 'Not Found', 405 => 'Method Not Allowed', 406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required', 408 => 'Request Timeout', 409 => 'Conflict', 410 => 'Gone',
            411 => 'Length Required', 412 => 'Precondition Failed', 413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long', 415 => 'Unsupported Media Type', 416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed', 500 => 'Internal Server Error', 501 => 'Not Implemented', 502 => 'Bad Gateway',
            503 => 'Service Unavailable', 504 => 'Gateway Timeout', 505 => 'HTTP Version Not Supported'
        );

        if(isset($list[$code])){
            $protocol = Server::getInstance()->getParam('SERVER_PROTOCOL', '#^HTTP/1\.[01]$#', 'HTTP/1.0');
            $text = $code.' '.$list[$code];

            header($protocol.' '.$text, true, $code);
            echo '<h1>'.$text.'</h1>';
        }

        die();
    }

    /**
     * Отправляет заголовок перенаправления
     * @access public
     * @static
     * @param string $link Ссылка перенаправления
     * @return void
     */
    public static function sendLocation($link){
        \Core\Cookie::getInstance()->send();

        header('Location: '.$link);
        die();
    }

    /**
     * Отправляет подготовленные заголовки
     * @access public
     * @return void
     */
    public function send(){
        foreach($this->headers as $name => $value){
            header($name.': '.$value);
        }
    }

    private function __clone(){
    }

    private function __wakeup(){
    }
}

?>