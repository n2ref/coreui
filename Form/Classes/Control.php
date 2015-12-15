<?php

namespace Combine\Form\Classes;
use Combine\Registry;
use Combine\Exception;
use Combine\Utils\Mtpl;

require_once __DIR__ . '/../../Registry.php';
require_once __DIR__ . '/../../Exception.php';
require_once __DIR__ . '/../../Utils/Mtpl/Mtpl.php';


/**
 * Class Cell
 * @package Combine\Form
 */
abstract class Control {

    protected $label            = '';
    protected $resource         = '';
    protected $attributes       = array();
    protected $validators       = array();
    protected $out              = '';
    protected $is_readonly      = false;
    protected $required         = false;
    protected $required_message = '';
    protected $data             = null;
    protected $html             = null;
    protected $html_wrapper     = null;
    protected $lang             = 'en';
    protected $theme_src        = '';
    protected $theme_location   = '';

    /**
     * @param string $label
     * @param string $name
     */
    public function __construct($label, $name = '') {
        $this->label = $label;
        if ( ! empty($name)) {
            $this->attributes['name'] = $name;
        }

        $this->lang           = Registry::getLanguage();
        $this->theme_src      = Registry::getThemeSrc();
        $this->theme_location = Registry::getThemeLocation();
    }


    /**
     * @param string $resource
     */
    public function setResource($resource) {
        $this->resource = $resource;
    }


    /**
     * @param  string     $name
     * @param  string     $value
     * @return self
     * @throws Exception
     */
    public function setAttr($name, $value) {
        if (is_string($name) && (is_string($value) || is_numeric($value))) {
            $this->attributes[$name] = $value;

        } else {
            throw new Exception("Attribute not valid type. Need string");
        }

        return $this;
    }


    /**
     * @param  string     $name
     * @param  string     $value
     * @return self
     * @throws Exception
     */
    public function setAppendAttr($name, $value) {
        if (is_string($name) && (is_string($value) || is_numeric($value))) {
            if (isset($this->attributes[$name])) {
                $this->attributes[$name] = $value . $this->attributes[$name];
            } else {
                $this->attributes[$name] = $value;
            }

        } else {
            throw new Exception("Attribute not valid type. Need string");
        }

        return $this;
    }



    /**
     * @param  string     $name
     * @param  string     $value
     * @return self
     * @throws Exception
     */
    public function setPrependAttr($name, $value) {
        if (is_string($name) && (is_string($value) || is_numeric($value))) {
            if (isset($this->attributes[$name])) {
                $this->attributes[$name] = $this->attributes[$name] . $value;
            } else {
                $this->attributes[$name] = $value;
            }

        } else {
            throw new Exception("Attribute not valid type. Need string");
        }

        return $this;
    }


    /**
     * @param  array $attributes
     * @return self
     */
    public function setAttribs($attributes) {
        foreach ($attributes as $name => $value) {
            $this->setAttr($name, $value);
        }
        return $this;
    }


    /**
     * @param  array $attributes
     * @return self
     */
    public function setAppendAttribs($attributes) {
        foreach ($attributes as $name => $value) {
            $this->setAppendAttr($name, $value);
        }
        return $this;
    }


    /**
     * @param  array $attributes
     * @return self
     */
    public function setPrependAttribs($attributes) {
        foreach ($attributes as $name => $value) {
            $this->setPrependAttr($name, $value);
        }
        return $this;
    }


    /**
     * @param  string $message
     * @return Control\Text|Control\Select|Control\Password|Control\Radio|Control\Upload
     */
    public function setRequired($message = '') {
        $this->required = true;
        $this->required_message = $message;
        return $this;
    }


    /**
     * @param  string $out
     * @return self
     */
    public function setOut($out) {
        $this->out = $out;
        return $this;
    }


    /**
     * @param  mixed $data
     * @return self
     */
    public function setData($data) {
        $this->data = $data;
        return $this;
    }


    /**
     * @param  string     $html
     * @return self
     */
    public function setHtml($html) {
        $this->html = $html;
        return $this;
    }


    /**
     * @param  string     $html
     * @return self
     */
    public function setHtmlWrapper($html) {
        $this->html_wrapper = $html;
        return $this;
    }

    
    /**
     * @param  string $name
     * @return mixed
     */
    public function getAttr($name) {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];

        }
        return false;
    }


    /**
     * @param bool $is_readonly
     */
    public function setReadonly($is_readonly) {
        $this->is_readonly = (bool)$is_readonly;
    }


    /**
     * @return bool
     */
    public function getReadonly() {
        return $this->is_readonly;
    }


    /**
     * Render form control
     * @return string
     */
    public function render() {

        if ($this->html !== null) {
            $control_html = $this->html;
        } else {
            $control_html = $this->makeControl();
        }

        if ($this->html_wrapper !== null) {
            $wrapper_html = $this->html_wrapper;
        } else {
            $wrapper_html = $this->makeWrapper();
        }

        $wrapper_html = str_replace('[CONTROL]', $control_html, $wrapper_html);

        return $wrapper_html;
    }


    /**
     * @return string
     */
    protected function makeWrapper() {

        $tpl = new Mtpl($this->theme_location . '/html/form/wrappers/control.html');

        if ( ! empty($this->attributes['id'])) {
            $label_for = ' for="' . $this->attributes['id'] . '"';

        } else {
            $label_for = '';
        }
        $name = ! empty($this->attributes['name']) ? $this->attributes['name'] : '';
        $tpl->assign('[RESOURCE]',  $this->resource);
        $tpl->assign('[NAME]',      $name);
        $tpl->assign('[LABEL_FOR]', $label_for);
        $tpl->assign('[LABEL]',     $this->label);

        if ($this->required) {
            $tpl->touchBlock('req');
        }

        if ( ! empty($this->out)) {
            $tpl->out->assign('[OUT]', $this->out);
        }

        return $tpl->render();
    }


    /**
     * @return string
     */
    abstract protected function makeControl();
}