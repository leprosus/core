<?php
namespace Core;

/**
 * Log - Класс для организации логирования
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.2
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class Log extends Common {
    const LEVEL_FATAL = 'FATAL';
    const LEVEL_ERROR = 'ERROR';
    const LEVEL_WARN = 'WARN';
    const LEVEL_DEBUG = 'DEBUG';
    const LEVEL_INFO = 'INFO';
    const LEVEL_ALL = 'ALL';

    private static $instance = null;
    private $path;
    private $level = self::LEVEL_ALL;
    private $datetimeFormat = 'Y-m-d H:i:s';
    private $fileName;
    private $priority
        = array(
            self::LEVEL_FATAL => 1,
            self::LEVEL_ERROR => 3,
            self::LEVEL_WARN => 7,
            self::LEVEL_DEBUG => 15,
            self::LEVEL_INFO => 31,
            self::LEVEL_ALL => 63
        );

    /**
     * Получение объекта
     * @access public
     * @static
     * @return \Core\Log
     */
    public static function getInstance() {
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Конструктор
     * @access private
     * @return void
     */
    private function __construct() {
        $this->path = sys_get_temp_dir();
        $this->fileName = date('Y-m-d') . '.log';
    }

    /**
     * Устанавливает каталог хранения журналов
     * @access public
     * @param string $path Каталог хранения журналов
     * @return \Core\Log
     */
    public function setPath($path) {
        $file = new \Core\File($path);
        $result = $file->isDir() && $file->isWritable();
        if($result) {
            $this->path = $path . (preg_match('#/$#', $path) ? '' : '/');
        }

        return $this;
    }

    /**
     * Устанавливает уровень важности записей
     * @access public
     * @param LEVEL_DEBUG|LEVEL_INFO|LEVEL_WARN|LEVEL_ERROR|LEVEL_FATAL|LEVEL_ALL $level Уровень важности
     * @return \Core\Log
     */
    public function setLevel($level) {
        if(preg_match('#^(DEBUG|INFO|WARN|ERROR|FATAL|ALL)$#', $level)) {
            $this->level = $level;
        }

        return $this;
    }

    /**
     * Устанавливает формат даты и времени
     * @access public
     * @param string $datetimeFormat Формат даты и времени
     * @return \Core\Log
     */
    public function setDatetimeFormat($datetimeFormat) {
        $this->datetimeFormat = $datetimeFormat;

        return $this;
    }

    /**
     * Осуществляет запись в журнал
     * @access public
     * @param string $message Текст записи
     * @param LEVEL_DEBUG|LEVEL_INFO|LEVEL_WARN|LEVEL_ERROR|LEVEL_FATAL $level Уровень важности
     * @return \Core\Log
     */
    public function write($message, $level = self::LEVEL_INFO) {
        if(preg_match('#^(DEBUG|INFO|WARN|ERROR|FATAL)$#', $level)
           && ($this->priority[$this->level] | $this->priority[$level]) == $this->priority[$this->level]
        ) {
            $file = new \Core\File($this->path . $this->fileName);
            $file->write(date($this->datetimeFormat) . ' (' . $level . '): ' . $message . "\n" . str_repeat('*', 80) . "\n", false);
        }

        return $this;
    }

    /**
     * Осуществляет запись в журнал сообщение уровня DEBUG
     *
     * @access public
     * @param string $message Текст записи
     * @return \Core\Log
     */
    public function writeDebug($message) {
        return $this->write($message, self::LEVEL_DEBUG);
    }

    /**
     * Осуществляет запись в журнал сообщение уровня INFO
     *
     * @access public
     * @param string $message Текст записи
     * @return \Core\Log
     */
    public function writeInfo($message) {
        return $this->write($message, self::LEVEL_INFO);
    }

    /**
     * Осуществляет запись в журнал сообщение уровня WARN
     *
     * @access public
     * @param string $message Текст записи
     * @return \Core\Log
     */
    public function writeWarn($message) {
        return $this->write($message, self::LEVEL_WARN);
    }

    /**
     * Осуществляет запись в журнал сообщение уровня ERROR
     *
     * @access public
     * @param string $message Текст записи
     * @return \Core\Log
     */
    public function writeError($message) {
        return $this->write($message, self::LEVEL_ERROR);
    }

    /**
     * Осуществляет запись в журнал сообщение уровня FATAL
     *
     * @access public
     * @param string $message Текст записи
     * @return \Core\Log
     */
    public function writeFatal($message) {
        return $this->write($message, self::LEVEL_FATAL);
    }

    private function __clone() {
    }

    private function __wakeup() {
    }
}

?>
