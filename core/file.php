<?php
namespace Core;

/**
 * File - Класс для работы с файлами
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.8
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class File{
    const SIZE = 1024;

    private $filePath = '';
    private $charset;
    private $isExist = false;
    private $fileExtension = null;
    private $fileName = null;
    private $fileSize = 0;
    private $fileModified = false;
    private $textPermissions = null;
    private $octalPermissions = null;
    private $isReadable = false;
    private $isWritable = false;
    private $isExecutable = false;
    private $isFile = false;

    /**
     * Конструктор
     * @access public
     * @param string $fileName Расположение файла
     * @param string $charset  Кодировка
     * @return \Core\File
     */
    public function __construct($filePath, $charset = 'UTF-8'){
        $this->filePath = Filter::prepareFilePath($filePath);
        $this->charset = $charset;

        $this->refreshData();
    }

    private function refreshData(){
        $this->isExist = file_exists($this->filePath);
        if($this->isExist){
            $pathInfo = pathinfo($this->filePath);
            $this->fileExtension = isset($pathInfo['extension']) ? $pathInfo['extension'] : null;
            $this->fileName = $pathInfo['filename'];
            $this->fileSize = filesize($this->filePath);
            $this->fileModified = filemtime($this->filePath);
            $this->isReadable = is_readable($this->filePath);
            $this->isWritable = is_writable($this->filePath);
            $this->isExecutable = is_executable($this->filePath);
            $this->isFile = is_file($this->filePath);

            if(!$this->isFile && !preg_match('#/$#', $this->filePath)){
                $this->filePath .= '/';
            }

            if($this->isFile){
                $perms = fileperms($this->filePath);
                if(($perms & 0xC000) == 0xC000){
                    $this->textPermissions = 's';
                } #Socket
                else if(($perms & 0xA000) == 0xA000){
                    $this->textPermissions = 'l';
                } #Symbolic Link
                else if(($perms & 0x8000) == 0x8000){
                    $this->textPermissions = '-';
                } #Regular
                else if(($perms & 0x6000) == 0x6000){
                    $this->textPermissions = 'b';
                } #Block special
                else if(($perms & 0x4000) == 0x4000){
                    $this->textPermissions = 'd';
                } #Directory
                else if(($perms & 0x2000) == 0x2000){
                    $this->textPermissions = 'c';
                } #Character special
                else if(($perms & 0x1000) == 0x1000){
                    $this->textPermissions = 'p';
                } #FIFO pipe
                else{
                    $this->textPermissions = 'u';
                }
                #Unknown
                #Owner
                $this->textPermissions .= (($perms & 0x0100) ? 'r' : '-');
                $this->textPermissions .= (($perms & 0x0080) ? 'w' : '-');
                $this->textPermissions .= (($perms & 0x0040)
                    ? (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800)
                        ? 'S' : '-'));
                #Group
                $this->textPermissions .= (($perms & 0x0020) ? 'r' : '-');
                $this->textPermissions .= (($perms & 0x0010) ? 'w' : '-');
                $this->textPermissions .= (($perms & 0x0008)
                    ? (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400)
                        ? 'S' : '-'));
                #World
                $this->textPermissions .= (($perms & 0x0004) ? 'r' : '-');
                $this->textPermissions .= (($perms & 0x0002) ? 'w' : '-');
                $this->textPermissions .= (($perms & 0x0001)
                    ? (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200)
                        ? 'T' : '-'));

                $this->octalPermissions = substr(sprintf('%o', $perms), -3);
            }
        }
    }

    /**
     * Читает содержимое файла
     * @access public
     * @throws \Core\Exception\FileNotFound
     * @throws \Core\Exception\IO
     * @return string Содержимое файла
     */
    public function read(){
        $this->refreshData();

        if(!$this->isExist()){
            throw new Exception\FileNotFound('File '.Filter::screen($this->filePath).' isn\'t found');
        }
        if(!$this->isFile() || !$this->isReadable || !($file = fopen($this->filePath, 'r'))){
            throw new Exception\IO('File '.Filter::screen($this->filePath).' cann\'t read');
        }

        $result = '';
        while(!feof($file)){
            $result .= fread($file, self::SIZE);
        }

        fclose($file);

        if(($encoding = mb_detect_encoding($result, 'auto', true)) && $encoding !== $this->charset){
            $result = mb_convert_encoding($result, $this->charset, $encoding);
        }

        return $result;
    }

    /**
     * Записывает или перезаписывает файл
     * @access public
     * @param string  $string  Строка данных для записи в файл
     * @param boolean $rewrite Признак перезаписи (по умолчанию true)
     * @throws \Core\Exception\IO
     * @return boolean Возвращает true, если операция записи выполнена успешно
     */
    public function write($string, $rewrite = true){
        $this->refreshData();

        if(!$this->isExist && !file_exists($dirPath = dirname($this->filePath))){
            mkdir($dirPath, '0777', true);
        }

        if(!($file = fopen($this->filePath, $this->isExist ? ($rewrite ? 'w' : 'a') : 'x'))
        ){
            throw new Exception\IO('File '.Filter::screen($this->filePath).' cann\'t write');
        }

        flock($file, \LOCK_EX);

        if(($encoding = mb_detect_encoding($string, 'auto', true)) && $encoding != $this->charset){
            $string = mb_convert_encoding($string, $this->charset, $encoding);
        }
        $return = fwrite($file, $string) !== false;

        flock($file, \LOCK_UN);

        if(!$this->isExist){
            $this->isExist = true;
            $this->isFile = true;
        }

        fclose($file);

        return $return;
    }

    /**
     * Ппроверяет наличие файла
     * @access public
     * @return int Возвращает 1, если файл обнаружен
     */
    public function isExist(){
        return $this->isExist;
    }

    /**
     * Возвращает путь к файлу
     * @access public
     * @return string Путь к файлу
     */
    public function getPath(){
        return $this->filePath;
    }

    /**
     * Возвращает время последней модификации файла
     * @access public
     * @return int время последней модификации файла
     */
    public function getModified(){
        return $this->fileModified;
    }

    /**
     * Возвращает размер файла
     * @access public
     * @return int Размер файла в байтах
     */
    public function getSize(){
        return $this->fileSize;

        return $this->fileSize;
    }

    /**
     * Возвращает расширение файла
     * @access public
     * @return string Тип файла
     */
    public function getExtension(){
        return $this->fileExtension;
    }

    /**
     * Возвращает имя файла
     * @access public
     * @return string Имя файла
     */
    public function getName(){
        return $this->fileName;
    }

    /**
     * Проверяет, является ли объект файлом
     * @access public
     * @return int Возвращает 1, если объект является файлом
     */
    public function isFile(){
        return $this->isFile;
    }

    /**
     * Проверяет, является ли объект директорией
     * @access public
     * @return int Возвращает 1, если объект является директорией
     */
    public function isDir(){
        return !$this->isFile();
    }

    /**
     * Рекурсивно удаляет папку или файл
     * @access public
     * @return void
     */
    public function remove(){
        self::removeItem($this->filePath);
    }

    /**
     * Копирует файл по указанному пути и далее работает с копией ресурса
     * @access public
     * @param string $path Новый путь файла
     * @return boolean
     */
    public function copy($path){
        $result = false;
        if($this->isExist && $this->isFile){
            $result = copy($this->filePath, $path);
            $this->filePath = $path;

            $this->refreshData();
        }

        return $result;
    }

    /**
     * Перемещает файл по указанному пути и далее работает с перемещённым ресурсом
     * @access public
     * @param string $path Новый путь файла
     * @return boolean
     */
    public function move($path){
        $result = false;
        if($this->isExist && $this->isFile){
            $result = copy($this->filePath, $path);
            $this->remove();
            $this->filePath = $path;

            $this->refreshData();
        }

        return $result;
    }

    /**
     * Возвращает права доступа в текстовом формате
     * @access public
     * @return string|null Права доступа
     */
    public function getTextPermissions(){
        return $this->textPermissions;
    }

    /**
     * Возвращает права доступа в цифровом формате
     * @access public
     * @return string|null Права доступа
     */
    public function getPermissions(){
        return $this->octalPermissions;
    }

    /**
     * Устанавливает права доступа в цифровом формате
     * @access public
     * @param string $permissions Права доступа в цифровом формате (Пример: 0777)
     * @return boolean Возвращает true, если операция выполнена успешно
     */
    public function setPermissions($permissions){
        try{
            $result = chmod($this->filePath, $permissions);

            $this->refreshData();
        } catch(Exception $error){
            $result = false;
        }

        return $result;
    }

    /**
     * Возвращает флаг возможности прочесть ресурс
     * @access public
     * @return boolean Флаг на чтение
     */
    public function isReadable(){
        return $this->isReadable;
    }

    /**
     * Возвращает флаг возможности записи в ресурс
     * @access public
     * @return boolean Флаг на запись
     */
    public function isWritable(){
        return $this->isWritable;
    }

    /**
     * Возвращает флаг возможности исполнения ресурса
     * @access public
     * @return boolean Флаг на исполнение
     */
    public function isExecutable(){
        return $this->isExecutable;
    }

    private static function removeItem($path){
        if(is_dir($path)){
            if(($dir = opendir($path))){
                while(($file = readdir($dir)) !== false){
                    if($file != '.' && $file != '..'){
                        $current = $path.'/'.$file;
                        if(is_dir($current)){
                            self::removeItem($current);
                        } else{
                            unlink($current);
                        }
                    }
                }
            }
        } else if(is_file($path)){
            unlink($path);
        }
    }

    public function __toString(){
        return $this->filePath;
    }

    /**
     * Функция определяет MIME тип файла
     *
     * @param string $fileName Имя файла
     * @access public
     * @return string MIME тип файла
     */
    static function getMIMEType($fileName){
        preg_match('#\.?([a-z\d]{2,4})$#i', $fileName, $matches);

        switch(strtolower($matches[1])){
            case 'js':
                $return = 'application/x-javascript';
                break;
            case 'json':
                $return = 'application/json';
                break;
            case 'css':
                $return = 'text/css';
                break;
            case 'xml':
            case 'xsl':
                $return = 'text/xml';
                break;
            case 'doc':
                $return = 'application/msword';
                break;
            case 'xls':
            case 'xlt':
            case 'xlm':
            case 'xld':
            case 'xla':
            case 'xlc':
            case 'xlw':
            case 'xll':
                $return = 'application/vnd.ms-excel';
                break;
            case 'ppt':
            case 'pps':
                $return = 'application/vnd.ms-powerpoint';
                break;
            case 'rtf':
                $return = 'text/rtf';
                break;
            case 'pdf':
                $return = 'application/pdf';
                break;
            case 'htm':
            case 'html':
            case 'shtml':
                $return = 'text/html';
                break;
            case 'txt':
                $return = 'text/plain';
                break;
            case 'mpeg':
            case 'mpg':
            case 'mpe':
                $return = 'video/mpeg';
                break;
            case 'mp3':
                $return = 'audio/mpeg3';
                break;
            case 'wav':
                $return = 'audio/wav';
                break;
            case 'aiff':
            case 'aif':
            case 'aifc':
                $return = 'audio/aiff';
                break;
            case 'avi':
                $return = 'video/msvideo';
                break;
            case 'wmv':
                $return = 'video/x-ms-wmv';
                break;
            case 'mov':
            case 'qt':
                $return = 'video/quicktime';
                break;
            case 'zip':
                $return = 'application/zip';
                break;
            case 'rar':
                $return = 'application/x-rar-compressed';
                break;
            case 'exe':
            case 'msi':
                $return = 'application/x-msdownload';
                break;
            case 'cab':
                $return = 'application/vnd.ms-cab-compressed';
                break;
            case 'tar':
                $return = 'application/x-tar';
                break;
            case 'swf':
            case 'flv':
                $return = 'application/x-shockwave-flash';
                break;
            case 'png':
                $return = 'image/png';
                break;
            case 'jpe':
            case 'jpeg':
            case 'jpg':
                $return = 'image/jpeg';
                break;
            case 'gif':
                $return = 'image/gif';
                break;
            case 'bmp':
                $return = 'image/bmp';
                break;
            case 'ico':
                $return = 'image/vnd.microsoft.icon';
                break;
            case 'tiff':
            case 'tif':
                $return = 'image/tiff';
                break;
            case 'svg':
            case 'svgz':
                $return = 'image/svg+xml';
                break;
            case 'psd':
                $return = 'image/vnd.adobe.photoshop';
                break;
            case 'ai':
            case 'eps':
            case 'ps':
                $return = 'application/postscript';
                break;
            case 'odt':
                $return = 'application/vnd.oasis.opendocument.text';
                break;
            case 'ods':
                $return = 'application/vnd.oasis.opendocument.spreadsheet';
                break;
            case 'hqx':
                $return = 'application/mac-binhex40';
                break;
            case 'cpt':
                $return = 'application/mac-compactpro';
                break;
            case 'csv':
                $return = 'text/csv';
                break;
            case 'bin':
                $return = 'application/binary';
                break;
            case 'dms':
            case 'lha':
            case 'lzh':
            case 'exe':
            case 'class':
            case 'so':
            case 'sea':
            case 'dll':
                $return = 'application/octet-stream';
                break;
            case 'psd':
                $return = 'application/x-photoshop';
                break;
            case 'oda':
                $return = 'application/oda';
                break;
            case 'eps':
                $return = 'application/postscript';
                break;
            case 'ps':
                $return = 'application/postscript';
                break;
            case 'smi':
            case 'smil':
                $return = 'application/smil';
                break;
            case 'mif':
                $return = 'application/vnd.mif';
                break;
            case 'wbxml':
                $return = 'application/wbxml';
                break;
            case 'wmlc':
                $return = 'application/wmlc';
                break;
            case 'dcr':
                $return = 'application/x-director';
                break;
            case 'dir':
                $return = 'application/x-director';
                break;
            case 'dxr':
                $return = 'application/x-director';
                break;
            case 'dvi':
                $return = 'application/x-dvi';
                break;
            case 'gtar':
                $return = 'application/x-gtar';
                break;
            case 'gz':
                $return = 'application/x-gzip';
                break;
            case 'php':
            case 'php4':
            case 'php3':
            case 'phtml':
                $return = 'application/x-httpd-php';
                break;
            case 'phps':
                $return = 'application/x-httpd-php-source';
                break;
            case 'sit':
                $return = 'application/x-stuffit';
                break;
            case 'tar':
            case 'tgz':
                $return = 'application/x-tar';
                break;
            case 'xhtml':
                $return = 'application/xhtml+xml';
                break;
            case 'xht':
                $return = 'application/xhtml+xml';
                break;
            case 'mid':
                $return = 'audio/midi';
                break;
            case 'midi':
                $return = 'audio/midi';
                break;
            case 'mpga':
                $return = 'audio/mpeg';
                break;
            case 'mp2':
                $return = 'audio/mpeg';
                break;
            case 'ram':
                $return = 'audio/x-pn-realaudio';
                break;
            case 'rm':
                $return = 'audio/x-pn-realaudio';
                break;
            case 'rpm':
                $return = 'audio/x-pn-realaudio-plugin';
                break;
            case 'ra':
                $return = 'audio/x-realaudio';
                break;
            case 'rv':
                $return = 'video/vnd.rn-realvideo';
                break;
            case 'txt':
            case 'text':
            case 'log':
                $return = 'text/plain';
                break;
            case 'rtx':
                $return = 'text/richtext';
                break;
            case 'movie':
                $return = 'video/x-sgi-movie';
                break;
            case 'docx':
                $return = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                break;
            case 'xlsx':
                $return = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                break;
            case 'xl':
                $return = 'application/excel';
                break;
            case 'eml':
                $return = 'message/rfc822';
                break;
            default :
                $return = 'unknown/'.$matches[1];
                break;
        }

        return $return;
    }
}

?>