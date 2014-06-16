<?php
namespace Core;

/**
 * Access - Класс проверки прав доступа
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.5
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class Access extends Common {
    private static $instance;
    private $rights;
    private $session;
    private $sessionName = 'rights';
    private $extensions = array();
    private $lifetime = 0;
    private $refreshFlag = false;

    private function __construct() {
        $this->session = Session::getInstance();
    }

    /**
     * Получение объекта
     * @access public
     * @static
     * @return \Core\Access
     */
    public static function getInstance() {
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Возвращает ID пользователя
     * @access public
     * @return int|null
     */
    public function getUserId() {
        $sessionName = 'get' . $this->getSessionName() . 'UserId';

        return $this->session->$sessionName();
    }

    /**
     * Устанавливает ID пользователя.<br />
     * Необходимо для корректной работы обработчиков.
     * @access public
     *
     * @param int $userId ID пользователя
     *
     * @return \Core\Access
     */
    public function setUserId($userId) {
        $sessionName = 'set' . $this->getSessionName() . 'UserId';
        $this->session->$sessionName($userId);

        return $this;
    }

    /**
     * Проверяет авторизацию пользователя
     * @access public
     * @return boolean
     */
    public function isAuth() {
        return !is_null($this->getUserId());
    }

    /**
     * Возвращает время жизни хранилища прав доступа
     * @access private
     * @return int Время жизни хранилища прав доступа (в секундах)
     */
    private function getLifetime() {
        return $this->lifetime;
    }

    /**
     * Устанавливает время жизни хранилища прав доступа в сессии
     * @access public
     *
     * @param int $lifetime Время жизни хранилища прав доступа в сессии (указывается в минутах)
     *
     * @return \Core\Access
     */
    public function setLifetime($lifetime) {
        $this->lifetime = $lifetime * 60;

        return $this;
    }

    /**
     * Возвращает имя для сохранения данных в сессии
     * @access private
     * @return int Имя для сохранения данных в сессии
     */
    private function getSessionName() {
        return $this->sessionName;
    }

    /**
     * Устанавливает имя для сохранения данных в сессии
     * @access public
     *
     * @param int $sessionName Имя для сохранения данных в сессии
     *
     * @return \Core\Access
     */
    public function setSessionName($sessionName) {
        $this->sessionName = $this->toCamelStyle($sessionName, true);

        return $this;
    }

    /**
     * Добавляет право в хранилище прав доступа
     * @access public
     *
     * @param string $action Название действия, совершаемого над ресурсом
     * @param string $resource Название ресурса
     * @param int $resourceId ID ресурса (параметр необязательный)
     *
     * @return \Core\Access
     */
    public function addRight($action, $resource, $resourceId = null) {
        $this->rights[$resource][$resourceId][$action] = true;

        $sessionName = 'set' . $this->getSessionName();
        $this->session->$sessionName($this->rights);

        return $this;
    }

    /**
     * Добавление обработчика для проверки прав
     * @access public
     *
     * @param \Core\Extension\Access $extension Название функции обработчика
     *
     * @return \Core\Access
     */
    public function addExtension($extension) {
        if($extension instanceof Extension\Access) {
            $this->extensions[] = $extension;
        }

        return $this;
    }

    /**
     * Возвращает обработчики для проверки прав
     * @access private
     * @return array
     */
    private function getExtensions() {
        return $this->extensions;
    }

    /**
     * Проверяет наличие прав для совершения действ над ресурсом
     * @param string $action Название действия, совершаемого над ресурсом
     * @param string $resource Название ресурса
     * @param int $resourceId ID ресурса (параметр необязательный)
     *
     * @return boolean
     */
    public function isAllowed($action, $resource, $resourceId = null) {
        if(!$this->refreshFlag) {
            $sessionName = 'get' . $this->getSessionName() . 'Timestamp';
            $timestamp = $this->session->$sessionName();

            if(is_null($timestamp) || ($timestamp + $this->getLifetime()) < mktime()) {
                $userId = $this->getUserId();

                $sessionName = 'set' . $this->getSessionName();
                $this->session->$sessionName(array());

                $sessionName = 'set' . $this->getSessionName() . 'Timestamp';
                $this->session->$sessionName(mktime());

                $this->setUserId($userId);
            }

            $this->refreshFlag = true;
        }

        $sessionName = 'get' . $this->getSessionName();
        $this->rights = $this->session->$sessionName();

        $result = isset($this->rights[$resource][$resourceId][$action]);
        if(!$result) {
            $list = $this->getExtensions();
            foreach($list as $item) {
                $extension = new $item();
                if($extension->apply($action, $this->getUserId(), $resource, $resourceId)) {
                    $this->addRight($action, $resource, $resourceId);
                    $result = true;
                    break;
                }
            }
        }

        return $result;
    }

    private function __clone() {
    }

    private function __wakeup() {
    }
}

?>