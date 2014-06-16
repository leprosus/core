<?php
namespace Core\Collection;

use Core\Exception;

/**
 * Map - Класс организации map списков
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.1
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class Map {
    private $keyType;
    private $objectType;
    private $keys = array();
    private $objects = array();
    private $map = array();

    public function __construct($keyType, $objectType) {
        $this->keyType = $keyType;
        $this->objectType = $objectType;
    }

    public function equals(Map $map) {
        $result = true;

        if($map instanceof Map && $map->getKeyType() == $this->keyType && $map->getObjectType() == $this->objectType) {
            if($this !== $map) {
                $this->each(function ($key, $object) use ($map, &$result) {
                    return $result = $map->get($key) === $object;
                });
            }
        }

        return $result;
    }

    public function isEmpty() {
        return $this->count() == 0;
    }

    public function getKeyType() {
        return $this->keyType;
    }

    public function getObjectType() {
        return $this->objectType;
    }

    /**
     * Генерит уникальный хеш для объекта
     * @access private
     * @static
     * @param Object $object Объект, для которого нужно получить хеш
     * @return string Хеш для объекта
     */
    private static function getHash($object) {
        return md5(serialize($object));
    }

    public function merge(Map $map) {
        $self = $this;

        if($map instanceof Map && $map->getKeyType() == $this->keyType && $map->getObjectType() == $this->objectType) {
            $map->each(function ($key, $value) use ($self) {
                $self->set($key, $value);
            });
        } else {
            throw new Exception\InvalidArgument('Invalid argument');
        }

        return $this;
    }

    public function count() {
        return count($this->map);
    }

    public function clear() {
        $this->keys = array();
        $this->objects = array();
        $this->map = array();

        return $this;
    }

    public function containsObject($object) {
        $result = false;

        if($this->checkType($object, $this->objectType)) {
            $result = isset($this->objects[self::getHash($object)]);
        } else {
            throw new Exception\InvalidArgument('Invalid argument');
        }

        return $result;
    }

    public function containsKey($key) {
        $result = false;

        if($this->checkType($key, $this->keyType)) {
            $result = isset($this->keys[self::getHash($key)]);
        } else {
            throw new Exception\InvalidArgument('Invalid argument');
        }

        return $result;
    }

    public function removeObject($object) {
        if(!$this->checkType($object, $this->objectType)) {
            throw new Exception\InvalidArgument('Invalid argument');
        }

        $objectHash = self::getHash($object);
        if(isset($this->objects[$objectHash])) {
            $keyHash = array_search($objectHash, $this->map);

            unset($this->keys[$keyHash]);
            unset($this->objects[$objectHash]);
            unset($this->map[$keyHash]);
        }

        return $this;
    }

    public function removeKey($key) {
        if(!$this->checkType($key, $this->keyType)) {
            throw new Exception\InvalidArgument('Invalid argument');
        }

        $keyHash = self::getHash($key);
        if(isset($this->keys[$keyHash])) {
            $objectHash = $this->map[$keyHash];

            unset($this->keys[$keyHash]);
            unset($this->objects[$objectHash]);
            unset($this->map[$keyHash]);
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
                                                    . '\tcallback($key, $object)'
                                                    . "\n"
                                                    . '\tcallback($key, $object, $map)');
            }
            foreach($this->map as $keyHash => $objectHash) {
                $key = $this->keys[$keyHash];
                $object = $this->objects[$objectHash];

                if($numberOfParams == 1) {
                    $arguments = array($object);
                } else if($numberOfParams == 2) {
                    $arguments = array(
                        $key,
                        $object
                    );
                } else if($numberOfParams == 3) {
                    $arguments = array($key, $object, $this);
                }

                if(($result = call_user_func_array($callback, $arguments)) === false) {
                    break;
                }
            }
        }

        return $this;
    }

    public function get($key) {
        if(!$this->checkType($key, $this->keyType)) {
            throw new Exception\InvalidArgument('Invalid argument');
        }

        $keyHash = self::getHash($key);

        return isset($this->map[$keyHash]) ? $this->objects[$this->map[$keyHash]] : null;
    }

    public function set($key, $object) {
        if(!$this->checkType($key, $this->keyType) || !$this->checkType($object, $this->objectType)) {
            throw new Exception\InvalidArgument('Invalid argument');
        }

        $keyHash = self::getHash($key);
        $objectHash = self::getHash($object);

        $oldObjectHash = isset($this->map[$keyHash]) ? $this->map[$keyHash] : null;

        $this->keys[$keyHash] = $key;
        $this->objects[$objectHash] = $object;
        $this->map[$keyHash] = $objectHash;

        if(!is_null($oldObjectHash) && !in_array($oldObjectHash, $this->map) && isset($this->objects[$oldObjectHash])) {
            unset($this->objects[$oldObjectHash]);
        }

        return $this;
    }

    private function checkType($object, $objectType) {
        return ($type = gettype($object)) == 'object' ? $object instanceof $objectType : $type == $objectType;
    }
}

?>
