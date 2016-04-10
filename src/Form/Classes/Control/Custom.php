<?php

namespace Combine\Form\Classes\Control;
use Combine\Form\Classes\Control;


require_once __DIR__ . '/../Control.php';



/**
 * Class Custom
 * @package Combine\Form\Control
 */
class Custom extends Control {

    protected $attributes = array();
    protected $html       = '';


    /**
     * @param string $label
     * @param string $html
     */
    public function __construct($label, $html) {
        parent::__construct($label);
        $this->html = $html;
    }


    /**
     * @return string
     */
    protected function makeControl() {
        return $this->html;
    }
}