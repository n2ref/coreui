<?php

namespace Combine\Form;
use Combine\Form;
use Combine\Form\Classes\Control;
use Combine\Form\Classes\Button;
use Combine\Registry;
use Combine\Exception;
use Combine\Utils;
use Combine\Utils\Db\Adapters;

require_once __DIR__ . '/../Utils/SqlParser/Select.php';
require_once __DIR__ . '/../Registry.php';
require_once __DIR__ . '/../Exception.php';
require_once __DIR__ . '/../Form.php';


/**
 * Class Db
 * @package Combine\Form
 */
class Db extends Form {

    /**
     * @var Adapters\PDO|Adapters\Mysqli
     */
    protected $db           = null;
    protected $query_params = null;
    protected $data         = null;
    protected $query        = '';
    protected $table        = '';
    protected $primary_key  = '';
    protected $record_id    = '';

    /**
     * @param string $resource
     */
    public function __construct($resource) {
        parent::__construct($resource);
        $this->db = Registry::getDbConnection()->getAdapter();
        $this->session->form->controls = array();
    }


    /**
     * Установка таблицы
     * @param  string
     * @return string
     * @throws Exception
     */
    public function setTable($table) {
        if ( ! empty($table) && is_string($table)) {
            $this->table = $table;
            $this->setSessData('table', $this->table);
        } else {
            throw new Exception('Not valid table');
        }
    }


    /**
     * @param  string $primary_key
     * @param  string $record_id
     * @throws Exception
     */
    public function setPrimaryKey($primary_key, $record_id) {

        if ( ! empty($primary_key) && is_string($primary_key)) {
            $this->primary_key = $primary_key;
            $this->setSessData('primary_key', $primary_key);
        } else {
            throw new Exception('Not valid primary key');
        }

        if (is_string($record_id) || is_numeric($record_id)) {
            $this->record_id = $record_id;
            $this->setSessData('record_id', $record_id);
        } else {
            throw new Exception('Not valid record_id');
        }
    }


    /**
     * @param string       $query
     * @param array|string $params
     */
    public function setQuery($query, $params = null) {
        $this->query        = $query;
        $this->query_params = $params;
    }


    /**
     * @return array|bool
     * @throws Exception
     */
    public function fetchData() {

        if ($this->data === null) {
            if (empty($this->query)) {
                throw new Exception('Empty query');
            }
            $this->data = $this->db->fetchRow($this->query, $this->query_params);
        }

        return $this->data;
    }


    /**
     * @return string
     */
    public function render() {

        if (empty($this->table)) {
            $select = new Utils\SqlParser\Select($this->query);
            $this->setTable($select->getTable());
        }
        if (empty($this->primary_key)) {
            $quoted_table = $this->db->quoteIdentifier($this->table);
            $index        = $this->db->fetchRow("SHOW INDEX FROM {$quoted_table} where Key_name = 'PRIMARY'");
            $primary_key  = ! empty($index['Column_name']) ? $index['Column_name'] : '';
            $this->setPrimaryKey($primary_key, '');
        }

        $data = $this->fetchData();

        $token = sha1(uniqid());
        $this->setSessData('__csrf_token', $token);
        $this->attributes['data-csrf-token'] = $token;
        $this->attributes['data-resource']   = $this->resource;


        $attributes = array();

        if ($this->ajax_request && ! isset($this->attributes['onsubmit'])) {
            $this->attributes['onsubmit'] = 'return combine.form.submit(this);';
        }

        if ( ! empty($this->attributes)) {
            foreach ($this->attributes as $attr_name => $value) {
                $attributes[] = "$attr_name=\"$value\"";
            }
        }


        if ( ! empty($this->positions)) {
            $template = $this->template;
            foreach ($this->positions as $name => $position) {
                $controls_html = '';
                if ( ! empty($position['controls'])) {
                    foreach ($position['controls'] as $control) {
                        if ($control instanceof Control) {
                            if ($control instanceof Control\Text ||
                                $control instanceof Control\Number ||
                                $control instanceof Control\Date ||
                                $control instanceof Control\Datetime ||
                                $control instanceof Control\Hidden ||
                                $control instanceof Control\Email ||
                                $control instanceof Control\Password
                            ) {
                                if (isset($data[$control->getAttr('name')])) {
                                    $control->setAttr('value', $data[$control->getAttr('name')]);
                                }

                            } elseif ($control instanceof Control\Textarea ||
                                      $control instanceof Control\Wysiwyg ||
                                      $control instanceof Control\Markdown
                            ) {
                                if (isset($data[$control->getAttr('name')])) {
                                    $control->setValue($data[$control->getAttr('name')]);
                                }

                            } elseif ($control instanceof Control\Select) {
                                if (isset($data[$control->getAttr('name')])) {
                                    $explode_value = explode(',', $data[$control->getAttr('name')]);
                                    $control->setSelected($explode_value);
                                }

                            } elseif ($control instanceof Control\Checkbox ||
                                      $control instanceof Control\Radio
                            ) {
                                if (isset($data[$control->getAttr('name')])) {
                                    $explode_value = explode(',', $data[$control->getAttr('name')]);
                                    $control->setChecked($explode_value);
                                }
                            }
                            $controls_html .= $control->render();
                        }
                    }
                }
                $buttons_html = '';
                if ( ! empty($position['buttons'])) {
                    $buttons_controls = array();
                    foreach ($position['buttons'] as $button) {
                        if ($button instanceof Button) {
                            if ($button instanceof Button\Switched &&
                                isset($data[$button->getAttr('name')])
                            ) {
                                $button->setAttr('value', $data[$button->getAttr('name')]);
                            }
                            $buttons_controls[] = $button->render();
                        }
                    }

                    $buttons_wrapper = $this->buttons_wrapper !== null
                        ? $this->buttons_wrapper
                        : file_get_contents($this->theme_location . '/html/form/wrappers/button.html');

                    $buttons_html = str_replace('[BUTTONS]', implode(' ', $buttons_controls), $buttons_wrapper);
                }

                $template = str_replace("[{$name}]", $controls_html . $buttons_html, $template);
            }
        } else {
            $template = '';
        }


        // Скрипты
        $scripts_js = array();
        $main_js = "{$this->theme_src}/js/form.js?theme_src={$this->theme_src}";
        if ( ! isset(self::$scripts_js[$main_js])) {
            self::$scripts_js[$main_js] = false;
            $scripts_js[] = "<script src=\"{$main_js}\"></script>";
        }

        // Стили
        $scripts_css = array();
        $main_css = "{$this->theme_src}/css/form.css";
        if ( ! isset(self::$scripts_css[$main_css])) {
            self::$scripts_css[$main_css] = false;
            $scripts_css[] = "<link href=\"{$main_css}\" rel=\"stylesheet\"/>";
        }


        $form = file_get_contents($this->theme_location . '/html/form.html');

        $form = str_replace('[ATTRIBUTES]', implode(' ', $attributes), $form);
        $form = str_replace('[CONTROLS]',   $template, $form);
        $form = str_replace('[RESOURCE]',   $this->resource, $form);
        $form = str_replace('[CSS]',        implode('', $scripts_css), $form);
        $form = str_replace('[JS]',         implode('', $scripts_js), $form);


        return $form;
    }
}