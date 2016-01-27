<?php

namespace Combine\Tabs\classes;


/**
 * Class ComboTab
 * @package Combine\Tabs\classes
 */
class ComboTab {

    const ELEMENT_ITEM  = 1;
    const ELEMENT_BREAK = 2;

    protected $title       = '';
    protected $is_disabled = false;
    protected $elements    = array();


    /**
     * @param string $title
     * @param bool   $is_disabled
     */
    public function __construct($title, $is_disabled = false) {
        $this->title       = $title;
        $this->is_disabled = $is_disabled;
    }


    /**
     * Добавление значения в таб
     * @param  string $title
     * @param  string $id
     * @param  bool   $disabled
     * @return self
     */
    public function addItem($title, $id, $disabled = false) {
        $this->elements[] = array(
            'type'     => self::ELEMENT_ITEM,
            'title'    => $title,
            'id'       => $id,
            'disabled' => $disabled,
        );
        return $this;
    }


    /**
     * Добавление разделителя
     * @return self
     */
    public function addBreak() {
        $this->elements[] = array(
            'type' => self::ELEMENT_BREAK
        );
        return $this;
    }


    /**
     * Получение всех элементов таба
     * @return array
     */
    public function getElements() {
        return $this->elements;
    }


    /**
     * Получение названия таба
     * @return array
     */
    public function getTitle() {
        return $this->title;
    }


    /**
     * Активен-ли таб
     * @return bool
     */
    public function isDisabled() {
        return $this->is_disabled;
    }
}