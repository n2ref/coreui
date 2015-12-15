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
class Checkbox extends Control {

    protected $options    = array();
    protected $checked    = null;
    protected $position   = 'horizontal';
    protected $attributes = array(
        'type' => 'checkbox',
    );


    /**
     * @param string $label
     * @param string $name
     */
    public function __construct($label, $name = '') {
        if ( ! empty($name)) {
            $name .= '[]';
        }
        parent::__construct($label, $name);
    }


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
     * @param  mixed $checked
     * @return self
     */
    public function setOptions($options, $checked = null) {
        $this->options = $options;
        $this->checked = $checked;
        return $this;
    }


    /**
     * @param  mixed $checked
     * @return self
     */
    public function setChecked($checked) {
        $this->checked = $checked;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getChecked() {
        return $this->checked;
    }


    /**
     * @return string
     */
    protected function makeControl() {

        $tpl = new Mtpl($this->theme_location . '/html/form/controls/checkbox.html');

        if ( ! empty($this->options)) {
            foreach ($this->options as $key => $name) {
                $attributes = array(
                    "value=\"{$key}\""
                );

                if ($this->checked !== null) {
                    if (is_array($this->checked) && in_array($key, $this->checked)) {
                        $attributes[] = 'checked="checked"';

                    } elseif ($this->checked === $key) {
                        $attributes[] = 'checked="checked"';
                    }
                }

                if ( ! empty($this->attributes)) {
                    foreach ($this->attributes as $attr_name => $value) {
                        if (trim($attr_name) != 'value') {
                            $attributes[] = "$attr_name=\"$value\"";
                        }
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


                $tpl->checkbox->assign('[ATTRIBUTES]', implode(' ', $attributes));
                $tpl->checkbox->assign('[NAME]',       $name);
                $tpl->checkbox->reassign();
            }
        }

        return $tpl->render();
    }
}