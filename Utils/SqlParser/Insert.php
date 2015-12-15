<?php

namespace Combine\Utils\SqlParser;


/**
 * Class Insert
 * @package Combine\Utils\SqlParser
 */
class Insert {

    /**
     * @var array
     */
    private $_sql = array();

    /**
     * @param string $sql Текст SQL запроса
     */
    public function __construct($sql) {
        $this->parse($sql);
    }



    /**
     * @return string
     */
    public function getSql() {

        $sql  = 'INSERT INTO' . ' ' . $this->_sql['INSERT INTO'];
        $sql .= '(' . ' ' . $this->_sql['FIELDS'] . ')';
        $sql .= ' VALUES (' . ' ' . $this->_sql['VALUES'] . ')';

        return $sql;
    }


    /**
     * @param string $sql Текст SQL запроса
     */
    private function parse($sql) {

        $table_match = array();
        preg_match('~^(?:\s+INSERT\s+INTO\s+)(.*?(?=\s+\(|(?:\s|\)\s+)VALUE\s|\s+$))~is', $sql, $table_match);
        if ( ! empty($table_match[1])) $this->_sql['INSERT INTO'] = $table_match[1];

        $fields_match = array();
        preg_match('~^(?:\s+INSERT\s+INTO\s+)(?:.*?\()(.*?)\)\s+VALUE~is', $sql, $fields_match);
        if ( ! empty($fields_match[1])) $this->_sql['FIELDS'] = $fields_match[1];

        $values_match = array();
        preg_match('~^(?:\s+INSERT\s+INTO)(?:.*?)(?:\)|)\s*VALUE\s*\(([^\)]*)~is', $sql, $values_match);
        if ( ! empty($values_match[1])) $this->_sql['VALUES'] = $values_match[1];
    }
}