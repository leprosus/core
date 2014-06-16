<?php
namespace Core\Extension;

/**
 * AcceessExtension - Абстрактный класс для организации расширений для класса проверки прав
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.1
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
abstract class Access extends \Core\Fast {

    /**
     * Выполняет проверку полученного запроса и устанавливает необходимый обработчик, действие, ID и параметры
     *
     * @access public
     * @abstract
     * @param string $actionName Название действия
     * @param int|null $userId ID пользователя
     * @param string $resourceName Название ресурса
     * @param int|null $resourceId ID ресурса
     * @return boolean Флаг корректности обработанного имени класса
     */
    abstract public function apply($actionName, $userId, $resourceName, $resourceId);
}

?>
