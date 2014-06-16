<?php
namespace Core;

/**
 * Document - Класс для генерации HTML head тела
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.5
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class Document {
    private static $instance;
    protected $tags = array();

    /**
     * Возвращает объект
     * @access public
     * @static
     * @return \Core\Document
     */
    public static function getInstance() {
        return (self::$instance === null) ? self::$instance = new self() : self::$instance;
    }

    /**
     * Устанавливает значения тега Title
     * @access public
     *
     * @param string $text Значение тега Title
     *
     * @return \Core\Document
     */
    public function setTitle($text) {
        unset($this->tags['title']);

        return $this->addTag('title', array('text' => $text));
    }

    /**
     * Удаляет значение тега Title
     * @access public
     * @return \Core\Document
     */
    public function removeTitle() {
        unset($this->tags['title']);

        return $this;
    }

    /**
     * Устанавливает значение тега Base
     * @access public
     *
     * @param string $text Значение тега Base
     *
     * @return \Core\Document
     */
    public function setBase($text) {
        unset($this->tags['base']);

        return $this->addTag('base', array('text' => $text));
    }

    /**
     * Удаляет значение тега Base
     * @access public
     * @return \Core\Document
     */
    public function removeBase() {
        unset($this->tags['base']);

        return $this;
    }

    /**
     * Добавляет файла стиля
     * @access public
     *
     * @param string $path Путь к файлу стилей
     *
     * @return \Core\Document
     */
    public function addStyle($path) {
        return $this->addTag('style', array('path' => $path));
    }

    /**
     * Удаляет файла стиля
     * @access public
     *
     * @param string $path Путь к файлу стилей
     *
     * @return \Core\Document
     */
    public function removeStyle($path) {
        return $this->removeTag('style', array('path' => $path));
    }

    /**
     * Добавляет файла скрипта
     * @access public
     *
     * @param string $path Путь к файлу скрипта
     *
     * @return \Core\Document
     */
    public function addScript($path) {
        return $this->addTag('script', array('path' => $path));
    }

    /**
     * Удаляет файла скрипта
     * @access public
     *
     * @param string $path Путь к файлу скрипта
     *
     * @return \Core\Document
     */
    public function removeScript($path) {
        return $this->removeTag('script', array('path' => $path));
    }

    /**
     * Добавляет значения тега Meta
     * @access public
     *
     * @param string $name Значение name для тега Meta
     * @param string $content Значение content для тега Meta
     *
     * @return \Core\Document
     */
    public function addMeta($name, $content) {
        return $this->addTag('meta', array('name' => $name, 'content' => $content));
    }

    /**
     * Удаляет значения тега Meta
     * @access public
     *
     * @param string $name Значение name для тега Meta
     * @param string $content Значение content для тега Meta
     *
     * @return \Core\Document
     */
    public function removeMeta($name, $content) {
        return $this->removeTag('meta', array('name' => $name, 'content' => $content));
    }

    /**
     * Добавляет значения тега Meta http-equiv
     * @access public
     *
     * @param string $name Значение http-equiv для тега Meta
     * @param string $content Значение content для тега Meta
     *
     * @return \Core\Document
     */
    public function addMetaHttp($name, $content) {
        return $this->addTag('meta http', array('name' => $name, 'content' => $content));
    }

    /**
     * Удаляет значения тега Meta http-equiv
     * @access public
     *
     * @param string $name Значение http-equiv для тега Meta
     * @param string $content Значение content для тега Meta
     *
     * @return \Core\Document
     */
    public function removeMetaHttp($name, $content) {
        return $this->removeTag('meta http', array('name' => $name, 'content' => $content));
    }

    /**
     * Устанавливает значение favicon
     * @access public
     *
     * @param string $path Путь к файлу
     *
     * @return \Core\Document
     */
    public function setFavIcon($path) {
        unset($this->tags['favicon']);

        return $this->addTag('favicon', array('path' => $path));
    }

    /**
     * Удаляет значение favicon
     * @access public
     *
     * @param string $path Путь к файлу
     *
     * @return \Core\Document
     */
    public function removeFavIcon() {
        unset($this->tags['favicon']);

        return $this;
    }

    /**
     * Добавление значение тега в массив $tags
     * @access protected
     *
     * @param string $name Название тега
     * @param array $attributes Атрибуты тега (ассоциированный массив)
     *
     * @return \Core\Document
     */
    protected function addTag($name, $attributes) {
        $this->tags[$name][] = $attributes;

        return $this;
    }

    /**
     * Удаление значение тега из массива $tags
     * @access protected
     *
     * @param string $name Название тега
     * @param array $attributes Атрибуты тега (ассоциированный массив)
     *
     * @return \Core\Document
     */
    protected function removeTag($name, $attributes) {
        foreach($this->tags[$name] as $index => $array) {
            if($attributes == $array) {
                unset($this->tags[$name][$index]);
            }
        }

        return $this;
    }

    /**
     * Генерация заголовка страницы
     * @access public
     *
     * @param boolean $versionable Устанавливает режим работы с жёстким версионным контролем для всех внешних ресурсов (по-умолчанию FALSE)
     *
     * @return string Возвращает <head> содержание страницы
     */
    public function generate($versionable = false) {
        $result = array();

        ksort($this->tags);

        foreach(array_keys($this->tags) as $tagName) {
            if($tagName == 'base') {
                $result[] = '<base href="' . $this->tags['base'][0]['text'] . '" />';
            } else if($tagName == 'title') {
                $result[] = '<title>' . $this->tags['title'][0]['text'] . '</title>';
            } else if($tagName == 'keywords') {
                $result[] = '<keywords>' . $this->tags['keywords'][0]['text'] . '</keywords>';
            } else if($tagName == 'description') {
                $result[] = '<description>' . $this->tags['description'][0]['text'] . '</description>';
            } else if($tagName == 'meta http') {
                foreach($this->tags['meta http'] as $key => $value) {
                    $result[]
                        = '<meta http-equiv="' . $this->tags['meta http'][$key]['name'] . '" content="' . $this->tags['meta http'][$key]['content'] . '" />';
                }
            } else if($tagName == 'meta') {
                foreach($this->tags['meta'] as $key => $value) {
                    $result[] = '<meta name="' . $this->tags['meta'][$key]['name'] . '" content="' . $this->tags['meta'][$key]['content'] . '" />';
                }
            } else if($tagName == 'style') {
                foreach($this->tags['style'] as $key => $value) {
                    if($versionable && ($file = new File($this->tags['style'][$key]['path'])) && $file->isExist()) {
                        $result[] = '<link rel="stylesheet" href="' . $this->tags['style'][$key]['path'] . '?v=' . $file->getModified() . '" />';
                    } else {
                        $result[] = '<link rel="stylesheet" href="' . $this->tags['style'][$key]['path'] . '" />';
                    }
                }
            } else if($tagName == 'script') {
                foreach($this->tags['script'] as $key => $value) {
                    if($versionable && ($file = new File($this->tags['script'][$key]['path'])) && $file->isExist()) {
                        $result[] = '<script src="' . $this->tags['script'][$key]['path'] . '?v=' . $file->getModified() . '"></script>';
                    } else {
                        $result[] = '<script src="' . $this->tags['script'][$key]['path'] . '"></script>';
                    }
                }
            } else if($tagName == 'favicon') {
                foreach($this->tags['favicon'] as $key => $value) {
                    $result[] = '<link rel="shortcut icon" href="' . $this->tags['favicon'][$key]['path'] . '" />';
                }
            }
        }

        return implode('', array_unique($result));
    }

    private function __clone() {
    }

    private function __wakeup() {
    }
}

?>
