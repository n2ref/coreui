<?php

namespace Combine\Table\classes;
use Combine\Exception;

require_once __DIR__ . '/../../Exception.php';


/**
 * Class Search
 * @package Combine\Table\classes
 */
class Search {

    protected $caption = '';
    protected $field   = '';
    protected $type    = '';
    protected $data    = array();
    protected $in      = '';
    protected $out     = '';

    protected $available_types = array(
        'text',
        'date',
        'datetime',
        'radio',
        'checkbox',
        'select',
        'multiselect'
    );


    /**
     * @param string $caption
     * @param string $field
     * @param string $type
     * @throws Exception
     */
    public function __construct($caption, $field, $type) {
        $this->caption = $caption;
        $this->field   = $field;

        $type = strtolower($type);
        if (in_array($type, $this->available_types)) {
            $this->type = strtolower($type);
        } else {
            throw new Exception("Undefined search type '{$type}'");
        }
    }


    /**
     * @return string
     */
    public function getCaption() {
        return $this->caption;
    }


    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }


    /**
     * @return array
     */
    public function getData() {
        return $this->data;
    }


    /**
     * @return string
     */
    public function getIn() {
        return $this->in;
    }


    /**
     * @return string
     */
    public function getOut() {
        return $this->out;
    }


    /**
     * @param  array $data
     * @return self
     */
    public function setData(array $data) {
        $this->data = $data;
        return $this;
    }


    /**
     * @return string
     */
    public function getField() {
        return $this->field;
    }


    /**
     * @param  string $in
     * @return self
     */
    public function setIn($in) {
        $this->in = $in;
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
}