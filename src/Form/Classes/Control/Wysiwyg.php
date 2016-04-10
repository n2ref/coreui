<?php

namespace Combine\Form\Classes\Control;
use Combine\Form\Classes\Control;


require_once __DIR__ . '/../Control.php';



/**
 * Class Wysiwyg
 * @package Combine\Form\Control
 */
class Wysiwyg extends Control {

    protected $value      = '';
    protected $config     = 'basic';
    protected $attributes = array();


    /**
     * @param string $label
     * @param string $name
     * @param string $config
     */
    public function __construct($label, $name, $config = 'basic') {
        parent::__construct($label, $name);
        $this->config = $config;
    }


    /**
     * @param string $config
     */
    public function setConfig($config) {
        $this->config = $config;
    }


    /**
     * @param  string $string
     * @return $this
     */
    public function setValue($string) {
        $this->value = htmlspecialchars($string);
        return $this;
    }
    protected $custom_config = array();


    /**
     * @param array $config
     * @return $this
     */
    public function setCustomConfig(array $config) {
        $this->config = 'custom';
        $this->custom_config = $config;
        return $this;
    }


    /**
     * @return string
     * @throws \Exception
     */
    protected function makeControl() {

        $tpl = file_get_contents($this->theme_location . '/html/form/controls/wysiwyg/ckeditor.html');

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


        // TODO сделать валидаторы
        if ( ! empty($this->validators)) {
            foreach ($this->validators as $validator) {

            }
        }


        switch ($this->config) {
            case 'basic':
                $config = ',' . json_encode(array(
                        'toolbarGroups' => array(
                            array('name' => 'basicstyles', 'groups' => array('basicstyles')),
                            array('name' => 'links', 'groups' => array('links')),
                            array('name' => 'paragraph', 'groups' => array('list', 'indent', 'align')),
                            array('name' => 'insert', 'groups' => array('insert')),
                        ),
                        'removeButtons' => 'Underline,Strike,Subscript,Superscript,Anchor,SpecialChar,Flash,Smiley,Iframe,PageBreak'
                    ));
                break;

            case 'standard':
                $config = ',' . json_encode(array(
                        'toolbarGroups' => array(
                            array('name' => 'clipboard', 'groups' => array('undo', 'clipboard')),
                            array('name' => 'links', 'groups' => array('links')),
                            array('name' => 'insert', 'groups' => array('insert')),
                            array('name' => 'tools', 'groups' => array('Maximize')),
                            '/',
                            array('name' => 'basicstyles', 'groups' => array('basicstyles', 'cleanup')),
                            array('name' => 'paragraph', 'groups' => array('list', 'indent', 'blocks', 'align')),
                        ),
                        'removeButtons' => 'Underline,Strike,Subscript,Superscript,Anchor,SpecialChar,Flash,Smiley,Iframe,PageBreak,CreateDiv'
                    ));
                break;

            case 'full':
                $config = ',' . json_encode(array(
                        'toolbarGroups' => array(
                            array('name' => 'clipboard', 'groups' => array('undo', 'clipboard')),
                            array('name' => 'links', 'groups' => array('links')),
                            array('name' => 'insert', 'groups' => array('insert')),
                            array('name' => 'editing', 'groups' => array( 'find', 'spellchecker')),
                            array('name' => 'tools', 'groups' => array('Maximize')),
                            '/',
                            array('name' => 'basicstyles', 'groups' => array('basicstyles', 'cleanup')),
                            array('name' => 'paragraph', 'groups' => array('list', 'indent', 'blocks', 'align')),
                            array('name' => 'colors'),
                            '/',
                            array('name' => 'styles'),
                        ),
                        'removeButtons' => 'Underline,Strike,Subscript,Superscript,Anchor,SpecialChar,Flash,Smiley,Iframe,PageBreak,CreateDiv,Styles'
                    ));
                break;

            case 'custom':
                $config = ',' . json_encode($this->custom_config);
                break;

            default : throw new \Exception("Incorrect ckeditor config '{$this->config}'"); break;
        }


        $tpl = str_replace('[TPL_DIR]',    $this->theme_src,          $tpl);
        $tpl = str_replace('[ATTRIBUTES]', implode(' ', $attributes), $tpl);
        $tpl = str_replace('[VALUE]',      $this->value,              $tpl);
        $tpl = str_replace('[ID]',         $id,                       $tpl);
        $tpl = str_replace('[CONFIG]',     $config,                   $tpl);

        return $tpl;
    }
}