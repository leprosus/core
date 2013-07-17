<?php
namespace Core\Extension;

/**
 * GadgetExtension - Абстрактный класс для организации расширений для гаджетов
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.1
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
abstract class Gadget extends \Core\Fast{

    /**
     * Выполняет обработку данных обработчиком
     * @access public
     * @abstract
     * @return mixed Результат работы обработчика
     */
    abstract public function apply();
}
?>
