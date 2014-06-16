<?php
namespace Core;

/**
 * Gadget - Абстракнтый класс для организации гаджетов
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.4
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
abstract class Gadget extends Content {
    private $extensions = array();

    /**
     * Возвращает данные для визуализации гаджета
     * @access public
     * @abstract
     * @return string Данные для визуализации гаджета
     */
    abstract public function apply();

    /**
     * Добавление обработчика для работы над контентом
     * @access public
     * @param GadgetExtension $extension Название функции обработчика
     * @return \Core\Gadget
     */
    public function addExtension($extension) {
        if($extension instanceof GadgetExtension) {
            $this->extensions[] = $extension;
        }

        return $this;
    }

    /**
     * Возвращает обработчики для работы над контентом
     * @access protected
     * @return array
     */
    protected function getExtensions() {
        return $this->extensions;
    }

    public function __toString() {
        $content = $this->apply();

        return is_null($content) ? '' : $content;
    }
}

?>