<?php
namespace Core;

/**
 * Handler - Абстракнтый класс для организации обработчиков
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.9
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
abstract class Handler extends Content{

    /**
     * Действие обработчика по-умолчанию
     * @access public
     * @abstract
     * @return void
     */
    abstract public function index();
}

?>