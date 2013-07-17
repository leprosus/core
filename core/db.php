<?php
namespace Core;

/**
 * DB - Класс для работы с MySQL базой данных
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.9
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class DB{
    private $db;
    private $autocommit;
    private $cache = array();
    private $lastQuery;

    /**
     * Инициализация объекта
     * @access public
     * @param string $hostname Хостинг
     * @param string $username Имя пользователя
     * @param string $password Пароль
     * @param string $database Имя базы данных
     * @throws \Core\Exception\InvalidArgument(
     * @throws \Core\Exception\SQL
     * @return \Core\DB
     */
    public function __construct($hostname, $username, $password, $database){
        if(!Type::isString($hostname) || !Type::isString($username) || !Type::isString($password) || !Type::isString($database)){
            throw new Exception\InvalidArgument('Set incorrect argument \\Core\\DB->_construct');
        }

        try{
            $this->db = new \PDO('mysql:host='.$hostname.';dbname='.$database, $username, $password);

            //Устанавливаем постоянное соединение
            $this->db->setAttribute(\PDO::ATTR_PERSISTENT, true);

            //Устанавливаем вывод всех ошибок
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            //Устанавливаем автокоммит
            $this->setAutocommit(true);

            //Устанавливаем кодировку по умолчанию
            $this->db->query('SET NAMES utf8;');
            $this->db->query('SET character_set_connection = utf8;');
            $this->db->query('SET character_set_client = utf8;');
            $this->db->query('SET character_set_results = utf8;');
        } catch(\PDOException $error){
            throw new Exception\SQL('Incorrect initialization in \\Core\\DB->_construct'."\n".$error->getMessage());
        }
    }

    /**
     * Инициализирует начало транзакции
     * @access public
     * @return boolean Результат инициализации
     * @throws \Core\Exception\SQL
     * @return boolean Результат выполнения
     */
    public function beginTransaction(){
        $result = false;

        if(!$this->db->inTransaction()){
            //Устанавливаем автокоммит
            $this->setAutocommit(false);

            try{
                //Начинаем транзакцию
                $this->db->beginTransaction();
                $result = true;
            } catch(\PDOException $error){
                throw new Exception\SQL('Incorrect begin of transaction in \\Core\\DB->beginTransaction'."\n".$error->getMessage());
            }
        }

        return $result;
    }

    /**
     * Инициализирует оконцание транзакции и применение всех изменений
     * @access public
     * @return boolean Результат инициализации
     * @throws \Core\Exception\SQL
     * @return boolean Результат выполнения
     */
    public function commitTransaction(){
        $result = false;

        if($this->db->inTransaction()){
            //Устанавливаем автокоммит
            $this->setAutocommit(true);

            try{
                //Коммитим транзакцию
                $this->db->commit();
                $result = true;
            } catch(\PDOException $error){
                throw new Exception\SQL('Incorrect commit of transaction in \\Core\\DB->commitTransaction'."\n".$error->getMessage());
            }
        }

        return $result;
    }

    /**
     * Инициализирует оконцание транзакции и отказ от всех изменений
     * @access public
     * @return boolean Результат инициализации
     * @throws \Core\Exception\SQL
     * @return boolean Результат выполнения
     */
    public function rollbackTransaction(){
        $result = false;

        if($this->db->inTransaction()){
            //Устанавливаем автокоммит
            $this->setAutocommit(true);

            try{
                //Коммитим транзакцию
                $this->db->rollBack();
                $result = true;
            } catch(\PDOException $error){
                throw new Exception\SQL('Incorrect rollback of transaction in \\Core\\DB->rollbackTransaction'."\n".$error->getMessage());
            }
        }

        return $result;
    }

    /**
     * Устанавливает автокоммит для всех операций на изменение
     * @access public
     * @param boolean $autocommit С автокоммитом или без
     * @return void
     */
    private function setAutocommit($autocommit){
        //Устанавливаем автокоммит
        $this->autocommit = $autocommit;
        $this->db->setAttribute(\PDO::ATTR_AUTOCOMMIT, $this->autocommit);
    }

    /**
     * Выаолняет запрос типа UPDATE, INSERT, REPLACE, DELETE
     *
     * @access public
     * @param string $query     Строка запроса
     * @param array  $arguments Аргументы запроса
     * @throws \Core\Exception\InvalidArgument
     * @throws \Core\Exception\SQL
     * @return boolean Результат выполнения запроса
     */
    public function query($query, $arguments = array()){
        if(!Type::isString($query) || !Type::isArray($arguments)){
            throw new Exception\InvalidArgument('Set incorrect argument in \\Core\\DB->query');
        }

        try{
            $hash = md5($query);
            $statement = isset($this->cache[$hash]) ? $this->cache[$hash]
                : ($this->cache[$hash] = $this->db->prepare($query));
            $this->lastQuery = self::prepare($statement, $arguments);
            $result = $statement->execute();
        } catch(\PDOException $error){
            throw new Exception\SQL('Incorrect query '.$this->lastQuery."\n".$error->getMessage());
        }

        return $result;
    }

    /**
     * Выаолняет запрос типа SELECT и возвращает одну запись из базы данных
     *
     * @access public
     * @param string $query     Строка запроса
     * @param array  $arguments Аргументы запроса
     * @throws \Core\Exception\InvalidArgument
     * @throws \Core\Exception\SQL
     * @return array Ассоциированный массив
     */
    public function queryAndFetchOne($query, $arguments = array()){
        if(!Type::isString($query) || !Type::isArray($arguments)){
            throw new Exception\InvalidArgument('Set incorrect argument in \\Core\\DB->queryAndFetchOne');
        }

        if(strpos($query, ' LIMIT ') === false){
            $query = preg_replace('#;$#', '', $query).' LIMIT 1;';
        }
        $data = $this->queryAndFetchAll($query, $arguments);

        return $data[0];
    }

    /**
     * Выаолняет запрос типа SELECT и возвращает все данные по запросу
     *
     * @access public
     * @param string $query     Строка запроса
     * @param array  $arguments Аргументы запроса
     * @throws \Core\Exception\InvalidArgument
     * @throws \Core\Exception\SQL
     * @return array Ассоциированный массив
     */
    public function queryAndFetchAll($query, $arguments = array()){
        if(!Type::isString($query) || !Type::isArray($arguments)){
            throw new Exception\InvalidArgument('Set incorrect argument in \\Core\\DB->queryAndFetchAll');
        }

        $result = null;

        if(preg_match('#^\s*SELECT#i', $query)){

            try{
                $statement = $this->db->prepare($query);
                $statement->setFetchMode(\PDO::FETCH_ASSOC);
                $this->lastQuery = self::prepare($statement, $arguments);
                $statement->execute();
                $result = ($fetch = $statement->fetchAll()) ? $fetch : null;
            } catch(\PDOException $error){
                throw new Exception\SQL('Incorrect query '.$this->lastQuery."\n".$error->getMessage());
            }
        }

        return $result;
    }

    /**
     * Подготавливает запрос к выполнению
     * @access public
     * @static
     * @param \PDOStatement $statement Запрос
     * @param array         $arguments Аргументы запроса
     * @return string Подготовленный запрос в виде текста
     */
    private static function prepare(&$statement, &$arguments){
        $query = $statement->queryString;

        $arguments = self::prepareArgs($arguments);

        if(count($arguments) > 0){
            foreach($arguments as $key => $value){
                if(preg_match('#^:[a-z]+#', $key)){
                    $statement->bindValue($key, $value, Type::isNull($value) ? \PDO::PARAM_NULL
                        : (Type::isInteger($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR));
                    $query = str_replace($key, (Type::isNull($value)
                        ? 'NULL' : (Type::isInteger($value) ? $value
                            : '\''.$value.'\'')), $query);
                    unset($arguments[$key]);
                }
            }

            $arguments = array_values($arguments);
            foreach($arguments as $key => $value){
                $statement->bindValue($key + 1, $value, Type::isNull($value) ? \PDO::PARAM_NULL
                    : (Type::isInteger($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR));
                if(($position = \mb_strpos($query, '?', 0, 'UTF-8')) !== false){
                    $query = mb_substr($query, 0, $position, 'UTF-8').(Type::isNull($value)
                        ? 'NULL'
                        : (Type::isInteger($value) ? $value
                            : '\''.$value.'\'')).mb_substr($query, $position + 1, mb_strlen($query, 'UTF-8'), 'UTF-8');
                }
            }
        }

        return $query;
    }

    /**
     * Подготавливает аргумент запроса
     * @access private
     * @static
     * @param mixed $argumetns
     * @return string Подготовленный аргумент
     */
    private static function prepareArgs($argumetns){
        foreach($argumetns as $key => &$value){
            if(Type::isArray($value)){
                foreach($value as $key => $item){
                    ;
                }
                if(Type::isArray($item)){
                    unset($value[$key]);
                }
                $value = implode(',', self::prepareArgs($value));
            } else if(Type::isNull($value)){
                $value = null;
            } else if(Type::isInteger($value) || Type::isFloat($value)){
                $value = sprintf('%s', $value);
            } else if(is_bool($value)){
                $value = sprintf('%d', $value);
            } else if(Type::isString($value)){
                $value = preg_match('#^(\d{4}-\d{2}-\d{2}(?:\s\d{2}:\d{2}(?::\d{2})?)?|\d{2}:\d{2}(?::\d{2})?)$#', $value)
                    ? $value : Filter::screen($value);
            } else{
                unset($argumetns[$key]);
            }
        }

        return $argumetns;
    }

    /**
     * Возвращает ID последнего INSERT
     *
     * @access public
     * @throws \Core\Exception\SQL
     * @return int|null
     */
    public function getLastId(){
        try{
            $result = $this->db->lastInsertId();
        } catch(\PDOException $error){
            throw new Exception\SQL('Cann\'t get last ID in \\Core\\DB->getLastId'."\n".$error->getMessage());
        }

        return $result;
    }

    /**
     * Возвращает последний вызванный запрос
     * @access public
     * @return string
     */
    public function getLastQuery(){
        return $this->lastQuery;
    }

    /**
     * Деструктор класса, освобождает память
     * @return void
     */
    public function __destroy(){
        $this->db = null;
    }
}

?>