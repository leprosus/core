<?php
namespace Core;

/**
 * Widget - Абстрактный класс для реализации виджетов
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.1
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
abstract class Widget extends Content {

    /**
     * Возвращает данные для визуализации виджета
     * @access public
     * @abstract
     * @param array $params Параметры виджета (по-умолчанию пустой массив)
     * @return string Данные для визуализации виджета
     */
    abstract public function apply($params = array());
}

?>
