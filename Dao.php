<?php
namespace Core;

/**
 * Dao - Класс для создания DAO прослоек
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.3
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
abstract class Dao extends \Core\Fast {
    private static $constants = array();
    private $filters = array();
    private $sort = array();
    private $conditions = array();
    private $joins = array();
    private $fields = array();
    private $orders = array();
    private $isPrepared = false;

    final private function getConstants() {
        $className = get_called_class();
        if(!isset(self::$constants[$className])) {
            $reflection = new \ReflectionClass($this);
            self::$constants[$className] = $reflection->getConstants();
        }

        return self::$constants[$className];
    }

    /**
     * Генерирует SQL LIMIT для постраничной выборки информации
     *
     * @final
     * @access protected
     * @param int $page
     * @param int $perPage
     * @throws \Core\Exception\InvalidArgument
     * @return string
     */
    final protected function generateLimit($page, $perPage) {
        if(!Type::isDecimal($page) || !Type::isDecimal($perPage) || $page < 1 || $perPage < 1) {
            throw new Exception\InvalidArgument('Set incorrect LIMIT arguments');
        }

        return sprintf(' LIMIT %d, %d', ($page - 1) * $perPage, $perPage);
    }

    /**
     * Добавляет фильтр
     * @final
     * @access public
     * @param string $type Тип фильтра
     * @param int|string|array|null $param Параметр фильра (по-умолчанию null)
     * @see Смотри константы объекта
     * @return \Core\Dao
     */
    final public function addFilter($type, $param = null) {
        if(!in_array($type, $this->getConstants())) {
            throw new Exception\InvalidArgument('Set incorrect Filter Type');
        } else if(!Type::isInteger($param) && !Type::isString($param) && !Type::isNull($param) && !Type::isArray($param)) {
            throw new Exception\InvalidArgument('Set incorrect Filter Param');
        }

        $this->filters[$type] = $param;
        $this->isPrepared = false;

        return $this;
    }

    /**
     * Удаляет все поля, объединения, условия и сортировки
     * @final
     * @access public
     * @return \Core\Dao
     */
    final public function clear() {
        $this->fields = array();
        $this->joins = array();

        $this->filters = array();
        $this->conditions = array();

        $this->sort = array();
        $this->orders = array();

        $this->isPrepared = false;

        return $this;
    }

    /**
     * Добавляет сортировку
     * @final
     * @access public
     * @param string $type Тип сортировки
     * @see Смотри константы объекта
     * @return \Core\Dao
     */
    final public function addSort($type) {
        if(!in_array($type, $this->getConstants())) {
            throw new Exception\InvalidArgument('Set incorrect Sort Type');
        }

        $this->sort[] = $type;
        $this->sort = array_unique($this->sort);
        $this->isPrepared = false;

        return $this;
    }

    /**
     * Возвращает флаг: был ли установлен фильтр
     * @final
     * @access protected
     * @param string $type Тип фильтра
     * @see Смотри константы объекта
     * @return boolean
     */
    final protected function hasFilter($type) {
        return in_array($type, array_keys($this->filters));
    }

    /**
     * Возвращает ID фильтра
     *
     * @final
     * @access protected
     * @param string $type Тип фильтра
     * @see Смотри константы объекта
     * @return int|null
     */
    final protected function getFilterParam($type) {
        return $this->hasFilter($type) ? $this->filters[$type] : null;
    }

    /**
     * Возвращает флаг: была ли установлена сортировка
     * @final
     * @access protected
     * @param string $type Тип сортировки
     * @see Смотри константы объекта
     * @return boolean
     */
    final protected function hasSort($type) {
        return in_array($type, $this->sort);
    }

    /**
     * Добавляет поле
     * @final
     * @access protected
     * @param string $field Строка с полем
     * @return \Core\Dao
     */
    final protected function addField($field) {
        if(!Type::isString($field)) {
            throw new Exception\InvalidArgument('Set incorrect FIELD arguments');
        }

        $this->fields[] = $field;

        return $this;
    }

    /**
     * Добавляет JOIN
     *
     * @final
     * @access protected
     * @param string $join Строка c JOIN
     * @return \Core\Dao
     */
    final protected function addJoin($join) {
        if(!Type::isString($join)) {
            throw new Exception\InvalidArgument('Set incorrect JOIN arguments');
        }

        $this->joins[] = $join;

        return $this;
    }

    /**
     * Добавляет условие для генерации WHERE
     *
     * @final
     * @access protected
     * @param string $condition Строа условия
     * @return \Core\Dao
     */
    final protected function addCondition($condition) {
        if(!Type::isString($condition)) {
            throw new Exception\InvalidArgument('Set incorrect CONDITION arguments');
        }

        $this->conditions[] = $condition;

        return $this;
    }

    /**
     * Добавляет сортировку для генерации ORDER
     *
     * @final
     * @access protected
     * @param string $order Строа сортировки
     * @return \Core\Dao
     */
    final protected function addOrder($order) {
        if(!Type::isString($order)) {
            throw new Exception\InvalidArgument('Set incorrect ORDER arguments');
        }

        $this->orders[] = $order;

        return $this;
    }

    /**
     * Подготавливает условия и сортировку
     * Необходимо выполнить:
     * 1. Анализ фильтров и сортировок (@see hasFilter, getFilterId, hasSort)
     * 2. Добавить соответствующие условия, объединения и сортировки (@see addCondition, addJoin, addOrder)
     * @abstract
     * @access protected
     * @return \Core\Dao
     */
    abstract protected function prepare();

    /**
     * Генерирует дополнительные поля для запроса
     * @final
     * @access protected
     * @param string $prefix Префикс строки с дополнительными полями (по-умолчанию запятая)
     * @throws \Core\Exception\InvalidArgument
     * @return string
     */
    protected function generateFields($prefix = ', ') {
        if(!Type::isString($prefix)) {
            throw new Exception\InvalidArgument('Set incorrect PREFIX arguments');
        }

        if(!$this->isPrepared) {
            $this->isPrepared = true;
            $this->prepare();
        }

        return count($this->fields) > 0 ? $prefix . implode(', ', array_unique($this->fields)) : '';
    }

    /**
     * Генерирует SQL JOIN цепочку
     *
     * @final
     * @access protected
     * @return string
     */
    protected function generateJoins() {
        if(!$this->isPrepared) {
            $this->isPrepared = true;
            $this->prepare();
        }

        return count($this->joins) > 0 ? ' ' . implode(' ', array_unique($this->joins)) : '';
    }

    /**
     * Генерирует SQL WHERE в соответствии с добавленными и предварительно подготовленными условиями
     *
     * @final
     * @access protected
     * @param string $prefix Префикс строки условия (по-умолчанию WHERE)
     * @throws \Core\Exception\InvalidArgument
     * @return string
     */
    protected function generateConditions($prefix = ' WHERE ') {
        if(!Type::isString($prefix)) {
            throw new Exception\InvalidArgument('Set incorrect PREFIX arguments');
        }

        if(!$this->isPrepared) {
            $this->isPrepared = true;
            $this->prepare();
        }

        return count($this->conditions) > 0 ? $prefix . implode(' AND ', array_unique($this->conditions)) : '';
    }

    /**
     * Генерирует SQL ORDER в соответствии с добавленными и предварительно подготовленными сортировками
     *
     * @final
     * @access protected
     * @param string $prefix Префикс строки условия (по-умолчанию ORDER BY)
     * @throws \Core\Exception\InvalidArgument
     * @return string
     */
    protected function generateOrders($prefix = ' ORDER BY ') {
        if(!Type::isString($prefix)) {
            throw new Exception\InvalidArgument('Set incorrect PREFIX arguments');
        }

        if(!$this->isPrepared) {
            $this->isPrepared = true;
            $this->prepare();
        }

        return count($this->orders) > 0 ? $prefix . implode(', ', array_unique($this->orders)) : '';
    }
}

?>
