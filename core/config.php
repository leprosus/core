<?php
namespace Core;

/**
 * Config - Класс для хранения настроект
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @method mixed getSomeName() Читает значение конфигурации someName
 * @method mixed isSomeName() Чтение boolean значение конфигурации someName
 * @method mixed hasSomeName() Проверка наличия значение конфигурации someName
 * @property-read mixed someName Читает значение из значение конфигурации someName
 * @version 0.4
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class Config extends Common{
    private $filename;
    private $data = null;

    /**
     * Конструктор
     * @access public
     * @return void
     */
    public function __construct($filename){
        $this->filename = $filename;
    }

    /**
     * Извлекает значения по заданным параметрам из INI-файла
     *
     * @access public
     * @param string $name      Название функции, которую вызывают
     * @param array  $arguments Массив с переданными функции параметрами
     * @return string Возвращает значение параметра
     */
    public function __call($name, $arguments){
        $result = null;

        if(is_null($this->data) && file_exists($this->filename)){
            $this->data = parse_ini_file($this->filename, true);
        }

        if($name == 'get' && count($arguments) == 2 && preg_match('#^[a-z]+$#', $arguments[0]) && preg_match('#^[a-z\d_]+$#', $arguments[1])){
            $section = $arguments[0];
            $option = $arguments[1];

            if(isset($this->data[$section][$option])){
                $result = $this->data[$section][$option];
            }
        } else if(!is_null($this->data) && preg_match('#^get([A-Z][a-z]+)(?:([A-z\d]+))?$#', $name, $matches) && isset($matches[1])){
            $section = mb_strtolower($matches[1], 'UTF-8');
            if(isset($matches[2])){
                $option = $this->toStandardStyle($matches[2]);
                if(isset($this->data[$section][$option])){
                    $result = $this->data[$section][$option];
                }
            } else if(isset($this->data[$section])){
                $result = $this->data[$section];
            }
        }

        return $result;
    }

    /**
     * Записывает в ini-файл
     *
     * @access public
     * @param array   $data    Ассоциированный массив со структурой файла
     * @param boolean $rewrite Признак перезаписи файла (по умолчанию true)
     * @return boolean Возвращает Ассоциированный массив
     */
    public function write($data, $rewrite = true){
        $result = false;

        $content = '';
        foreach($data as $section => $value){
            $content .= '['.$section.']\n';
            foreach($data[$section] as $option => $value){
                $content .= $option.' = '.$data[$section][$option].'\n';
            }
            $content .= '\n';
        }

        $file = fopen($this->filename, file_exists($this->filename) ? ($rewrite ? 'w' : 'a') : 'x');
        if($file){
            if(fwrite($file, $content) !== false){
                $result = true;
            }
            fclose($file);
        }

        return $result;
    }
}
?>
