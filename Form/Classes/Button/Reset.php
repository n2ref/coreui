<?php

namespace Combine\Form\Classes\Button;
use Combine\Form\Classes;

require_once __DIR__ . '/../Button.php';


/**
 * Class Reset
 * @package Combine\Form\Classes\Button
 */
class Reset extends Classes\Button {

    protected $attributes = array(
        'type'  => 'reset',
        'class' => 'btn btn-default',
    );


    /**
     * @return string
     */
    protected function makeControl() {

        $tpl = file_get_contents($this->theme_location . '/html/form/buttons/reset.html');

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