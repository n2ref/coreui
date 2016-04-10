<?php

namespace Combine\Table\classes;
use Combine\Exception;

require_once __DIR__ . '/../../Exception.php';
require_once 'Cell.php';


/**
 * Class Row
 * @package Combine\Table\classes
 */
class Row implements \Iterator {

    protected $cells = array();
    protected $attr  = array();

    /**
     * Row constructor.
     * @param array $row
     */
    public function __construct(array $row) {
        foreach ($row as $key => $cell) {
            $this->cells[$key] = new Cell($cell);
        }
    }


    /**
     * Get cell class
     * @param string $field
     * @return Cell|string
     */
    public function __get($field) {
        if ( ! array_key_exists($field, $this->cells)) {
            $this->cells[$field] = new Cell('');
        }
        return $this->cells[$field];
    }


    /**
     * Set value in cell
     * @param string $field
     * @param string $value
     */
    public function __set($field, $value) {
        if (array_key_exists($field, $this->cells)) {
            $this->cells[$field]->setValue($value);
        } else {
            $this->cells[$field] = new Cell($value);
        }
    }


    /**
     * Check cell
     * @param string $field
     * @return bool
     */
    public function __isset($field) {
        return isset($this->cells[$field]);
    }


    /**
     * Установка значения атрибута
     * @param  string     $name
     * @param  string     $value
     * @throws Exception
     */
    public function setAttr($name, $value) {
        if (is_string($name) && is_string($value) ) {
            $this->attr[$name] = $value;

        } else {
            throw new Exception("Attribute not valid type. Need string");
        }
    }


    /**
     * Установка значения в начале атрибута
     * @param  string     $name
     * @param  string     $value
     * @throws Exception
     */
    public function setPrependAttr($name, $value) {
        if (is_string($name) && is_string($value) ) {
            if (array_key_exists($name, $this->attr)) {
                $this->attr[$name] = $value . $this->attr[$name];
            } else {
                $this->attr[$name] = $value;
            }

        } else {
            throw new Exception("Attribute not valid type. Need string");
        }
    }


    /**
     * Установка значения в конце атрибута
     * @param  string     $name
     * @param  string     $value
     * @throws Exception
     */
    public function setAppendAttr($name, $value) {
        if (is_string($name) && is_string($value) ) {
            if (array_key_exists($name, $this->attr)) {
                $this->attr[$name] .= $value;
            } else {
                $this->attr[$name] = $value;
            }

        } else {
            throw new Exception("Attribute not valid type. Need string");
        }
    }


    /**
     * Установка атрибутов
     * @param  array      $attributes
     * @throws Exception
     */
    public function setAttribs($attributes) {
        foreach ($attributes as $name => $value) {
            if (is_string($name) && is_string($value) ) {
                $this->attr[$name] = $value;

            } else {
                throw new Exception("Attribute not valid type. Need string");
            }
        }
    }


    /**
     * Установка атрибутов в начале
     * @param  array      $attributes
     * @throws Exception
     */
    public function setPrependAttribs($attributes) {
        foreach ($attributes as $name => $value) {
            if (is_string($name) && is_string($value) ) {
                if (array_key_exists($name, $this->attr)) {
                    $this->attr[$name] = $value . $this->attr[$name];
                } else {
                    $this->attr[$name] = $value;
                }

            } else {
                throw new Exception("Attribute not valid type. Need string");
            }
        }
    }


    /**
     * Установка значения в конце атрибута
     * @param  array      $attributes
     * @throws s
     */
    public function setAppendAttribs($attributes) {
        foreach ($attributes as $name => $value) {
            if (is_string($name) && is_string($value) ) {
                if (array_key_exists($name, $this->attr)) {
                    $this->attr[$name] .= $value;
                } else {
                    $this->attr[$name] = $value;
                }

            } else {
                throw new Exception("Attribute not valid type. Need string");
            }
        }
    }


    /**
     * Получение всех атрибутов
     * @return array
     */
    public function getAttribs() {
        return $this->attr;
    }


    public function rewind() {
        return reset($this->cells);
    }

    public function key() {
        return key($this->cells);
    }

    public function current() {
        return current($this->cells);
    }

    public function valid() {
        return key($this->cells) !== null;
    }

    public function next() {
        return next($this->cells);
    }
}