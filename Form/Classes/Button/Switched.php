<?php

namespace Combine\Form\Classes\Button;
use Combine\Registry;
use Combine\Form\Classes;

require_once __DIR__ . '/../Button.php';


/**
 * Class Text
 * @package Combine\Form\Classes\Button
 */
class Switched extends Classes\Button {

    protected $active_value   = '';
    protected $inactive_value = '';
    protected $default        = true;
    protected $attributes     = array(
        'type' => 'hidden'
    );


    /**
     * @param string $name
     * @param string $value
     * @param string $active_value
     * @param string $inactive_value
     * @param bool   $default
     */
    public function __construct($name, $value, $active_value, $inactive_value, $default = true) {
        $this->setAttr('name',  $name);
        $this->setAttr('value', $value);
        $this->active_value   = $active_value;
        $this->inactive_value = $inactive_value;
        $this->default        = $default;

        $this->theme_location = Registry::getThemeLocation();
        $this->theme_src      = Registry::getThemeSrc();
    }


    /**
     * @return string
     */
    protected function makeControl() {

        $tpl = file_get_contents($this->theme_location . '/html/form/buttons/switched.html');

        $value = $this->getAttr('value');
        if ($value != $this->active_value && $value != $this->inactive_value) {
            $value = $this->default
                ? $this->active_value
                : $this->inactive_value;
            $this->setAttr('value', $value);
        }

        $is_active  = $value == $this->active_value;
        $attributes = array();

        if ( ! empty($this->attributes)) {
            foreach ($this->attributes as $attr_name => $value) {
                $attributes[] = "$attr_name=\"$value\"";
            }
        }


        $tpl = str_replace('[ATTRIBUTES]',      implode(' ', $attributes), $tpl);
        $tpl = str_replace('[THEME_SRC]',       $this->theme_src, $tpl);
        $tpl = str_replace('[IS_DISPLAY_ON]',   $is_active ? '' : 'display:none;', $tpl);
        $tpl = str_replace('[IS_DISPLAY_OFF]',  $is_active ? 'display:none;' : '', $tpl);
        $tpl = str_replace('[ACTIVE_VALUE]',    $this->active_value, $tpl);
        $tpl = str_replace('[INACTIVE_VALUE]',  $this->inactive_value, $tpl);

        return $tpl;
    }
}