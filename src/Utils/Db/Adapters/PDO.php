<?php

namespace Combine\Utils\Db\Adapters;
use Combine\Utils\Db\Adapter;
use Combine\Exception;


require_once __DIR__ . '/../Adapter.php';
require_once __DIR__ . '/../../../Exception.php';


/**
 * Class PDO
 * @package Combine\Utils\Db\Adapters
 */
class PDO implements Adapter {

    /**
     * @var \PDO
     */
    protected $db;

    /**
     * @var string
     */
    protected $quote_identifier_symbol = '`';


    /**
     * @param \PDO $db
     * @throws Exception
     */
    public function __construct($db) {
        $this->setConnection($db);
    }


    /**
     * @param \PDO $db
     * @throws Exception
     */
    public function setConnection($db) {
        if ($db instanceof \PDO) {
            $this->db = $db;
        } else {
            throw new Exception('Error set db connection, need PDO instance');
        }
    }


    /**
     * @return \PDO
     */
    public function getConnection() {
        return $this->db;
    }


    /**
     * @param  string $symbol
     * @return string
     */
    public function setQuoteIdentifierSymbol($symbol) {
        $this->quote_identifier_symbol = $symbol;
    }


    /**
     * Returns the symbol the adapter uses for delimited identifiers.
     *
     * @return string
     */
    public function getQuoteIdentifierSymbol() {
        return $this->quote_identifier_symbol;
    }


    /**
     * Экранирует значение
     * @param  string|array $value
     * @param  int          $type
     * @return string
     */
    public function quote($value, $type = \PDO::PARAM_STR) {

        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->db->quote($val, $type);
            }
            $quoted_value = implode(', ', $value);
        } else {
            $quoted_value = $this->db->quote($value, $type);
        }

        return $quoted_value;
    }


    /**
     * Экранирует значение в строке
     *
     * @param string $string
     * @param string $value
     * @param int    $count
     * @param int    $type
     *
     * @return mixed
     */
    public function quoteInto($string, $value, $count = null, $type = \PDO::PARAM_STR) {

        $quoted_value = $this->quote($value, $type);
        return str_replace('?', $quoted_value, $string, $count);
    }


    /**
     * Экранирует значение
     * @param  string $ident
     * @return mixed
     */
    public function quoteIdentifier($ident) {

        if (is_string($ident)) {
            $ident = explode('.', $ident);
        }

        $q = $this->getQuoteIdentifierSymbol();

        if (is_array($ident)) {
            $segments = array();
            foreach ($ident as $segment) {
                $segments[] = ($q . str_replace($q, "$q$q", $segment) . $q);
            }
            $quoted = implode('.', $segments);

        } else {
            $quoted = ($q . str_replace($q, "$q$q", $ident) . $q);
        }

        return $quoted;
    }


    /**
     * Выполняет SQL запрос
     * @param  string       $sql
     * @param  string|array $bind_params
     * @return bool
     * @throws Exception
     */
    public function query($sql, $bind_params = '') {

        $stmt = $this->prepare($sql);

        if ($stmt) {
            if ($bind_params) $this->bindValue($stmt, $bind_params);
            $result = $this->execute($stmt);

            if ($result === false) {
                $error = $stmt->errorInfo();
                throw new Exception("Failed execute query" . $error[2]);

            } else {
                return true;
            }
        }

        throw new Exception("Failed to prepare statement");
    }


    /**
     * Возвращает ID последней вставленной строки либо последнее значение,
     * которое выдал объект последовательности.
     * @param  string $table_name Имя объекта последовательности, который должен выдать ID.
     * @return string Вернет строку представляющую ID последней добавленной в базу записи.
     */
    public function lastInsertId($table_name = null) {

        return $this->db->lastInsertId($table_name);
    }


    /**
     * Добавление записи или массива записей в указанную таблицу
     *
     * @param  string $table Название таблицы для добавления в нее данных
     * @param  array  $data  Данные для добавления в таблицу
     *
     * @return bool
     * @throws Exception
     */
    public function insert($table, array $data) {

        if ( ! trim($table) || empty($data)) {
            return false;
        }

        $query = $this->buildInsert($table, $data);

        if ( ! $this->query($query['sql'], $query['params'])) {
            throw new Exception("Failed execute query");
        }
        return true;
    }

    /**
     * Обновление записи или массива записей в указанной таблице
     *
     * @param string $table Название таблицы
     * @param array  $data  Данные
     * @param string $where Условие
     *
     * @return bool
     * @throws Exception
     */
    public function update($table, array $data, $where = '') {

        if ( ! trim($table) || empty($data)) {
            return false;
        }

        $query = $this->buildUpdate($table, $data);

        $query['sql'] = $where ? $query['sql'] . ' WHERE ' .  $where : $query['sql'];
        if ( ! $this->query($query['sql'], $query['params'])) {
            throw new Exception("Failed execute query");
        }
        return true;
    }


    /**
     * Удаление записей из таблицы
     *
     * @param string $table Название таблицы
     * @param string $where Условие удаления
     *
     * @return bool
     */
    public function delete($table, $where = '') {

        $where     = $where ? " WHERE {$where}" : '';
        $table     = $this->quoteIdentifier($table);
        $is_delete = $this->query("DELETE FROM {$table}" . $where);

        return $is_delete;
    }



    /**
     * Возващает результат запроса со всеми записями
     *
     * @param  string       $sql         Запрос каторый необходимо выполнить
     * @param  string|array $bind_params Параметры каторые нужно привязать к запросу
     *
     * @return array|bool Результирующий массив данных, либо false в случае ошибки
     * @throws Exception
     */
    public function fetchAll($sql, $bind_params = null) {

        $stmt = $this->prepare($sql);

        if ($stmt) {
            if ($bind_params !== null) $this->bindValue($stmt, $bind_params);
            $result = $this->execute($stmt);

            if ($result === false) {
                $error = $stmt->errorInfo();
                throw new Exception("Failed execute query" . $error[2]);

            } else {
                return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
        }

        throw new Exception("Failed to prepare statement");
    }


    /**
     * Возващает результат запроса с первой строкой
     * из результата запроса
     *
     * @param string       $sql         Запрос каторый необходимо выполнить
     * @param string|array $bind_params Параметры каторые нужно привязать к запросу
     *
     * @return array|bool Результирующий массив данных, либо false в случае ошибки
     * @throws Exception
     */
    public function fetchRow($sql, $bind_params = null) {

        $stmt = $this->prepare($sql);

        if ($stmt) {
            if ($bind_params !== null) $this->bindValue($stmt, $bind_params);
            $result = $this->execute($stmt);

            if ($result === false) {
                $error = $stmt->errorInfo();
                throw new Exception("Failed execute query" . $error[2]);

            } else {
                return $stmt->fetch(\PDO::FETCH_ASSOC);
            }
        }

        throw new Exception("Failed to prepare statement");
    }


    /**
     * Возващает результат запроса с первым столбцом
     * из результата запроса
     *
     * @param string       $sql           Запрос который необходимо выполнить
     * @param string|array $bind_params   Параметры каторые нужно привязать к запросу
     * @param int          $column_number Номер столбца, данные которого необходимо извлечь.
     *
     * @return array|bool Результирующий массив данных, либо false в случае ошибки
     * @throws Exception
     */
    public function fetchCol($sql, $bind_params = null, $column_number = 0) {

        $stmt = $this->prepare($sql);

        if ($stmt) {
            if ($bind_params !== null) $this->bindValue($stmt, $bind_params);
            $result = $this->execute($stmt);

            if ($result === false) {
                $error = $stmt->errorInfo();
                throw new Exception("Failed execute query" . $error[2]);

            } else {
                $column        = array();
                $column_number = $column_number > 0
                    ? $column_number - 1
                    : 0;

                while ($col = $stmt->fetchColumn($column_number)) {
                    $column[] = $col;
                }
                return $column;
            }
        }

        throw new Exception("Failed to prepare statement");
    }


    /**
     * Возващает результат запроса в виде одномерного массива
     * ключами которого выступает первое поле из запроса, а значениями второе поле
     *
     * @param string       $sql         Запрос каторый необходимо выполнить
     * @param string|array $bind_params Параметры каторые нужно привязать к запросу
     *
     * @return array|bool Результирующий массив данных, либо false в случае ошибки
     * @throws Exception
     */
    public function fetchPairs($sql, $bind_params = null) {

        $stmt = $this->prepare($sql);

        if ($stmt) {
            if ($bind_params !== null) $this->bindValue($stmt, $bind_params);
            $result = $this->execute($stmt);

            if ($result === false) {
                $error = $stmt->errorInfo();
                throw new Exception("Failed execute query" . $error[2]);

            } else {
                $pairs = array();

                while ($tmp = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $pairs[current($tmp)] = next($tmp);
                }

                return $pairs;
            }
        }

        throw new Exception("Failed to prepare statement");
    }


    /**
     * Возващает резельтат запроса с первым значением поля, из первой строки запроса
     *
     * @param string       $sql         Запрос каторый необходимо выполнить
     * @param string|array $bind_params Параметры каторые нужно привязать к запросу
     *
     * @return string Первое значение из запроса, либо false в случае ошибки
     * @throws Exception
     */
    public function fetchOne($sql, $bind_params = null) {

        $stmt = $this->prepare($sql);

        if ($stmt) {
            if ($bind_params !== null) $this->bindValue($stmt, $bind_params);
            $result = $this->execute($stmt);

            if ($result === false) {
                $error = $stmt->errorInfo();
                throw new Exception("Failed execute query" . $error[2]);

            } else {
                $row    = $stmt->fetch(\PDO::FETCH_ASSOC);
                $return = '';
                if (is_array($row)) {
                    $return = current($row);
                }

                return $return;
            }
        }

        throw new Exception("Failed to prepare statement");
    }


    /**
     * Выключает режим автоматической фиксации транзакции.
     * @return bool
     *      Возвращает TRUE в случае успешного завершения
     *      или FALSE в случае возникновения ошибки.
     */
    public function beginTransaction() {

        return  $this->db->beginTransaction();
    }


    /**
     * Фиксирует транзакцию, возвращая соединение с базой данных
     * в режим автоматической фиксации до тех пор,
     * пока следующий вызов PDO::beginTransaction() не начнет новую транзакцию.
     * @return bool
     *      Возвращает TRUE в случае успешного завершения
     *      или FALSE в случае возникновения ошибки.
     */
    public function commit() {

        return $this->db->commit();
    }


    /**
     * Откатывает изменения в базе данных сделанные в рамках текущей транзакции,
     * которая была создана методом PDO::beginTransaction().
     * Если активной транзакции нет, будет выброшено исключение PDOException.
     * @return bool
     *      Возвращает TRUE в случае успешного завершения
     *      или FALSE в случае возникновения ошибки.
     */
    public function rollback() {

        return $this->db->rollBack();
    }


    /**
     * Связывает параметр с заданным значением
     *
     * @param \PDOStatement $stmt   Экземпляр запроса
     * @param string|array  $values Значение или массив значений, которые нужно привязать к запросу
     *
     * @return bool
     *      Возвращает TRUE в случае успешного завершения
     *      или FALSE в случае возникновения ошибки.
     */
    protected function bindValue($stmt, $values) {

        $num_parameter = 1;

        if (is_string($values) || is_numeric($values)) {
            $stmt->bindValue($num_parameter, $values);

        } elseif (is_array($values)) {
            foreach ($values as $key => $value) {
                if (is_string($key)) {
                    $stmt->bindValue(':'.$key, $value);
                } else {
                    $stmt->bindValue($num_parameter++, $value);
                }
            }
        }

        return true;
    }


    /**
     * Подготовка запроса к выполнению
     * @param  string             $query
     * @return \PDOStatement|bool
     *      Если СУБД успешно подготовила запрос,
     *      PDO::prepare() возвращает объект PDOStatement.
     *      Если подготовить запрос не удалось, PDO::prepare() возвращает FALSE
     *      или выбрасывает исключение PDOException (зависит от текущего режима обработки ошибок).
     */
    protected function prepare($query) {

        return $this->db->prepare($query);
    }


    /**
     * Запускает подготовленный запрос на выполнение
     * @param  \PDOStatement $stmt Объект PDOStatement
     * @return bool
     *      Возвращает TRUE в случае успешного завершения
     *      или FALSE в случае возникновения ошибки.s
     */
    protected function execute(\PDOStatement $stmt) {

        $stmt->closeCursor();
        $result = $stmt->execute();

        return $result;
    }


    /**
     * Формирование insert запроса
     *
     * @param  string $table Название таблицы
     * @param  array  $data  Массив данных для добавления в таблицу
     *
     * @return array Массив со сформированным запросам на добавление и параметрами к нему
     */
    private function buildInsert($table, array $data) {

        $query_data = array();

        $query_data['fields']       = array();
        $query_data['value_fields'] = array();
        $query_data['params']       = array();

        foreach ($data as $name=>$value) {
            if (is_string($value) || is_numeric($value)) {
                $query_data['fields'][]       = $this->quoteIdentifier($name);
                $query_data['value_fields'][] = '?';
                $query_data['params'][]       = $value;
            }
        }

        $implode_fields       = implode(', ', $query_data['fields']);
        $implode_value_fields = implode(', ', $query_data['value_fields']);

        $table = $this->quoteIdentifier($table);
        $query = array(
            'sql'    => "INSERT INTO {$table} ({$implode_fields}) VALUES ({$implode_value_fields})",
            'params' => $query_data['params']
        );

        return $query;
    }


    /**
     * Формирование update запроса
     *
     * @param  string $table Название таблицы
     * @param  array  $data  Массив данных
     *
     * @return array Массив со сформированным запросом и параметрами к нему
     */
    private function buildUpdate($table, array $data) {

        $query_data = array();

        $query_data['fields'] = array();
        $query_data['params'] = array();

        foreach ($data as $name => $value) {
            if (is_string($value) || is_numeric($value)) {
                $quoted_name = $this->quoteIdentifier($name);
                $query_data['fields'][] = "{$quoted_name} = ?";
                $query_data['params'][] = $value;
            }
        }

        $implode_fields = implode(', ', $query_data['fields']);

        $table = $this->quoteIdentifier($table);
        $query = array(
            'sql'    => "UPDATE {$table} SET {$implode_fields}",
            'params' => $query_data['params']
        );

        return $query;
    }
}