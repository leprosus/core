<?php
namespace Core\Collection;

use Core\Exception;

/**
 * Enum - Класс организации enumeration списков
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.2
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class Enum {
    private $type;
    private $list = array();

    function __construct($type) {
        $this->type = $type;
    }

    public function getType() {
        return $this->type;
    }

    public function count() {
        return count($this->list);
    }

    public function isEmpty() {
        return $this->count() == 0;
    }

    public function clear() {
        $this->list = array();

        return $this;
    }

    public function containsObject($object) {
        if(!$this->checkType($object)) {
            throw new Exception\InvalidArgument('Invalid argument');
        }

        $result = in_array($object, $this->list);

        return $result;
    }

    public function equals(Enum $enum) {
        $result = true;

        if($enum instanceof Enum && $enum->getType() == $this->type) {
            if($this !== $enum) {
                $this->each(function ($object) use ($enum, &$result) {
                    return $result = $enum->containsObject($object);
                });
            }
        }

        return $result;
    }

    public function toArray() {
        return $this->list;
    }

    public function remove($object) {
        if($this->checkType($object)) {
            if(($index = array_search($object, $this->list, true)) !== false) {
                unset($this->list[$index]);
                $this->list = array_values($this->list);
            }
        } else {
            throw new Exception\InvalidArgument('Invalid argument');
        }

        return $this;
    }

    public function each($callback) {
        if(is_callable($callback)) {
            $reflection = new \ReflectionFunction($callback);
            $numberOfParams = $reflection->getNumberOfParameters();
            if($numberOfParams == 0 || $numberOfParams > 3) {
                throw new Exception\InvalidArgument('Callback function should not contain less that one and more than three parameters'
                                                    . "\n"
                                                    . 'Formats:'
                                                    . "\n"
                                                    . '\tcallback($object)'
                                                    . "\n"
                                                    . '\tcallback($index, $object)'
                                                    . "\n"
                                                    . '\tcallback($index, $object, $enum)');
            }
            foreach($this->list as $index => $object) {
                if($numberOfParams == 1) {
                    $arguments = array($object);
                } else if($numberOfParams == 2) {
                    $arguments = array(
                        $index,
                        $object
                    );
                } else if($numberOfParams == 3) {
                    $arguments = array($index, $object, $this);
                }

                if(($result = call_user_func_array($callback, $arguments)) === false) {
                    break;
                }
            }
        } else {
            throw new Exception\InvalidArgument('Invalid argument');
        }

        return $this;
    }

    public function add($object, $index = null) {
        if($this->checkType($object) && (is_null($index) || is_int($index))) {
            $count = $this->count();

            if(is_null($index)) {
                $index = $count;
            }

            if($index > $count) {
                throw new \OutOfRangeException();
            } else {
                if($index == 0) {
                    array_unshift($this->list, $object);
                } else if($index == $count) {
                    array_push($this->list, $object);
                } else {
                    $offset = array_slice($this->list, $index);
                    array_unshift($offset, $object);
                    array_splice($this->list, $index, $count, $offset);
                }
            }
        } else {
            throw new Exception\InvalidArgument('Invalid argument');
        }

        return $this;
    }

    public function addList(Enum $enum, $index = null) {
        if($enum instanceof Enum && $enum->getType() == $this->type && (is_null($index) || is_int($index))) {
            $count = $this->count();

            $listArray = $enum->toArray();
            $listCount = count($listArray);

            if(is_null($index)) {
                $index = $count;
            }

            if($index > $count) {
                throw new \OutOfRangeException();
            } else {
                if($index == 0) {
                    for($i = $listCount - 1; $i >= 0; $i--) {
                        array_unshift($this->list, $listArray[$i]);
                    }
                } else if($index == $count) {
                    for($i = 0; $i < $listCount; $i++) {
                        array_push($this->list, $listArray[$i]);
                    }
                } else {
                    $offset = array_slice($this->list, $index);
                    for($i = $listCount - 1; $i >= 0; $i--) {
                        array_unshift($offset, $listArray[$i]);
                    }
                    array_splice($this->list, $index, $count, $offset);
                }
            }
        } else {
            throw new Exception\InvalidArgument('Invalid argument');
        }

        return $this;
    }

    public function containsIndex($index) {
        if(!is_int($index)) {
            throw new Exception\InvalidArgument('Invalid argument');
        }

        return array_key_exists($index, $this->list);
    }

    public function containsRange($indexFrom, $indexTo) {
        if(!is_int($indexFrom) || !is_int($indexTo)) {
            throw new Exception\InvalidArgument('Invalid argument');
        }

        return array_key_exists($indexTo, $this->list);
    }

    public function get($index) {
        if(!is_int($index)) {
            throw new Exception\InvalidArgument('Invalid argument');
        }

        return isset($this->list[$index]) ? $this->list[$index] : null;
    }

    public function getRange($indexFrom, $indexTo) {
        $list = new static($this->type);

        $count = $this->count();
        if(is_int($indexFrom) && is_int($indexTo)) {
            if($indexFrom > $count || $indexTo > $count) {
                throw new \OutOfRangeException();
            } else {
                if($indexFrom > $indexTo) {
                    list($indexFrom, $indexTo) = array($indexTo, $indexFrom);
                }

                $range = array_slice($this->list, $indexFrom, $indexTo - $indexFrom + 1);
                $list->addList($range);
            }
        } else {
            throw new Exception\InvalidArgument('Invalid argument');
        }

        return $list;
    }

    public function indexOf($object) {
        $result = null;

        if($this->checkType($object)) {
            if(($index = array_search($object, $this->list, true)) !== false) {
                $result = $index;
            }
        } else {
            throw new Exception\InvalidArgument('Invalid argument');
        }

        return $result;
    }

    public function lastIndexOf($object) {
        $result = null;

        if($this->checkType($object)) {
            if(count($indexes = array_keys($this->list, $object, true)) > 0) {
                $result = array_pop($indexes);
            }
        } else {
            throw new Exception\InvalidArgument('Invalid argument');
        }

        return $result;
    }

    public function merge(Enum $enum) {
        $self = $this;

        if($enum instanceof Enum && $enum->getType() == $this->type) {
            $enum->each(function ($value) use ($self) {
                $self->add($value);
            });
        } else {
            throw new Exception\InvalidArgument('Invalid argument');
        }

        return $this;
    }

    public function removeIndex($index) {
        if(is_int($index)) {
            $count = $this->count();

            if($index > $count) {
                throw new Exception\ArgumentOutOfRange('Argument is out of range');
            }

            unset($this->list[$index]);
            $this->list = array_values($this->list);
        } else {
            throw new Exception\InvalidArgument('Invalid argument');
        }

        return $this;
    }

    public function removeRange($indexFrom, $indexTo) {
        $count = $this->count();
        if(is_int($indexFrom) && is_int($indexTo)) {
            if($indexFrom > $count || $indexTo > $count) {
                throw new Exception\ArgumentOutOfRange('Argument is out of range');
            } else {
                if($indexFrom > $indexTo) {
                    list($indexFrom, $indexTo) = array($indexTo, $indexFrom);
                }

                for($i = $indexFrom; $i <= $indexTo; $i++) {
                    unset($this->list[$i]);
                }

                $this->list = array_values($this->list);
            }
        } else {
            throw new Exception\InvalidArgument('Invalid argument');
        }

        return $this;
    }

    public function set($object, $index) {
        if($this->checkType($object) && is_int($index)) {
            $count = $this->count();

            if($index > $count) {
                throw new Exception\ArgumentOutOfRange('Argument is out of range');
            } else {
                $this->list[$index] = $object;
            }
        } else {
            throw new Exception\InvalidArgument('Invalid argument');
        }

        return $this;
    }

    private function checkType($object) {
        return ($type = gettype($object)) == 'object' ? $object instanceof $this->type : $type == $this->type;
    }
}

?>
