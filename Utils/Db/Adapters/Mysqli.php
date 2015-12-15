<?php

namespace Combine\Utils\Db\Adapters;
use Combine\Utils\Db\Adapter;
use Combine\Exception;

require_once __DIR__ . '/../Adapter.php';
require_once __DIR__ . '/../../../Exception.php';


/**
 * Class Mysqli
 * @package Combine\Utils\Db\Adapters
 */
class Mysqli implements Adapter {

    /**
     * @var \mysqli
     */
    protected $db;

    /**
     * @var string
     */
    protected $quote_identifier_symbol = '`';


    /**
     * @param \mysqli $db
     * @throws Exception
     */
    public function __construct($db) {
        $this->setConnection($db);
    }


    /**
     * @param \mysqli $db
     * @throws Exception
     */
    public function setConnection($db) {
        if ($db instanceof \mysqli) {
            $this->db = $db;
        } else {
            throw new Exception('Error set db connection, need mysqli instance');
        }
    }


    /**
     * @return \mysqli
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
     * @param  null         $type
     * @return string
     */
    public function quote($value, $type = null) {

        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $q = $this->getQuoteIdentifierSymbol();
                $value[$key] = $q . $this->db->escape_string($value) . $q;
            }
            $quoted_value = implode(', ', $value);
        } else {
            $q = $this->getQuoteIdentifierSymbol();
            $quoted_value = $q . $this->db->escape_string($value) . $q;
        }

        return $quoted_value;
    }


    /**
     * Экранирует значение в строке
     *
     * @param string $string
     * @param string $value
     * @param int    $count
     *
     * @return mixed
     */
    public function quoteInto($string, $value, $count = null) {

        $quoted_value = $this->quote($value);
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
     * Выполняет запрос к базе данных
     *
     * @param string $sql
     *      Объект mysqli
     * @param string|array $bind_params
     *      Текст запроса
     *
     * @return \mysqli_result|bool
     *      Возвращает FALSE в случае неудачи.
     *      В случае успешного выполнения запросов SELECT, SHOW, DESCRIBE или EXPLAIN
     *      mysqli_query() вернет объект mysqli_result.
     *      Для остальных успешных запросов mysqli_query() вернет TRUE.
     * @throws Exception
     */
    public function query($sql, $bind_params = '') {

        $stmt = $this->db->prepare($sql);

        if ($stmt) {
            if ($bind_params) $this->bindValue($stmt, $bind_params);
            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception("Failed execute query\n");

            } else {
                return true;
            }
        }

        throw new Exception("Failed to prepare statement\n");
    }


    /**
     * Связывает параметр с заданным значением
     *
     * @param \mysqli_stmt $stmt
     *      Экземпляр запроса
     * @param string|array $values
     *      Значение или массив значений
     *      которые нужно привязать к запросу
     *
     * @return bool
     *      Возвращает TRUE в случае успешного завершения
     *      или FALSE в случае возникновения ошибки.
     */
    private function bindValue($stmt, $values) {

        if (is_string($values) || is_numeric($values)) {
            $stmt->bind_param('s', $values);

        } elseif (is_array($values)) {
            foreach ($values as $value) {
                if (is_int($value)) {
                    $stmt->bind_param('i', $value);
                } else {
                    $stmt->bind_param('s', $value);
                }
            }
        }

        return true;
    }


    /**
     * Начало транзакции
     *
     * @return bool
     *      Возвращает TRUE в случае успешного завершения
     *      или FALSE в случае возникновения ошибки.
     */
    public function beginTransaction() {
        return $this->db->begin_transaction();
    }


    /**
     * Фиксирует транзакцию
     *
     * @return bool
     *      Возвращает TRUE в случае успешного завершения
     *      или FALSE в случае возникновения ошибки.
     */
    public function commit() {
        return $this->db->commit();
    }


    /**
     * Откатывает изменения в базе данных сделанные в рамках текущей транзакции,
     *
     * @return bool
     *      Возвращает TRUE в случае успешного завершения
     *      или FALSE в случае возникновения ошибки.
     */
    public function rollback() {
        return $this->db->rollback();
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
            throw new Exception("Failed execute query\n");
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
            throw new Exception("Failed execute query\n");
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
     * Возващает резельтат запроса с первым значением поля,
     * из первой строки запроса
     *
     *
     * @return string
     *      Первое значение из запроса,
     *      либо false в случае ошибки
     * @throws Exception
     */
    public function fetchOne($sql, $bind_params = null) {

        $stmt = $this->db->prepare($sql);

        if ($stmt) {
            if ($bind_params !== null) $this->bindValue($stmt, $bind_params);
            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception("Failed execute query\n");
            }

            $result = $stmt->get_result();
            $row    = $result->fetch_assoc();
            $return = '';
            if (is_array($row)) {
                return current($row);
            }

            return $return;
        }

        throw new Exception("Failed to prepare statement\n");
    }


    /**
     * Возващает результат запроса с первой строкой
     * из результата запроса
     *
     *
     * @return array
     *      Результирующий массив данных
     * @throws Exception
     */
    public function fetchRow($sql, $bind_params = null) {

        $stmt = $this->db->prepare($sql);

        if ($stmt) {
            if ($bind_params !== null) $this->bindValue($stmt, $bind_params);
            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception("Failed execute query\n");
            }

            $result = $stmt->get_result();
            return $result->fetch_assoc();
        }

        throw new Exception("Failed to prepare statement\n");
    }


    /**
     * Возващает результат запроса с указанным столбцом
     * из результата запроса
     *
     * @param int $column_number
     *      Номер необходимого столбца
     *
     * @return array
     *      Результирующий массив данных
     * @throws Exception
     */
    public function fetchCol($sql, $bind_params = null, $column_number = 1) {

        $stmt = $this->db->prepare($sql);

        if ($stmt) {
            if ($bind_params !== null) $this->bindValue($stmt, $bind_params);
            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception("Failed execute query\n");
            }

            $result = $stmt->get_result();
        } else {
            throw new Exception("Failed to prepare statement\n");
        }

        $column        = array();
        $column_number = $column_number > 0
            ? $column_number - 1
            : 0;

        // compatibility layer with PHP < 5.3
        if (function_exists('mysqli_fetch_all')) {
            $res = $result->fetch_all(MYSQLI_ASSOC);

            foreach ($res as $r) {
                $i = 0;
                foreach ($r as $value) {
                    if ($i++ == $column_number) {
                        $column[] = $value;
                        break;
                    }
                }
            }

        } else {
            while ($tmp = $result->fetch_array()) {
                $i = 0;
                foreach ($tmp as $value) {
                    if ($i++ == $column_number) {
                        $column[] = $value;
                        break;
                    }
                }
            }
        }

        return $column;
    }


    /**
     * Возващает результат запроса в виде одномерного массива
     * ключами которого выступает первое поле из запроса, а значениями второе поле
     *
     * @return array
     *      Результирующий массив данных
     * @throws Exception
     */
    public function fetchPairs($sql, $bind_params = null) {

        $stmt = $this->db->prepare($sql);

        if ($stmt) {
            if ($bind_params !== null) $this->bindValue($stmt, $bind_params);
            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception("Failed execute query\n");
            }

            $result = $stmt->get_result();
        } else {
            throw new Exception("Failed to prepare statement\n");
        }


        $pairs = array();

        // compatibility layer with PHP < 5.3
        if (function_exists('mysqli_fetch_all')) {
            $res = $result->fetch_all(MYSQLI_ASSOC);

            foreach ($res as $r) {
                $pairs[current($r)] = next($r);
            }

        } else {
            while ($tmp = $result->fetch_array()) {
                $pairs[current($tmp)] = next($tmp);
            }
        }

        return $pairs;
    }


    /**
     * Возващает результат запроса со всеми записями
     *
     * @return array
     *      Результирующий массив данных
     * @throws Exception
     */
    public function fetchAll($sql, $bind_params = null) {

        $stmt = $this->db->prepare($sql);

        if ($stmt) {
            if ($bind_params !== null) $this->bindValue($stmt, $bind_params);
            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception("Failed execute query\n");
            }

            $result = $stmt->get_result();
        } else {
            throw new Exception("Failed to prepare statement\n");
        }


        $all = array();

        // compatibility layer with PHP < 5.3
        if (function_exists('mysqli_fetch_all')) {
            $all = $result->fetch_all(MYSQLI_ASSOC);

        } else {
            while ($tmp = $result->fetch_array()) {
                $all[] = $tmp;
            }
        }

        return $all;
    }


    /**
     * Возвращает ID последней вставленной строки либо последнее значение,
     * которое выдал объект последовательности.
     *
     * @param string|null $table_name
     *
     * @return int|string
     *      Вернет строку представляющую ID последней добавленной в базу записи.
     */
    public function lastInsertId ($table_name = null) {

        return $this->db->insert_id;
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
                $query_data['params'][$name]  = $value;
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
                $query_data['fields'][]       = "{$quoted_name} = ?";
                $query_data['params'][$name]  = $value;
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