<?php
namespace Core;

/**
 * RSS - Генератор RSS
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.2
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class RSS {
    private $name;
    private $link;
    private $description;
    private $language;
    private $date;
    private $items = array();

    /**
     * Добавляет заголовок к RSS каналу
     *
     * @access public
     * @param string $name Название канала
     * @param string $link Ссылка на канал
     * @param string $description Описание канала
     * @param string $language Язык (по умолчанию - русский)
     * @param string $date Дата и время в формате RFC 822 (по умолчанию - текущие значения)
     * @return \Core\Rss
     */
    public function addRssHeader($name, $link, $description, $language = 'ru', $date = null) {
        $this->name = $name;
        $this->link = $link;
        $this->description = $description;
        $this->language = $language;
        $this->date = is_null($date) ? date('r') : $date;

        return $this;
    }

    /**
     * Добавляет элементы RSS канала
     *
     * @access public
     * @param string $title Название элемента
     * @param string $link Ссылка на элемент
     * @param string $description Описание элемента
     * @param string $date Дата и время в формате RFC 822 (по умолчанию - текущие значения)
     * @return \Core\Rss
     */
    public function addRssItem($title, $link, $description, $date = null) {
        $this->items[] = array($title, $link, $description, is_null($date) ? date('r') : $date);

        return $this;
    }

    /**
     * Генерирует код RSS канала
     *
     * @access public
     * @return string Код RSS канала
     */
    public function generateRss() {
        $rsstext
            =
            '<?xml version="1.0"?><rss version="2.0"><channel>'
            . '<title>{$this->name}</title>'
            . '<link>{$this->link}</link>'
            . '<description>{$this->description}</description>'
            . '<language>{$this->language}</language>'
            . '<lastBuildDate>{$this->date}</lastBuildDate>';
        foreach($this->items as $current) {
            list($title, $link, $description, $date) = $current;
            $rsstext
                .= '<item>' . '<title>{$title}</title>' . '<link>{$link}</link>' . '<description>{$description}</description>' . '<pubDate>{$date}</pubDate>' . '</item>';
        }
        $rsstext .= '</channel></rss>';

        return $rsstext;
    }
}

?>