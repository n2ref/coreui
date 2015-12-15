<?php

namespace Combine\Tabs\classes;


/**
 * Class ComboTab
 * @package Combine\Tabs\classes
 */
class ComboTab {

    const ELEMENT_ITEM  = 1;
    const ELEMENT_BREAK = 2;

    protected $title    = '';
    protected $elements = array();


    /**
     * @param string $title
     */
    public function __construct($title) {
        $this->title = $title;
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
}