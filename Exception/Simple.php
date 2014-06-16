<?php
namespace Core\Exception;

/**
 * Simple - Абстрактное исключение
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.1
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
abstract class Simple extends \Exception {
    const LEVEL_FATAL = 'FATAL';
    const LEVEL_ERROR = 'ERROR';
    const LEVEL_WARN = 'WARN';

    private $level;

    public function __construct($message, \Exception $exception = null, $level = self::LEVEL_ERROR) {
        parent::__construct($message, 0, $exception);
        $this->level = $level;
    }

    public function getLevel() {
        return $this->level;
    }

    public function __toString() {
        $data = array();

        $exception = $this;

        do {
            $trace = $exception->getTrace();
            foreach($trace as $key => &$value) {
                $value = "\t" . sprintf('#%s %s: %s(%s)', $key, isset($value['file']) ? $value['file'] . '(' . $value['line'] . ')' : '{closure}', $value['function'],
                                        implode(', ', array_map('gettype', $value['args'])));
            }

            $data[] = sprintf('Uncaught %s exception \'%s\' with message \'%s\' in %s: %s' . "\n" . 'Stack trace:' . "\n" . '%s', $exception->getLevel() . ' ',
                              get_class($exception), str_replace("\n", ' ', $exception->getMessage()), $exception->getFile(), $exception->getLine(), implode("\n", $trace));
        } while($exception = $exception->getPrevious());

        return implode("\n\n", $data);
    }
}

?>
