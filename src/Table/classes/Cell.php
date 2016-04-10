<?php

namespace Combine\Table\classes;
use Combine\Exception;
require_once __DIR__ . '/../../Exception.php';


/**
 * Class Cell
 * @package Combine\Table\classes
 */
class Cell {

    protected $value = '';
    protected $attr  = array();

    public function __construct($value) {
        $this->value = $value;
    }


    /**
     * @return string
     */
    public function __toString() {
        return (string)$this->value;
    }


    /**
     * @param string $value
     */
    public function setValue($value) {
        $this->value = $value;
    }


    /**
     * @return string
     */
    public function getValue() {
        return $this->value;
    }


    /**
     * Установка значения атрибута
     * @param  string     $name
     * @param  string     $value
     * @throws \Exception
     */
    public function setAttr($name, $value) {
        if ((is_string($name) || is_numeric($name)) &&
            (is_string($value) || is_numeric($value))
        ) {
            $this->attr[$name] = $value;

        } else {
            throw new Exception("Attribute not valid type. Need string or numeric");
        }
    }


    /**
     * Установка значения в начале атрибута
     * @param  string     $name
     * @param  string     $value
     * @throws \Exception
     */
    public function setPrependAttr($name, $value) {
        if ((is_string($name) || is_numeric($name)) &&
            (is_string($value) || is_numeric($value))
        ) {
            if (array_key_exists($name, $this->attr)) {
                $this->attr[$name] = $value . $this->attr[$name];
            } else {
                $this->attr[$name] = $value;
            }

        } else {
            throw new Exception("Attribute not valid type. Need string or numeric");
        }
    }


    /**
     * Установка значения в конце атрибута
     * @param  string     $name
     * @param  string     $value
     * @throws \Exception
     */
    public function setAppendAttr($name, $value) {
        if ((is_string($name) || is_numeric($name)) &&
            (is_string($value) || is_numeric($value))
        ) {
            if (array_key_exists($name, $this->attr)) {
                $this->attr[$name] .= $value;
            } else {
                $this->attr[$name] = $value;
            }

        } else {
            throw new Exception("Attribute not valid type. Need string or numeric");
        }
    }


    /**
     * Установка атрибутов
     * @param  array      $attributes
     * @throws \Exception
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
     * @throws \Exception
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
     * @throws \Exception
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
}