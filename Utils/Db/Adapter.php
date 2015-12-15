<?php

namespace Combine\Utils\Db;


/**
 * Interface Adapter
 * @package Combine\Utils\Db
 */
interface Adapter {

    public function setConnection($db);

    public function getConnection();

    public function quote($value);

    public function quoteInto($string, $value, $count);

    public function quoteIdentifier($ident);

    public function query($sql, $bind_params);

    public function fetchOne($sql, $bind_params);

    public function fetchRow($sql, $bind_params);

    public function fetchPairs($sql, $bind_params);

    public function fetchCol($sql, $bind_params, $column_number);

    public function fetchAll($sql, $bind_params);

    public function insert($table, array $data);

    public function update($table, array $data, $where);

    public function delete($table, $where);

    public function lastInsertId($table_name);

    public function beginTransaction();

    public function commit();

    public function rollback();
}