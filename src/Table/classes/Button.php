<?php

namespace Combine\Table\classes;


/**
 * Class Button
 * @package Combine\Table\classes
 */
class Button {

    protected $title      = '';
    protected $attributes = array(
        'type'  => 'button',
        'class' => 'btn btn-default btn-xs'
    );

    /**
     * @param string $title
     */
    public function __construct($title) {
        $this->title = $title;
    }


    /**
     * @param array $attributes
     * @return Button
     */
    public function setAttribs(array $attributes) {
        foreach ($attributes as $attr => $value) {
            $this->attributes[$attr] = $value;
        }
        return $this;
    }



    /**
     * @param  array $attributes
     * @return Button
     */
    public function setAppendAttribs(array $attributes) {
        foreach ($attributes as $attr => $value) {
            $this->attributes[$attr] = array_key_exists($attr, $this->attributes)
                ? $this->attributes[$attr] . $value
                : $value;
        }
        return $this;
    }



    /**
     * @param  array $attributes
     * @return Button
     */
    public function setPrependAttribs(array $attributes) {
        foreach ($attributes as $attr => $value) {
            $this->attributes[$attr] = array_key_exists($attr, $this->attributes)
                ? $value . $this->attributes[$attr]
                : $value;
        }
        return $this;
    }


    /**
     * @param  string $attr
     * @param  string $value
     * @return Button
     */
    public function setAttr($attr, $value) {
        $this->attributes[$attr] = $value;
        return $this;
    }


    /**
     * @param  string $attr
     * @param  string $value
     * @return Button
     */
    public function setAppendAttr($attr, $value) {
        $this->attributes[$attr] = array_key_exists($attr, $this->attributes)
            ? $this->attributes[$attr] . $value
            : $value;
        return $this;
    }


    /**
     * @param  string $attr
     * @param  string $value
     * @return Button
     */
    public function setPrependAttr($attr, $value) {
        $this->attributes[$attr] = array_key_exists($attr, $this->attributes)
            ? $value . $this->attributes[$attr]
            : $value;
        return $this;
    }


    /**
     * @return string
     */
    public function __toString() {
        return $this->getHtml();
    }


    /**
     * @return string
     */
    public function getHtml() {

        $attributes = array();
        foreach ($this->attributes as $attr => $value) {
            $attributes[] = "$attr=\"{$value}\"";
        }

        $implode_attributes = implode(' ', $attributes);
        $implode_attributes = $implode_attributes ? ' ' . $implode_attributes : '';


        return "<button{$implode_attributes}>{$this->title}</button>";
    }
}