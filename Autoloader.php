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
final class Autoloader {
    private static $instance = null;
    private $extensions = array();
    private $namespaces = array();

    /**
     * Получение объекта
     * @access public
     * @static
     * @return \Core\Autoloader
     */
    public static function getInstance() {
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
    private function __construct() {
        $this->addNamespace('Core', __DIR__);
    }

    /**
     * Инициализация автозагрузчика
     * @access public
     * @return \Core\Autoloader
     */
    public function apply() {
        spl_autoload_register('self::load');

        return $this;
    }

    /**
     * Добавляет путь на пространство имён
     * @param $namespace Пространство имён
     * @param $path Путь
     * @return \Core\Autoloader
     */
    public function addNamespace($namespace, $path) {
        $this->namespaces[$namespace] = preg_replace('#(/|\\\\)$#', '', $path);

        return $this;
    }

    /**
     * Возвращает пространство имён
     * @return array
     */
    public function getNamespaces() {
        return $this->namespaces;
    }

    /**
     * Добавляет обработчик
     * @access public
     * @param AutoloaderExtension $extension Обработчик
     * @return \Core\Autoloader
     */
    public function addExtension($extension) {
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
    private function getExtensions() {
        return $this->extensions;
    }

    /**
     * Основной механиз автозагрузчика
     * @access private
     * @static
     * @param string $className Имя класса, для определения месторасположения
     * @return void
     */
    private static function load($className) {
        $self = self::getInstance();
        $path = null;

        foreach($self->getExtensions() as $item) {
            $extension = new $item();
            $path = $extension->apply($className, self::$instance);
            if(!is_null($path)) {
                break;
            }
        }

        $namespaces = $self->getNamespaces();
        if(is_null($path) && preg_match('#^([^\\\\]+)#', $className, $matches) && isset($namespaces[$matches[1]])) {
            $path = preg_replace(array('#^[^\\\\]+#', '#\\\\#'), array($namespaces[$matches[1]], DIRECTORY_SEPARATOR), $className) . '.php';
        }

        $isExist = file_exists($path);
        if($isExist) {
            include_once($path);
        }

        return $isExist;
    }

    private function __clone() {
    }

    private function __wakeup() {
    }
}

?>