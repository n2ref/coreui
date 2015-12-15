<?php

namespace Combine\Form\Classes\Control;
use Combine\Form\Classes\Control\File;
use Combine\Utils\Mtpl;


require_once __DIR__ . '/../../../Utils/Mtpl/Mtpl.php';
require_once __DIR__ . '/File.php';


/**
 * Class Upload2
 * @package Combine\Form\Classes\Control
 */
class Upload2 extends File {

    protected $files      = array();
    protected $attributes = array(
        'type' => 'hidden',
    );


    /**
     * @param string $name
     * @param int    $size
     * @return self
     */
    public function addFile($name, $size) {
        $this->files[] = array(
            'name' => $name,
            'size' => $size
        );
        return $this;
    }


    /**
     * @return string
     */
    protected function makeControl() {

        $tpl = new Mtpl($this->theme_location . '/html/form/controls/upload2.html');

        if ( ! empty($this->files)) {
            foreach ($this->files as $file) {
                $file_type = strpos($file['name'], '.') !== false
                    ? substr($file['name'], strrpos($file['name'], '.'))
                    : '';
                $file_id   = uniqid();

                $tpl->file->assign('[ID]',          $file_id);
                $tpl->file->assign('[NAME]',        $file['name']);
                $tpl->file->assign('[SIZE]',        $file['size']);
                $tpl->file->assign('[SIZE_FORMAT]', $this->formatSizeUnits($file['size']));
                $tpl->file->assign('[TYPE]',        $file_type);
                $tpl->file->reassign();

                if (empty($this->attributes['value'])) {
                    $this->attributes['value'] = "{$file['name']}:{$file['size']}:{$file_type}";
                } else {
                    $this->attributes['value'] .= "|{$file['name']}:{$file['size']}:{$file_type}";
                }
            }
        }

        $id = uniqid();
        $attributes = array(
            "id=\"control-{$id}\""
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


        $tpl->assign('[ATTRIBUTES]', implode(' ', $attributes));
        $tpl->assign('[THEME_SRC]',  $this->theme_src);
        $tpl->assign('[ID]',         $id);
        $tpl->assign('[RESOURCE]',   $this->resource);


        return $tpl->render();
    }
}