<?php
namespace Core;

/**
 * Debuger - Обработчик ошибок и исключений
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.2
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class Debuger {
    const TYPE_CONSOLE = 'console';
    const TYPE_LOG = 'log';
    const TYPE_MAIL = 'mail';

    private static $instance = null;
    private $types;
    private $log;
    private $mail;

    /**
     * Получение объекта
     * @access public
     * @static
     * @return \Core\Debuger
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
        $this->log = \Core\Log::getInstance();
        $this->mail = new \Core\Mail();
    }

    /**
     * Устанавливает типы
     * @access public
     * @param string|array $types
     * @return \Core\Debuger
     */
    public function setTypes($types = null) {
        if(is_string($types)) {
            $this->types = array($types);
        } else if(is_array($types)) {
            $this->types = $types;
        }

        return self::$instance;
    }

    /**
     * Возвращает типы
     * @access public
     * @return array
     */
    public function getTypes() {
        return $this->types;
    }

    /**
     * Возвращает объект Mail
     *
     * @access public
     * @return \Core\Mail
     */
    public function getMailer() {
        return $this->mail;
    }

    /**
     * Инициализация обработчика ошибок
     * @access public
     * @return \Core\Debuger
     */
    public function run() {
        $self = $this;
        $log = $this->log;
        $mail = $this->mail;

        set_error_handler(function ($errNumber, $errString, $errFile, $errLine) use ($self, $log, $mail) {
            restore_error_handler();

            $levelMapping = array(
                \E_ERROR => \Core\Log::LEVEL_ERROR,
                \E_USER_ERROR => \Core\Log::LEVEL_ERROR,
                \E_WARNING => \Core\Log::LEVEL_WARN,
                \E_USER_WARNING => \Core\Log::LEVEL_WARN,
                \E_NOTICE => \Core\Log::LEVEL_WARN,
                \E_USER_NOTICE => \Core\Log::LEVEL_WARN
            );

            $errorTypes = array(
                \E_ERROR => 'ERROR',
                \E_USER_ERROR => 'ERROR',
                \E_WARNING => 'WARN',
                \E_USER_WARNING => 'WARN',
                \E_NOTICE => 'NOTICE',
                \E_USER_NOTICE => 'NOTICE'
            );

            if(in_array($errNumber, array(E_ERROR, E_USER_ERROR, E_WARNING, E_USER_WARNING, E_NOTICE, E_USER_NOTICE))) {
                $data = array();

                $data[]
                    = $errorTypes[$errNumber] . ': [' . $errNumber . '] ' . $errString . "\n" . 'Error on line ' . $errLine . ' in file ' . $errFile;

                if(mb_strlen(\Core\Router::getInstance()
                                         ->getQuery(), 'UTF-8') > 0
                ) {
                    $data[] = 'URL:' . \Core\Router::getInstance()
                                                   ->getQuery();
                }

                if(count($post = \Core\Request::getInstance()
                                              ->getParams()) > 0
                ) {
                    $data[] = 'POST:' . "\n" . var_export($post, true);
                }

                if(count($server = \Core\Server::getInstance()
                                               ->getParams()) > 0
                ) {
                    $data[] = 'SERVER:' . "\n" . var_export($server, true);
                }

                if(!is_null($ip = \Core\Server::getInstance()
                                              ->getIPAddress())
                ) {
                    $data[] = 'IP:' . "\n" . var_export($ip, true);
                }

                if(isset($_SESSION) && count($_SESSION) > 0) {
                    $data[] = 'SESSION:' . "\n" . var_export($_SESSION, true);
                }

                if(isset($_COOKIE) && count($_COOKIE) > 0) {
                    $data[] = 'COOKIE:' . "\n" . var_export($_COOKIE, true);
                }

                $text = implode("\n\n", $data) . "\n";

                if(in_array(\Core\Debuger::TYPE_CONSOLE, $self->getTypes())) {
                    echo $text;
                }

                if(in_array(\Core\Debuger::TYPE_LOG, $self->getTypes())) {
                    if(isset($levelMapping[$errNumber])) {
                        $log->write($text, $levelMapping[$errNumber]);
                    } else {
                        $log->writeError($text);
                    }
                }

                if(in_array(\Core\Debuger::TYPE_MAIL, $self->getTypes())) {
                    $text = str_replace("\n", '<br />', $text);
                    $mail->setText($text);
                }
            }
        });

        set_exception_handler(function (\Exception $exception) use ($self, $log, $mail) {
            $levelMapping = array(
                \Core\Exception\Simple::LEVEL_FATAL => \Core\Log::LEVEL_FATAL,
                \Core\Exception\Simple::LEVEL_ERROR => \Core\Log::LEVEL_ERROR,
                \Core\Exception\Simple::LEVEL_WARN => \Core\Log::LEVEL_WARN
            );

            $data = array();

            $reflection = new \ReflectionClass($exception);
            $isInternal = $reflection->isSubclassOf('\\Core\\Exception\\Simple');

            $level = $isInternal && isset($levelMapping[$exception->getLevel()]) ? $levelMapping[$exception->getLevel()] : \Core\Log::LEVEL_ERROR;

            do {
                $trace = $exception->getTrace();
                foreach($trace as $key => &$value) {
                    $value = "\t" . sprintf('#%s %s: %s(%s)', $key, isset($value['file']) ? $value['file'] . '(' . $value['line'] . ')' : '{closure}', $value['function'],
                                            implode(', ', array_map('gettype', $value['args'])));
                }

                $reflection = new \ReflectionClass($exception);

                $data[] = sprintf('Uncaught %sexception \'%s\' with message \'%s\' in %s: %s' . "\n" . 'Stack trace:' . "\n" . '%s',
                                  $reflection->isSubclassOf('\\Core\\Exception\\Simple') ? $exception->getLevel() . ' ' : '', get_class($exception),
                                  str_replace("\n", ' ', $exception->getMessage()), $exception->getFile(), $exception->getLine(), implode("\n", $trace));
            } while($exception = $exception->getPrevious());

            $text = implode("\n\n", $data);

            if(in_array(\Core\Debuger::TYPE_CONSOLE, $self->getTypes())) {
                echo $text;
            }

            if(in_array(\Core\Debuger::TYPE_LOG, $self->getTypes())) {
                $log->write($text, $level);
            }

            if(in_array(\Core\Debuger::TYPE_MAIL, $self->getTypes())) {
                $mail->setText($text);
            }
        });
    }
}

?>
