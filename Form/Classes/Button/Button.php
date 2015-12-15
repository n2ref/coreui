<?php

namespace Combine\Form\Classes\Button;
use Combine\Form\Classes;

require_once __DIR__ . '/../Button.php';


/**
 * Class Text
 * @package Combine\Form\Classes\Button
 */
class Button extends Classes\Button {

    protected $attributes = array(
        'type'  => 'button',
        'class' => 'btn btn-default',
    );


    /**
     * @return string
     */
    protected function makeControl() {

        $tpl = file_get_contents($this->theme_location . '/html/form/buttons/button.html');

        $attributes = array();

        if ( ! empty($this->attributes)) {
            foreach ($this->attributes as $attr_name => $value) {
                $attributes[] = "$attr_name=\"$value\"";
            }
        }


        $tpl = str_replace('[ATTRIBUTES]', implode(' ', $attributes), $tpl);

        return $tpl;
    }
}