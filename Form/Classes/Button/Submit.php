<?php

namespace Combine\Form\Classes\Button;
use Combine\Form\Classes;

require_once __DIR__ . '/../Button.php';


/**
 * Class Submit
 * @package Combine\Form\Classes\Button
 */
class Submit extends Classes\Button {

    protected $attributes = array(
        'type'  => 'submit',
        'class' => 'btn btn-primary',
    );


    /**
     * @return string
     */
    protected function makeControl() {

        $tpl = file_get_contents($this->theme_location . '/html/form/buttons/submit.html');

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