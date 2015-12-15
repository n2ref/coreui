<?php

namespace Combine\Form\Classes\Control;
use Combine\Form\Classes\Control;

require_once __DIR__ . '/../Control.php';



/**
 * Class Modal
 * @package Combine\Form\Control
 */
class Modal extends Control {

    protected $url        = '';
    protected $title      = '';
    protected $text       = '';
    protected $size       = 'normal';
    protected $attributes = array(
        'type' => 'hidden'
    );


    /**
     * @param string $label
     * @param string $name
     * @param string $title
     */
    public function __construct($label, $name = '', $title = '') {
        parent::__construct($label, $name);
        $this->title = $title;
    }


    /**
     * @param  string     $url
     * @return self
     * @throws \Exception
     */
    public function setUrl($url) {

        $this->url = $url;
        return $this;
    }


    /**
     * @param  string     $size
     * @return self
     * @throws \Exception
     */
    public function setSize($size) {

        $this->size = $size;
        return $this;
    }


    /**
     * @param  string     $text
     * @return self
     * @throws \Exception
     */
    public function setText($text) {

        $this->text = htmlspecialchars($text);
        return $this;
    }


    /**
     * @param  string     $value
     * @return self
     * @throws \Exception
     */
    public function setValue($value) {

        $this->attributes['value'] = htmlspecialchars($value);
        return $this;
    }


    /**
     * @return string
     */
    protected function makeControl() {

        $tpl = file_get_contents($this->theme_location . '/html/form/controls/modal.html');

        $id = uniqid('ck');
        $attributes = array(
            "id=\"{$id}\""
        );

        if ( ! empty($this->attributes)) {
            foreach ($this->attributes as $attr_name => $value) {
                if (trim($attr_name) != 'id') {
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

        switch ($this->size) {
            case 'small': $size = ' combine-modal-sm'; break;
            case 'large': $size = ' combine-modal-lg'; break;
            case 'normal': default: $size = '';break;
        }


        $tpl = str_replace('[ATTRIBUTES]', implode(' ', $attributes), $tpl);
        $tpl = str_replace('[THEME_SRC]',  $this->theme_src,          $tpl);
        $tpl = str_replace('[KEY]',        $id,                       $tpl);
        $tpl = str_replace('[URL]',        $this->url,                $tpl);
        $tpl = str_replace('[TITLE]',      $this->title,              $tpl);
        $tpl = str_replace('[TEXT]',       $this->text,              $tpl);
        $tpl = str_replace('[SIZE]',       $size,                     $tpl);

        return $tpl;
    }
}