<?php
namespace Core;

/**
 * Content - Класс для организации объектов, которые управляют контентом
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.1
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
class Content extends Fast {
    private $fileName;
    private $template;
    private $content;
    private $trim = true;

    function __construct($fileName = null) {
        if(!is_null($fileName)) {
            $this->setTemplate($fileName);
        }
    }

    /**
     * Устанавливает шаблон
     * @access public
     * @final
     *
     * @param string $fileName Относительный путь к шаблону
     *
     * @return \Core\Content
     */
    final public function setTemplate($fileName) {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Возвращает текущий контент
     * @access public
     * @final
     * @return string Контент страницы
     */
    final public function getContent() {
        if(is_null($this->content)) {
            if(!is_null($this->fileName)) {
                $file = new File($this->fileName);
                $this->content = $file->read();
                $this->content = is_null($this->content) ? '' : $this->content;
                if($this->trim) {
                    $this->content = preg_replace(array('#\r+#', '#\n\s+#'), array('', "\n"), $this->content);
                }
            } else {
                $this->content = '';
            }

            $this->template = $this->content;
        }

        return $this->content;
    }

    final public function getWidgetedContent() {
        return self::replaceWidgets($this->getContent());
    }

    /**
     * Устанавливает контент до состояни шаблона
     * @access public
     * @final
     * @return \Core\Content
     */
    final public function resetContent() {
        $this->content = $this->template;

        return $this;
    }

    /**
     * Устанавливает новый контент
     * @access public
     * @final
     *
     * @param string Новый контент
     *
     * @return \Core\Content
     */
    final public function setContent($content) {
        $this->content = $content;

        return $this;
    }

    /**
     * Заменяет чать контента
     * @access public
     * @final
     *
     * @param string $field Поле, требуемое замены
     * @param string $content Замещающий контент
     *
     * @return \Core\Content
     */
    final public function replaceContent($field, $content) {
        $content = str_replace($field, Type::isString($content) ? $content : ($content instanceof Content ? $content->__toString() : strval($content)), $this->getContent());
        $this->setContent($content);

        return $this;
    }

    /**
     * Находит поля виджетов и преобразует в их контент
     * @access public
     * @final
     *
     * @param string $content Контент
     *
     * @return void
     */
    final public function applyWidgets() {
        $this->content = self::replaceWidgets($this->getContent());
    }

    /**
     * Рекурсивно находит поля виджетов и преобразует в их контент
     * @access private
     * @final
     * @static
     *
     * @param string $content Контент
     *
     * @return string Результирующий контент
     */
    final protected static function replaceWidgets($content) {
        $widgetsList = array();

        if(is_string($content) && preg_match_all('#%([a-z][a-z\d\\\\_-]*[a-z\d]+)(?:\(([^\)]*)\))?%#i', $content, $matches, PREG_SET_ORDER)) {
            krsort($matches);

            foreach($matches as $widget) {
                $widgetSearch = $widget[0];
                $widgetName = '\\' . preg_replace('#^\\|\\$#', '', $widget[1]);
                $widgetParams = isset($widget[2]) ? explode(',', self::replaceWidgets($widget[2])) : array();
                foreach($widgetParams as &$param) {
                    $param = preg_replace(array('#^(\'|")#', '#(\'|")$#'), '', trim($param));
                }

                try {
                    $reflection = new \ReflectionClass($widgetName);
                    if(!$reflection->isUserDefined() || !$reflection->isSubclassOf('\\Core\\Widget')) {
                        throw new \ReflectionException();
                    } else {
                        $widget = new $widgetName();
                        $widgetReplace = $widget->apply($widgetParams);
                        $widgetsList[$widgetSearch] = self::replaceWidgets($widgetReplace);
                    }
                } catch(\ReflectionException $error) {
                }
            }
        }

        return str_replace(array_keys($widgetsList), $widgetsList, $content);
    }

    public function setTrim($trim) {
        $this->trim = $trim;

        return $this;
    }

    public function getTrim() {
        return $this->trim;
    }

    public function __toString() {
        return $this->getWidgetedContent();
    }
}

?>
