<?php

namespace Combine\Form\Classes\Control;
use Combine\Form\Classes\Control;
use Combine\Utils\Mtpl;


require_once __DIR__ . '/../../../Utils/Mtpl/Mtpl.php';
require_once __DIR__ . '/../Control.php';



/**
 * Class Text
 * @package Combine\Form\Control
 */
class Captcha extends Control {


    /**
     * @return string
     */
    protected function makeControl() {

        $tpl        = new Mtpl(__DIR__ . '/tpl/control.html');
        $attributes = array();

        $attributes_raw['type']  = isset($attributes_raw['type'])  ? $attributes_raw['type']  : 'text';
        $attributes_raw['class'] = isset($attributes_raw['class']) ? $attributes_raw['class'] : 'form-control';

        if ( ! empty($attributes_raw)) {
            foreach ($attributes_raw as $attr_name => $value) {
                $attributes[] = "$attr_name=\"$value\"";
            }
        }

        if (isset($options['required']) && $options['required']) {
            $attributes[] = 'required="required"';
            $tpl->assign('[REQUIRED]', '<span class="combine-req-star">*</span> ');

            if ( ! empty($options['required_message'])) {
                $attributes[] = "data-required-message=\"{$options['required_message']}\"";
            }
        }


        if ( ! empty($options['validators'])) {
            foreach ($options['validators'] as $validator) {
                // TODO сделать валидаторы
            }
        }


        if ( ! empty($attributes_raw['id'])) {
            $attr_id = $attributes_raw['id'];

        } elseif ( ! empty($attributes_raw['name'])) {
            $attr_id = $attributes_raw['name'];

        } else {
            $attr_id = '';
        }


        $tpl->assign('[ID]',         $attr_id);
        $tpl->assign('[LABEL]',      $label);
        $tpl->assign('[ATTRIBUTES]', implode(' ', $attributes));

        if ( ! empty($options['out'])) {
            $tpl->assign('[OUT]', $options['out']);
        }

        return $tpl->render();
    }
}