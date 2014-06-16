<?php
namespace Core;

/**
 * Request - Обёртка для работы с POST и FILES
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.8
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class Request extends Common {
    private static $instance = null;
    private $params = array();
    private $files = array();

    /**
     * Получение объекта
     * @access public
     * @static
     * @return \Core\Request
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
        $this->params = $_POST;
        $this->files = $_FILES;
        $_POST = array();
        $_FILES = array();

        foreach($this->files as &$file) {
            $pathinfo = pathinfo($file['name']);
            $file['extension'] = $pathinfo['extension'];
            $file['name'] = $pathinfo['filename'];
        }
    }

    /**
     * Определяет, есть ли параметр в запросе или нет
     * @access public
     * @param string $name Название параметра
     * @param string $regexp Проверка регулярным выражением (<b>параметр необязательный</b>)
     * @see Filter::screen
     * @return boolean
     */
    public function hasParam($name, $regexp = null) {
        return isset($this->params[$name]) && (is_null($regexp) || preg_match($regexp, $this->params[$name]));
    }

    /**
     * Возвращает значение параметра, если неопределён, то null
     *
     * @access public
     * @param string $name Название параметра
     * @param string $regexp Проверка регулярным выражением (по-умолчанию null)<br />Без регулярного выражения параметр по выходу экранируется
     * @param string $default Значение по-умолчанию, в случае, если параметр не соответствует регулярному выражению (по-умолчанию null)
     * @see Filter::screen
     * @return object
     */
    public function getParam($name, $regexp = null, $default = null) {
        return $this->hasParam($name, $regexp) ? (is_null($regexp) ? Filter::screen($this->params[$name]) : $this->params[$name]) : $default;
    }

    /**
     * Заменяет значение параметра запроса
     * @access public
     * @param string $name Название параметра
     * @param string $value Значение параметра
     * @param string $regexp Проверка регулярным выражением (<b>параметр необязательный</b>)
     * @return boolean
     */
    public function setParam($name, $value, $regexp = null) {
        $return = false;

        if($this->hasParam($name, $regexp) && (is_null($regexp) || preg_match($regexp, $value))) {
            $this->params[$name] = $value;
            $return = true;
        }

        return $return;
    }

    /**
     * Добавляет параметр к запросу
     * @access public
     * @param string $name Название параметра
     * @param string $value Значение параметра
     * @param string $regexp Проверка регулярным выражением (<b>параметр необязательный</b>)
     * @return boolean
     */
    public function addParam($name, $value, $regexp = null) {
        $return = false;

        if(is_null($regexp) || preg_match($regexp, $value)) {
            $this->params[$name] = $value;
            $return = true;
        }

        return $return;
    }

    /**
     * Удаляет параметр из запроса
     * @access public
     * @param string $name Название параметра
     * @return boolean
     */
    public function removeParam($name) {
        $return = false;

        if($this->hasParam($name)) {
            unset($this->params[$name]);
            $return = true;
        }

        return $return;
    }

    /**
     * Возвращает ассоциированный массив с параметрами запроса
     * @access public
     * @return array Массив с параметрами запроса
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * Возвращает количество переданных данных
     * @access public
     * @return int
     */
    public function getSize() {
        return count($this->params) + count($this->files);
    }

    /**
     * Возвращает ассоциированный массив с файлами запроса
     * @access public
     * @return array Массив с файлами запроса
     */
    public function getFiles() {
        return $this->files;
    }

    /**
     * Проверяет налицие выгруженного файла в запросе
     * @access public
     * @param string $name Имя поля для выгрудаемого файла
     * @param string|null $extensionRegexp Регулярное выражение для проверки расширения файла (параметр необязательный, по-умолчанию null)
     * @param string|null $nameRegexp Регулярное выражение для проверки имени файла (параметр необязательный, по-умолчанию null)
     * @param int|null $maxSize Максимальный размер файла (параметр необязательный, по-умолчанию null)
     * @return boolean
     */
    public function hasFile($name, $extensionRegexp = null, $nameRegexp = null, $maxSize = null) {
        return isset($this->files[$name])
               && $this->files[$name]['error'] == 0
               && (is_null($extensionRegexp) || preg_match($extensionRegexp, $this->files[$name]['extension']))
               && (is_null($nameRegexp) || preg_match($nameRegexp, $this->files[$name]['name']))
               && (is_null($maxSize) || $this->files[$name]['size'] <= $maxSize);
    }

    /**
     * Возвращает имя файла
     * @access public
     * @param string $name Имя поля для выгрудаемого файла
     * @param string|null $regexp Регулярное выражение для проверки имени файла (параметр необязательный, по-умолчанию null)
     * @return string|null Имя файла
     */
    public function getFileName($name, $regexp = null) {
        return $this->hasFile($name, null, $regexp) ? $this->files[$name]['name'] : null;
    }

    /**
     * Возвращает разширение файла
     * @access public
     * @param string $name Имя поля для выгрудаемого файла
     * @param strinnull $regexp Регулярное выражение для проверки расширения файла (параметр необязательный, по-умолчанию null)
     * @return string|null Расширение файла
     */
    public function getFileExtension($name, $regexp = null) {
        return $this->hasFile($name, $regexp) ? $this->files[$name]['extension'] : null;
    }

    /**
     * Возвращает MIME тип файла
     *
     * @access public
     * @param string $name Имя поля для выгрудаемого файла
     * @return string|null MIME типа
     */
    public function getFileMIME($name) {
        return $this->hasFile($name) ? $this->files[$name]['type'] : null;
    }

    /**
     * Возвращает размер файла
     * @access public
     * @param string $name Имя поля для выгрудаемого файла
     * @return int|null Размер файла
     */
    public function getFileSize($name) {
        return $this->hasFile($name) ? $this->files[$name]['size'] : null;
    }

    /**
     * Возвращает номер ошибки
     * @access public
     * @param string $name Имя поля для выгрудаемого файла
     * @return int|null
     */
    public function getFileError($name) {
        return $this->hasFile($name) ? $this->files[$name]['error'] : null;
    }

    /**
     * Возвращает текст ошибки
     * @access public
     * @param string $name Имя поля для выгрудаемого файла
     * @return string Текст ошибки
     */
    public function getFileErrorMessage($name) {
        $errors = array(
            0 => 'The file uploaded with success',
            1 => 'The file size is too big (see php.ini)',
            2 => 'The file size is too big (see MAX_FILE_SIZE in HTML form)',
            3 => 'The file was partially uploaded',
            4 => 'No file was uploaded',
            6 => 'Missing a temporary folder (see php.ini)',
            7 => 'Failed to write file to disk (see php.ini)',
            8 => 'A PHP extension stopped the file upload (see php.ini)'
        );

        return ($error = $this->getFileError($name)) ? (isset($errors[$error]) ? $errors[$error] : 'Error #' . $error) : '';
    }

    /**
     * Перемещает файл из временной папки в указанную
     * @access public
     * @param string $name Имя поля для выгрудаемого файла
     * @param string $path Папка назначения
     * @param string|null $filename Имя файла при перемещении (параметр необязательный, по умолчанию вставляется принятое от пользователя имя файла)
     * @return boolean Результат перемещения файла
     */
    public function moveFileTo($name, $path, $filename = null) {
        $result = false;

        if($this->hasFile($name)) {
            if(!preg_match('#(\|/)$#', $path)) {
                $path .= '/';
            }

            $path .= is_null($filename) ? $this->files[$name]['name'] . '.' . $this->files[$name]['extension'] : $filename;

            $result = move_uploaded_file($this->files[$name]['tmp_name'], $path);
        }

        return $result;
    }

    private function __clone() {
    }

    private function __wakeup() {
    }
}

?>