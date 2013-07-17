<?php
namespace Core;

/**
 * Object - Класс включает в себя типовые функции, которые используются классами ядра
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.3
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
class Common{

    /**
     * Корректирует camelStyle с учётом аббревиатур в строке
     *
     * @access protected
     * @final
     * @param string $camelStyle Строка в camleStyle
     * @example someUIName -> someUiName
     * @example UISomeName -> UiSomeName
     * @return string
     */
    final protected function correctAbbreviation($camelStyle){
        return preg_replace(array(
            '#([A-Z]+)([A-Z][a-z]|\d)#e', '#([A-Z]+$|[A-Z]{2})#e'
        ), array('ucfirst(strtolower(\'\\1\')).\'\\2\'', 'ucfirst(strtolower(\'\\1\'))'), $camelStyle);
    }

    /**
     * Переводит в standard_style
     *
     * @access protected
     * @final
     * @param string $camelStyle Строка в camleStyle
     * @example someName1 -> some_name_1
     * @return string
     */
    final protected function toStandardStyle($camelStyle){
        $camelStyle = $this->correctAbbreviation($camelStyle);

        return preg_replace(array('#(\d+)#e', '#(^_|_$)#', '#^([A-Z])#e', '#([A-Z])#e'), array(
            '\'_\'.\'\\1\'', '', 'strtolower(\'\\1\')', '\'_\'.strtolower(\'\\1\')'
        ), $camelStyle);
    }

    /**
     * Переводит в camleStyle
     *
     * @access protected
     * @final
     * @param string  $standardStyle Строка в standard_style
     * @param boolean $increaseFirst Увеличить первый символ к заглавной или нет (по-умолчанию FALSE)
     * @example _some_name_1_ -> someName1 - приведение к переменной
     * @example _some_name_1_ -> SomeName1 - приведение к классу
     * @return string
     */
    final protected function toCamelStyle($standardStyle, $increaseFirst = false){
        return is_string($standardStyle) ? preg_replace(array('#(^_|_$)#', '#_([a-z\d])#e', '#^(.)#e'), array(
            '', 'strtoupper(\'\\1\')', $increaseFirst ? 'strtoupper(\'\\1\')' : 'strtolower(\'\\1\')'
        ), $standardStyle) : null;
    }
}
?>
