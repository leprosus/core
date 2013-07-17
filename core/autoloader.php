<?php
namespace Core;

/**
 * Autoloader - Автоматический загрузчик классов
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 1.0
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class Autoloader{
    private static $instance = null;
    private $extensions = array();
    private $core;

    /**
     * Получение объекта
     * @access public
     * @static
     * @return \Core\Autoloader
     */
    public static function getInstance(){
        if(is_null(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * Конструктор
     * @access private
     * @return void
     */
    private function __construct(){
        $this->core = preg_replace('#\\\\#', '/', __DIR__).'/';
    }

    /**
     * Инициализация автозагрузчика
     * @access public
     * @return \Core\Autoloader
     */
    public function run(){
        spl_autoload_register('self::load');

        return $this;
    }

    /**
     * Возвращает относительный путь к ядру
     * @access private
     * @return string Относительный путь
     */
    private function getCore(){
        return $this->core;
    }

    /**
     * Добавляет обработчик
     * @access public
     * @param AutoloaderExtension $extension Обработчик
     * @return \Core\Autoloader
     */
    public function addExtension($extension){
        if($extension instanceof AutoloaderExtension) {
            $this->extensions[] = $extension;
        }

        return $this;
    }

    /**
     * Возвращает список обработчиков
     * @access private
     * @return array Список обработчиков
     */
    private function getExtensions(){
        return $this->extensions;
    }

    /**
     * Основной механиз автозагрузчика
     * @access private
     * @static
     * @param string $className Имя класса, для определения месторасположения
     * @return void
     */
    private static function load($className){
        $self = self::getInstance();
        $path = null;

        $className = preg_replace(array(
            '#([A-Z]+)([A-Z][a-z]|\d)#e', '#([A-Z]+$|[A-Z]{2})#e'
        ), array('ucfirst(strtolower(\'\\1\')).\'\\2\'', 'ucfirst(strtolower(\'\\1\'))'), $className);

        if(preg_match('#^Core\\\\(.+)#', $className, $matches)){
            $path = $self->getCore().preg_replace(array(
                '#\\\\#', '#^/#', '#(^[A-Z]|/[A-Z])#e', '#([A-Z])#e'
            ), array('/', '', 'strtolower(\'\\1\')', '\'_\'.strtolower(\'\\1\')'), $matches[1]).'.php';
        } else{
            foreach($self->getExtensions() as $item){
                $extension = new $item();
                $path = $extension->apply($className, $this);
                if(!is_null($path)) {
                    break;
                }
            }

            if(is_null($path)) {
                $path = preg_replace(array(
                    '#\\\\#', '#^/#', '#^([A-Z])#e', '#(/[A-Z])#e', '#([A-Z])#e'
                ), array(
                    '/', '', 'strtolower(\'\\1\')', 'strtolower(\'\\1\')', '\'_\'.strtolower(\'\\1\')'
                ), $className).'.php';
            }
        }

        $isExist = file_exists($path);
        if($isExist) {
            include_once($path);
        }

        return $isExist;
    }

    private function __clone(){
    }

    private function __wakeup(){
    }
}

?>