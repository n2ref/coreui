<?php

namespace Combine\Form\Classes\Control;
use Combine\Form\Classes\Control;

require_once __DIR__ . '/../Control.php';



/**
 * Class Select
 * @package Combine\Form\Control
 */
class Select extends Control {

    protected $options    = array();
    protected $selected   = null;
    protected $attributes = array(
        'class' => 'combine-form-control',
    );


    /**
     * @param  string     $type
     * @param  array|bool $params
     * @param  string     $message
     * @return self
     * @throws \Exception
     */
    public function addValidator($type, $params, $message) {

        $type = strtolower($type);

        switch ($type) {
            case 'regex' :
            case 'length' :
            case 'email' :
                $validator = new \stdClass();
                $validator->type    = $type;
                $validator->params  = $params;
                $validator->message = $message;

                $this->validators[] = $validator;
                break;

            default :
                throw new \Exception("Validator type '{$type}' not found");
                break;
        };

        return $this;
    }


    /**
     * @param  array $options
     * @param  mixed $selected
     * @return self
     */
    public function setOptions($options, $selected = null) {
        $this->options  = $options;
        $this->selected = $selected;
        return $this;
    }


    /**
     * @param  mixed $selected
     * @return self
     */
    public function setSelected($selected) {
        $this->selected = $selected;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getSelected() {
        return $this->selected;
    }



    /**
     * @return string
     */
    protected function makeControl() {

        $tpl = file_get_contents($this->theme_location . '/html/form/controls/select.html');

        $attributes = array();

        if ( ! empty($this->attributes)) {
            foreach ($this->attributes as $attr_name => $value) {
                $attributes[] = "$attr_name=\"$value\"";
            }
        }

        if ($this->required) {
            $attributes[] = 'required="required"';

            if ($this->required_message) {
                $attributes[] = "data-required-message=\"{$this->required_message}\"";
            }
        }


        // TODO сделать валидаторы
        if ( ! empty($this->validators)) {
            foreach ($this->validators as $validator) {

            }
        }

        $options = '';
        if ( ! empty($this->options)) {
            foreach ($this->options as $value => $option) {
                if (is_array($option)) {
                    $options .= "<optgroup label=\"{$value}\">";
                    foreach ($option as $val => $opt) {
                        $sel = $this->selected !== null && ((is_array($this->selected) && in_array((string)$val, $this->selected)) || (string)$val === $this->selected)
                            ? 'selected="selected" '
                            : '';
                        $options .= "<option {$sel}value=\"{$val}\">{$opt}</option>";
                    }
                    $options .= '</optgroup>';

                } else {
                    $sel = $this->selected !== null && ((is_array($this->selected) && in_array((string)$value, $this->selected)) || (string)$value === $this->selected)
                        ? 'selected="selected" '
                        : '';
                    $options .= "<option {$sel}value=\"{$value}\">{$option}</option>";
                }
            }
        }


        $tpl = str_replace('[OPTIONS]',    $options, $tpl);
        $tpl = str_replace('[ATTRIBUTES]', implode(' ', $attributes), $tpl);

        return $tpl;
    }
}