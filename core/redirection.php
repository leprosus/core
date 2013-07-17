<?php
namespace Core;

/**
 * Object - Класс для организации редиректов как внутри классов-обработчиков, так и по URL
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.3
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
class Redirection extends Common{

    /**
     * Осуществляет редирект на ссылку
     * @access protected
     * @param string $link Ссылка, на которую осуществляется редирект
     * @return void
     */
    final protected function redirectTo($link){
        Header::sendLocation($link);
    }

    /**
     * Осуществляет редирект на страницу сайта
     * @access protected
     * @param string $page Ссылка, на которую осуществляется редирект в рамках домена
     * @return void
     */
    final protected function redirectToPage($page){
        $page = Router::getDomainUrl().$page;
        Header::sendLocation($page);
    }

    /**
     * Осуществляет редирект на главную страницу сайта
     * @access protected
     * @param string $path Ссылка, на которую осуществляется редирект
     * @return void
     */
    final protected function redirectToMain(){
        $this->redirectToPage('');
    }

    /**
     * Осуществляет редирект на указанный обработчик
     * @access protected
     * @param string      $handler Страница (обязательный параметр)
     * @param string|null $action  Действие (необязательный параметр)
     * @param array       $params  Ассоциированный массив с дополнительными параметрами (необязательный параметр)
     * @param array       $anchor  Ассоциированный массив с параметрами для якоря (необязательный параметр)<br />Параметры разделяются символом ,
     * @return void
     */
    final protected function redirectToHandler($handler, $action = null, $params = array(), $anchor = array()){
        $this->redirectToPage(Router::createUrl($handler, $action, $params, $anchor));
    }

    /**
     * Перенаправляет выполнение на новое действие обработчика.<br />
     * Применяется только в рамках действий данного обработчика.
     *
     * @access protected
     * @final
     * @param string $action Перенаправляемое действие
     * @param array  $params Ассоциированный массив с дополнительными параметрами (необязательный параметр)
     * @param array  $anchor Ассоциированный массив с параметрами для якоря (необязательный параметр)<br />Параметры разделяются символом ,
     * @return void
     */
    final protected function redirectToAction($action, $params = array(), $anchor = array()){
        $handler = $this->getRouter()->getHandler();
        $this->redirectToHandler($handler, $action, $params, $anchor);
    }

    /**
     * Пименяет другой обработчик и действие.<br />
     *
     * @access protected
     * @final
     * @param string $handlerClass Класс применяемого обработчика
     * @param string $actionMethod Метод обработчика
     * @return void
     */
    final protected function applyHandler($handlerClass, $actionMethod = null){
        $reflection = new \ReflectionClass($handlerClass);
        if($reflection->isUserDefined() && $reflection->isSubclassOf('\\Core\\Handler')){
            if(Type::isNull($actionMethod)){
                $actionMethod = 'index';
            }

            $reflection = new \ReflectionMethod($handlerClass, $actionMethod);
            if($reflection->isUserDefined() && $reflection->isPublic() && !$reflection->isFinal()){
                $handler = new $handlerClass();
                $handler->$actionMethod();
                $this->setContent($handler->getContent());
            }
        }
    }

    /**
     * Пименяет другое действие обработчика.<br />
     * Применяется только в рамках действий данного обработчика.
     *
     * @access protected
     * @final
     * @param string $actionMethod Применяемый метод действия
     * @return void
     */
    final protected function applyAction($actionMethod){
        $reflection = new \ReflectionMethod($this, $actionMethod);
        if($reflection->isUserDefined() && $reflection->isPublic() && !$reflection->isFinal()){
            $this->$actionMethod();
        }
    }

    /**
     * Перезагружает обработчик.<br />
     * Применяется только в рамках данного обработчика.
     *
     * @access protected
     * @final
     * @return void
     */
    final protected function reloadHandler(){
        $handler = $this->getRouter()->getHandler();
        $this->redirectToHandler($handler);
    }

    /**
     * Перезагружает действие.<br />
     * Применяется только в рамках действий данного обработчика.
     *
     * @access protected
     * @final
     * @return void
     */
    final protected function reloadAction(){
        $router = $this->getRouter();

        $action = $router->getAction();
        $params = $router->getParams();

        $this->redirectToAction($action, $params);
    }
}
?>
