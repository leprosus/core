<?php
namespace Core;

/**
 * Locale - Класс для отправки локализаций
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @method mixed getSomeName() Читает локализации someName
 * @method mixed hasSomeName() Проверка наличия локализации someName
 * @version 0.4
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class Locale extends Common{
    private static $instance;
    private $lang;
    private $default = 'ru';
    private $data = array();
    private $loaded = array();

    /**
     * Получение объекта
     * @access public
     * @static
     * @return \Core\Locale
     */
    public static function getInstance(){
        return (is_null(self::$instance)) ? self::$instance = new self() : self::$instance;
    }

    /**
     * Конструктор
     * @access private
     * @return void
     */
    private function __construct(){
    }

    /**
     * Устанавливает язык локализации
     * @access public
     * @param string $lang
     * @return \Core\Locale
     */
    public function setLang($lang){
        $this->lang = $lang;

        return $this;
    }

    /**
     * Возвращает язык локализации
     * @access public
     * @param string $lang
     * @return void
     */
    public function getLang(){
        return $this->lang;
    }

    /**
     * Устанавливает язык по-умолчанию
     * @access public
     * @param string $lang
     * @return \Core\Locale
     */
    public function setDefaultLang($lang){
        $this->default = $lang;

        return $this;
    }

    /**
     * Загружает данные по локализации
     * @access public
     * @param string $path Путь к директории, где находятся языковые ini файлы.<br />Пример: ru.ini, en.ini
     * @return \Core\Locale
     */
    public function loadData($path){
        if(Type::isNull($this->lang)){
            throw new Exception\Logic('Didn\'t set lang parametre. See \\Core\\Locale::getInstance()->setLang');
        } else if(!Type::isString($path)){
            throw new Exception\InvalidArgument('Set invalid argument in \\Core\\Locale::getInstance()->loadData');
        }
        $fileName = $path.$this->lang.'.ini';

        if(!in_array($path, $this->loaded)){
            $this->loaded[] = $path;

            $path = preg_match('#.+/$#', $path) ? $path : $path.'/';
            $fileName = $path.$this->lang.'.ini';
            if(!file_exists($fileName)){
                $fileName = $path.$this->default.'.ini';
            }

            if(file_exists($fileName) && ($data = parse_ini_file($fileName, true))){
                $this->data = array_merge_recursive($this->data, $data);
            }

            foreach($this->data as &$section){
                foreach($section as &$option){
                    if(is_array($option)){
                        $option = $option[0];
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Возвращает значение по заданным параметрам локализационных файлов
     * @access public
     * @param string $name      Название функции, которую вызывают
     * @param array  $arguments Массив с переданными параметрами
     * @return string Возвращает значение параметра
     */
    public function __call($name, $arguments){
        $result = null;
        if(!is_null($this->data)){
            if($name == 'get' && count($arguments) > 1 && preg_match('#^[a-z]+$#', $arguments[0]) && preg_match('#^[a-z\d_]+$#', $arguments[1])){
                $localeName = $arguments[0].$this->toCamelStyle($arguments[1], true);

                unset($arguments[0]);
                unset($arguments[1]);
                $arguments = array_values($arguments);
            } else if(preg_match('#^get([A-Z][A-z\d]*)$#', $name, $matches)){
                $localeName = $this->toCamelStyle($matches[1]);
            }

            if(isset($localeName)){
                $result = $this->get($localeName, $arguments);
            }
        }

        return $result;
    }

    /**
     * Возвращает локализацию
     * @access public
     * @param string $name Название локализации в формате someName
     * @return mixed Значение локализации
     */
    public function __get($name){
        $name = $this->toCamelStyle($name);

        return $this->get($name, array());
    }

    /**
     * Возвращает локализацию
     * @access private
     * @param string $name      Название локализации в формате someName
     * @param array  $arguments Массив с переданными параметрами
     * @return mixed Значение локализации
     */
    private function get($name, $arguments){
        $result = null;

        if(preg_match('#^([a-z]+)([A-Z][A-z\d]+)$#', $name, $matches)){
            $section = $matches[1];
            $option = $this->toStandardStyle($matches[2]);

            if(isset($this->data[$section][$option])){
                $result = $this->data[$section][$option];
                if(preg_match_all('#\{(\d+)\}#', $result, $matches)){
                    foreach($matches[1] as $index => $number){
                        if(isset($arguments[$number - 1])){
                            $result = str_replace($matches[0][$index], $arguments[$number - 1], $result);
                        }
                    }
                }

                $result = $this->replaceFields($result);
            }
        }

        return $result;
    }

    /**
     * Возвращает ассоциированный массив всей секции
     * @access public
     * @param string  $name       Имя секции
     * @param boolean $camelStyle Преобразует все имена в camelStyle
     * @return array Ассоциированный массив
     */
    public function getSectionData($name, $camelStyle = true){
        $result = isset($this->data[$name]) ? $this->data[$name] : array();

        if($camelStyle){
            foreach($result as $key => $value){
                $newKey = $this->toCamelStyle($key);
                $result[$newKey] = $value;
                if($key != $newKey){
                    unset($result[$key]);
                }
            }
        }

        return $result;
    }

    /**
     * Заменяет все поля, которые ссылаются на другие локали
     * @access private
     * @param string $text Текст
     * @return string
     */
    private function replaceFields($text){
        if(preg_match_all('#\{([a-z]+\.[a-z\d_]+)\}#', $text, $matches)){
            foreach($matches[1] as $index => $field){
                list($section, $option) = explode('.', $field);
                if(isset($this->data[$section][$option])){
                    $text = str_replace($matches[0][$index], $this->data[$section][$option], $text);
                }
            }
        }

        return $text;
    }
}

?>