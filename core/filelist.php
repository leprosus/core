<?php
namespace Core;

/**
 * Filelist - Класс для получения списка файлов и/или папок по указанному пути
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.4
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class Filelist{
    const FILES = 'files';
    const DIRS = 'dirs';

    private $path;
    private $regexp;
    private $depth;

    function __construct($path, $regexp = null){
        $this->path = preg_replace('#(/|\\\\)$#', '', $path);
        $this->regexp = $regexp;
    }

    /**
     * Возвращает список файлов и директорий в виде массива, где каждый объект String
     *
     * @access public
     * @param int $depth Глубина просмотра каталога (по умолчанию null)
     * @return \Core\Collection\Enum Cписок файлов и директорий в виде массива
     */
    public function getFileList($depth = null){
        $this->depth = $depth;

        return $this->getFullList($this->path, self::FILES);
    }

    /**
     * Возвращает список директорий в виде массива, где каждый объект String
     *
     * @access public
     * @param int $depth Глубина просмотра каталога (по умолчанию null)
     * @return \Core\Collection\Enum Cписок директорий в виде массива
     */
    public function getDirList($depth = null){
        $this->depth = $depth;

        return $this->getFullList($this->path, self::DIRS);
    }

    /**
     * Формирует список файлов и директорий
     * @access private
     * @param string $path Путь
     * @param int    $type Тип формирования: директорий и файлов (1) или только директорий (2)
     * @return \Core\Collection\Enum Cписок файлов и директорий в виде массива
     */
    private function getFullList($path, $type, $step = 1){
        $enum = new \Core\Collection\Enum('\\Core\\File');
        if(is_dir($path) && ($dir = opendir($path)) && (is_null($this->depth) || $step <= $this->depth)){
            while(($file = readdir($dir)) !== false){
                if($file != '.' && $file != '..'){
                    $current = $path.'/'.$file;
                    if((is_null($this->regexp) || preg_match($this->regexp, $file)) && ((is_dir($current) && $type == self::DIRS) || (is_file($current) && $type == self::FILES))){
                        $enum->add(new File($current));
                    }

                    if(is_dir($current)){
                        $children = $this->getFullList($current, $type, $step + 1);
                        $enum->addList($children);
                    }
                }
            }
        }

        return $enum;
    }
}

?>