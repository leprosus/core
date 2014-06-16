<?php
namespace Core\Extension;

/**
 * RouterExtension - Абстрактный класс для организации расширений для класса маршрутизатора
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.1
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
abstract class Router extends \Core\Fast {

    /**
     * Выполняет проверку полученного запроса и устанавливает необходимый обработчик, действие, ID и параметры
     *
     * @access public
     * @abstract
     * @param string $url Относительный URL для обработки
     * @param \Core\Router $router Объект Router для установки необходимых данных
     * @return boolean Флаг корректности обработанного url
     */
    abstract public function apply($url, $router);
}

?>
