<?php
namespace Core\Extension;

/**
 * AutoloaderExtension - Абстрактный класс для организации расширений для класса автозагрузчика
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.1
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
abstract class Autoloader extends \Core\Fast {

    /**
     * Выполняет анализ полученного имени класса и определяет путь до него
     * @access public
     * @abstract
     * @param string $className Имя обрабатываемого класса
     * @return string|null Путь до определёного класса
     */
    abstract public function apply($className);
}

?>
