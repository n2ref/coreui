<?php

namespace Combine\Table\classes;
use Combine\Exception;

require_once __DIR__ . '/../../Exception.php';


/**
 * Class Column
 * @package Combine\Table\classes
 */
class Column {

    protected $title      = '';
    protected $field      = '';
    protected $type       = '';
    protected $attr       = array();
    protected $is_sorting = true;

    /**
     * @param string $title
     * @param string $field
     * @param string $type
     */
    public function __construct($title, $field, $type) {
        $this->title = $title;
        $this->field = $field;
        $this->type  = $type;
    }


    /**
     * @return string
     */
    public function getField() {
        return $this->field;
    }


    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }


    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }


    /**
     * @param  string      $name
     * @return string|bool
     */
    public function getAttr($name) {
        if (array_key_exists($name, $this->attr)) {
            return $this->attr[$name];
        } else {
            return false;
        }
    }


    /**
     * Получение всех атрибутов
     * @return array
     */
    public function getAttributes() {
        return $this->attr;
    }


    /**
     * Установка значения атрибута
     * @param  string     $name
     * @param  string     $value
     * @throws Exception
     * @return self
     */
    public function setAttr($name, $value) {
        if ((is_string($name) || is_numeric($name)) &&
            (is_string($value) || is_numeric($value))) {
            $this->attr[$name] = $value;

        } else {
            throw new Exception("Attribute not valid type. Need string");
        }
        return $this;
    }


    /**
     * Установка значения в начале атрибута
     * @param  string     $name
     * @param  string     $value
     * @throws Exception
     * @return self
     */
    public function setPrependAttr($name, $value) {
        if ((is_string($name) || is_numeric($name)) &&
            (is_string($value) || is_numeric($value))) {
            if (array_key_exists($name, $this->attr)) {
                $this->attr[$name] = $value . $this->attr[$name];
            } else {
                $this->attr[$name] = $value;
            }

        } else {
            throw new Exception("Attribute not valid type. Need string");
        }
        return $this;
    }


    /**
     * Установка значения в конце атрибута
     * @param  string     $name
     * @param  string     $value
     * @throws Exception
     * @return self
     */
    public function setAppendAttr($name, $value) {
        if ((is_string($name) || is_numeric($name)) &&
            (is_string($value) || is_numeric($value))) {
            if (array_key_exists($name, $this->attr)) {
                $this->attr[$name] .= $value;
            } else {
                $this->attr[$name] = $value;
            }

        } else {
            throw new Exception("Attribute not valid type. Need string");
        }
        return $this;
    }


    /**
     * @param  array $attributes
     * @return self
     */
    public function setAttributes($attributes) {
        foreach ($attributes as $name => $value) {
            $this->setAttr($name, $value);
        }
        return $this;
    }


    /**
     * @param  bool $is_sort
     * @return self
     */
    public function setSorting($is_sort = true) {
        $this->is_sorting = (bool)$is_sort;
        return $this;
    }


    /**
     * @return bool
     */
    public function isSorting() {
        return $this->is_sorting;
    }
}