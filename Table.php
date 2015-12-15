<?php

namespace Combine;
use Combine\Table\classes\Row;
use Combine\Table\classes\Button;
use Combine\Table\classes\Column;
use Combine\Table\classes\Search;
use Combine\Table\classes\Filter;
use Combine\Utils\Mtpl;
use Combine\Utils\Session\SessionNamespace;

require_once 'Registry.php';

require_once 'Utils/Mtpl/Mtpl.php';
require_once 'Utils/Session/SessionNamespace.php';

require_once 'Table/classes/Row.php';
require_once 'Table/classes/Cell.php';
require_once 'Table/classes/Button.php';
require_once 'Table/classes/Column.php';
require_once 'Table/classes/Search.php';
require_once 'Table/classes/Filter.php';


/**
 * Class Table
 * @package Combine
 */
class Table {

    protected $resource	       = '';
    protected $edit_url        = '';
    protected $delete_url      = '';
    protected $add_url	       = '';
    protected $personal_url    = '';
    protected $show_checkboxes = true;
    protected $show_export     = false;
    protected $data            = array();
    protected $columns         = array();
    protected $buttons         = array();
    protected $search          = array();
    protected $sessData        = array();
    protected $record_count    = 0;
    protected $current_page    = 1;

    protected $show_delete   = true;
    protected $is_used_fetch = false;
    protected $HTML          = '';

    protected static $added_script = false;


    /**
     * @var SessionNamespace
     */
    protected $session            = null;
    protected $theme_src          = '';
    protected $theme_location     = '';
    protected $date_mask	      = "d.m.Y";
    protected $datetime_mask	  = "d.m.Y H:i";
    protected $records_per_page   = 25;
    protected $filter_column      = false;
    protected $round_record_count = false;
    protected $lang               = '';
    protected $locutions	      = array(
        'ru' => array(
            'Search'                                     => 'Поиск',
            'Clear'                                      => 'Очистить',
            'All'                                        => 'Все',
            'Add'                                        => 'Добавить',
            'Delete'                                     => 'Удалить',
            'num'                                        => '№',
            'from'                                       => 'из',
            'off'                                        => 'выкл',
            'on'                                         => 'вкл',
            'Total'                                      => 'Всего',
            'No records'                                 => 'Нет записей',
            'Are you sure you wcombine to delete this post?' => 'Вы действительно хотите удалить эту запись?',
            'You must select at least one record'        => 'Нужно выбрать хотя бы одну запись'
        ),
        'en' => array(
            'Search'                                     => 'Search',
            'Clear'                                      => 'Clear',
            'All'                                        => 'All',
            'Add'                                        => 'Add',
            'Delete'                                     => 'Delete',
            'num'                                        => '№',
            'from'                                       => 'from',
            'off'                                        => 'off',
            'on'                                         => 'on',
            'Total'                                      => 'Total',
            'No records'                                 => 'No records',
            'Are you sure you wcombine to delete this post?' => 'Are you sure you wcombine to delete this post?',
            'You must select at least one record'        => 'You must select at least one record'
        )
    );


    /**
     * @param string $resource
     */
	public function __construct($resource) {

        $this->resource = $resource;
        $this->lang     = Registry::getLanguage();

        $theme         = Registry::getTheme();
        $container_dir = substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']));

        $this->theme_src      = $container_dir . '/Themes/' . $theme;
        $this->theme_location = __DIR__ . '/Themes/' . $theme;

        $this->current_page = isset($_GET["_page_{$this->resource}"]) && $_GET["_page_{$this->resource}"] > 0
            ? (int)$_GET["_page_{$this->resource}"]
            : 1;


        $this->session = new SessionNamespace($this->resource);
        $this->session->access = new \stdClass();
        $this->session->db     = new \stdClass();
        $this->session->table  = new \stdClass();

        if (isset($this->session->table)) {
            // Количество записей
            if (isset($this->session->table->records_per_page)) {
                $this->records_per_page = $this->session->table->records_per_page;
                $this->records_per_page = $this->records_per_page === 0
                    ? 1000000000
                    : $this->records_per_page;
            }

            // Поисковые данные
            if (isset($this->session->table->search)) {
                $this->sessData['search'] = $this->session->table->search;
            }

            // Сортировка
            if (isset($this->session->table->order) && isset($this->session->table->order_type)) {
                $this->sessData['order']      = $this->session->table->order;
                $this->sessData['order_type'] = $this->session->table->order_type;
            }
        }
    }


    /**
     * @param string $edit_url
     */
    public function setEditUrl($edit_url) {
        $this->edit_url = $edit_url;
    }


    /**
     * @param string $delete_url
     */
    public function setDeleteUrl($delete_url) {
        $this->show_delete = true;
        $this->delete_url  = $delete_url;
    }


    /**
     * @param string $add_url
     */
    public function setAddUrl($add_url) {
        $this->add_url = $add_url;
    }


    /**
     * @param string $personal_url
     */
    public function setPersonalUrl($personal_url) {
        $this->personal_url = $personal_url;
    }


    /**
     *
     */
    public function showCheckboxes() {
        $this->show_checkboxes = true;
    }


    /**
     *
     */
    public function hideCheckboxes() {
        $this->show_checkboxes = false;
    }


    /**
     *
     */
    public function showExport() {
        $this->show_export = true;
    }


    /**
     *
     */
    public function hideExport() {
        $this->show_export = false;
    }


    /**
     * Получение таблицы
     * @return string
     */
    public function render() {

        $this->make();

        if ( ! self::$added_script) {
            $scripts    = $this->getScripts();
            $this->HTML = $scripts . $this->HTML;
            self::$added_script = true;
        }

        return $this->HTML;
    }


    /**
     * Скрипты
     * @return string
     */
    public function getScripts() {
        $scripts  = "<link rel=\"stylesheet\" href=\"{$this->theme_src}/css/table.css\"/>";
        $scripts .= "<script src=\"{$this->theme_src}/js/table.js\"></script>";
        $scripts .= "<script src=\"{$this->theme_src}/js/i18n.js\"></script>";

        return $scripts;
    }


    /**
     * Добавление кнопки
     * @param  string $title
     * @return Button
     */
    public function addButton($title) {
        return $this->buttons[] = new Button($title);
    }


    /**
     * Добавление своей кнопки
     * @param string $html
     */
    public function addCustomControl($html) {
        $this->buttons[] = $html;
    }


    /**
     * Добавление колонки
     *
     * @param string $title
     * @param string $field
     * @param string $type
     * @param string $width OPTIONAL parameter width column
     *
     * @return Column
     */
    public function addColumn($title, $field, $type, $width = '') {

        $column = new Column($title, $field, strtolower($type));

        if ($type == 'status') {
            $this->session->access->change_status_field = $field;
        }
        if ($width) {
            $column->setAttr('width', $width);
        }

        $this->columns[] = $column;
        return $column;
    }


    /**
     * Добавление поля для поиска
     *
     * @param string $title caption
     * @param string $field destination field name
     * @param string $type type of search field
     *
     * @return Search
     */
    public function addSearch($title, $field, $type) {

        $search = new Search($title, $field, $type);

        $this->search[] = $search;
        return $search;
    }


    /**
     * @param array $data
     */
    public function setData($data) {
        $this->data = $data;
    }


    /**
     * Получение отфильтрованных данных.
     * Только тех, которые будут показаны в таблице
     * @return array|Row
     */
    public function fetchData() {

        if ( ! $this->is_used_fetch) {
            $this->is_used_fetch = true;

            $filter = new Filter();

            $this->record_count = count($this->data);

            if ( ! empty($this->search) && ! empty($this->sessData['search'])) {
                $this->data = $filter->searchData($this->data, $this->search, $this->sessData['search']);
            }
            if ( ! empty($this->sessData['order'])) {
                $this->data = $filter->orderData($this->data, $this->sessData['order'], $this->sessData['order_type']);
            }

            $this->record_count = count($this->data);

            if ($this->record_count > (($this->current_page - 1) * $this->records_per_page) - $this->records_per_page) {
                $this->data = $filter->pageData($this->data, $this->records_per_page, $this->current_page);

                if ( ! empty($this->data)) {
                    foreach ($this->data as $key => $row) {
                        $this->data[$key] = new Row($row);
                    }
                }
            } else {
                $this->data = array();
            }
        }

        return $this->data;
    }


    /**
	 * Создание таблицы
	 * @return void
	 */
	protected function make() {

        $tpl = new Mtpl($this->theme_location . '/html/table.html');


        if ( ! empty($this->search)) {
            $search_value = ! empty($this->sessData['search']) ? $this->sessData['search'] : array();

            if ( ! empty($search_value) && count($search_value)) {
                $tpl->search->clear->assign('[RESOURCE]', $this->resource);
                $tpl->search->clear->assign('[TPL_DIR]',  $this->theme_src);
            }

            $tpl->search->assign('[RESOURCE]', $this->resource);
            $tpl->search->assign('[TPL_DIR]',  $this->theme_src);

            foreach ($this->search as $key => $search) {
                if ($search instanceof Search) {
                    $control_value = isset($search_value[$key])
                        ? $search_value[$key]
                        : '';
                    switch ($search->getType()) {
                        case 'text' :
                            $tpl->search->field->text->assign("[ID]",      "search-{$this->resource}-{$key}");
                            $tpl->search->field->text->assign("[NAME]",    "search[{$key}]");
                            $tpl->search->field->text->assign("[VALUE]",   $control_value);
                            $tpl->search->field->text->assign("[IN_TEXT]", $search->getIn());
                            break;

                        case 'radio' :
                            $data = $search->getData();
                            if ( ! empty($data)) {
                                $data  = array('' => $this->getLocution('All')) + $data;
                                foreach ($data as $radio_value => $radio_title) {
                                    $tpl->search->field->radio->assign("[ID]",      "search-{$this->resource}-{$key}");
                                    $tpl->search->field->radio->assign("[NAME]",    "search[{$key}]");
                                    $tpl->search->field->radio->assign("[VALUE]",   $radio_value);
                                    $tpl->search->field->radio->assign("[TITLE]",   $radio_title);
                                    $tpl->search->field->radio->assign("[IN_TEXT]", $search->getIn());

                                    $is_checked = $control_value == $radio_value
                                        ? 'checked="checked"'
                                        : '';
                                    $tpl->search->field->radio->assign("[IS_CHECKED]", $is_checked);
                                    $tpl->search->field->radio->reassign();
                                }
                            }
                            break;

                        case 'checkbox' :
                            $data = $search->getData();
                            if ( ! empty($data)) {
                                foreach ($data as $checkbox_value => $checkbox_title) {
                                    $tpl->search->field->checkbox->assign("[ID]",      "search-{$this->resource}-{$key}");
                                    $tpl->search->field->checkbox->assign("[NAME]",    "search[{$key}][]");
                                    $tpl->search->field->checkbox->assign("[VALUE]",   $checkbox_value);
                                    $tpl->search->field->checkbox->assign("[TITLE]",   $checkbox_title);
                                    $tpl->search->field->checkbox->assign("[IN_TEXT]", $search->getIn());

                                    $is_checked = is_array($control_value) && in_array($checkbox_value, $control_value)
                                        ? 'checked="checked"'
                                        : '';
                                    $tpl->search->field->checkbox->assign("[IS_CHECKED]", $is_checked);
                                    $tpl->search->field->checkbox->reassign();
                                }
                            }
                            break;

                        case 'date' :
                            $tpl->search->field->date->assign("[ID]",         "search-{$this->resource}-{$key}");
                            $tpl->search->field->date->assign("[NAME_FROM]",  "search[{$key}][0]");
                            $tpl->search->field->date->assign("[NAME_TO]",    "search[{$key}][1]");
                            $tpl->search->field->date->assign("[VALUE_FROM]", isset($control_value[0]) ? $control_value[0] : '');
                            $tpl->search->field->date->assign("[VALUE_TO]",   isset($control_value[1]) ? $control_value[1] : '');
                            $tpl->search->field->date->assign("[IN_TEXT]",    $search->getIn());
                            break;

                        case 'datetime' :
                            $tpl->search->field->datetime->assign("[ID]",         "search-{$this->resource}-{$key}");
                            $tpl->search->field->datetime->assign("[NAME_FROM]",  "search[{$key}][0]");
                            $tpl->search->field->datetime->assign("[NAME_TO]",    "search[{$key}][1]");
                            $tpl->search->field->datetime->assign("[VALUE_FROM]", isset($control_value[0]) ? $control_value[0] : '');
                            $tpl->search->field->datetime->assign("[VALUE_TO]",   isset($control_value[1]) ? $control_value[1] : '');
                            $tpl->search->field->datetime->assign("[IN_TEXT]",    $search->getIn());
                            break;

                        case 'select' :
                            $data    = $search->getData();
                            $options = array('' => '') + $data;
                            $tpl->search->field->select->assign("[ID]",       "search-{$this->resource}-{$key}");
                            $tpl->search->field->select->assign("[NAME]",     "search[{$key}]");
                            $tpl->search->field->select->assign("[IN_TEXT]",  $search->getIn());
                            $tpl->search->field->select->fillDropDown("[ID]", $options, $control_value);
                            break;

                        case 'multiselect' :
                            $data = $search->getData();
                            $tpl->search->field->multiselect->assign("[ID]",       "search-{$this->resource}-{$key}");
                            $tpl->search->field->multiselect->assign("[NAME]",     "search[{$key}][]");
                            $tpl->search->field->multiselect->assign("[IN_TEXT]",  $search->getIn());
                            $tpl->search->field->multiselect->fillDropDown("[ID]", $data, $control_value);
                            break;
                    }


                    $tpl->search->field->assign("[#]",        $key);
                    $tpl->search->field->assign("[OUT_TEXT]", $search->getOut());
                    $tpl->search->field->assign('[CAPTION]',  $search->getCaption());
                    $tpl->search->field->reassign();
                }
            }
        }

        if ($this->add_url) {
            $tpl->add_button->assign('[URL]', $this->add_url);
        }

        if ($this->show_delete && $this->delete_url != '') {
            $delete_msg    = $this->getLocution('Are you sure you wcombine to delete this post?');
            $no_select_msg = $this->getLocution('You must select at least one record');
            $tpl->del_button->assign(
                '[DELETE_ACTION]',
                "combine.table.del('{$this->resource}', '{$delete_msg}',  '{$no_select_msg}', '{$this->delete_url}')"
            );
        }


        $token = sha1(uniqid());
        $this->session->table->__csrf_token = $token;
        $tpl->assign('[TOKEN]',         $token);
        $tpl->assign('[RESOURCE]',      $this->resource);
        $tpl->assign('[BUTTONS]',       implode('', $this->buttons));
        $tpl->assign('[TOTAL_RECORDS]', ($this->round_record_count ? '~' : '') . $this->record_count);


        foreach ($this->columns as $key => $column) {
            if ($column instanceof Column) {
                if ($column->isSorting()) {
                    if (isset($this->sessData['order']) && $this->sessData['order'] == $key + 1) {
                        if ($this->sessData['order_type'] == "ASC") {
                            $tpl->header->cell->sort->order_asc->assign('[SRC]', $this->theme_src . '/img/asc.gif');
                        } elseif ($this->sessData['order_type'] == "DESC") {
                            $tpl->header->cell->sort->order_desc->assign('[SRC]', $this->theme_src . '/img/desc.gif');
                        }
                    }
                    $width = $column->getAttr('width');
                    if ($width) {
                        $tpl->header->cell->sort->assign('<th', "<th width=\"{$width}\"");
                    }
                    $tpl->header->cell->sort->assign('[COLUMN_NUMBER]', ($key + 1));
                    $tpl->header->cell->sort->assign('[CAPTION]',       $column->getTitle());

                } else {
                    $width = $column->getAttr('width');
                    if ($width) {
                        $tpl->header->cell->no_sort->assign('<th', "<th width=\"{$width}\"");
                    }
                    $tpl->header->cell->no_sort->assign('[CAPTION]', $column->getTitle());
                }

                $tpl->header->cell->reassign();
            }
        }

        if ($this->show_checkboxes == true) {
            $tpl->header->touchBlock('checkboxes');
        }

        $this->fetchData();


        if ( ! empty($this->data)) {
            $row_index  = 1;
            $row_number = $this->current_page > 1
                ? (($this->current_page - 1) * $this->records_per_page) + 1
                : 1;

            foreach ($this->data as $row) {
                if ( ! ($row instanceof Row)) {
                    $row = new Row($row);
                }

                $tpl->row->assign('[ID]', $row->id);
                $tpl->row->assign('[#]',  $row_number);


                if ($this->edit_url) {
                    $edit_url = $this->replaceTCOL($row, $this->edit_url);
                    $row->setAppendAttr('class', 'edit-row');
                    if (strpos($edit_url, 'javascript:') === 0) {
                        $row->setAppendAttr('onclick', substr($edit_url, 11));
                    } else {
                        $row->setAppendAttr('onclick', "combine.table.load('{$edit_url}');");
                    }
                }

                foreach ($this->columns as $column) {
                    if ($column instanceof Column) {
                        $cell  = $row->{$column->getField()};
                        $value = $cell->getValue();

                        switch ($column->getType()) {
                            case 'text':
                                $tpl->row->col->assign('[VALUE]', htmlspecialchars($value));
                                break;

                            case 'number':
                                $value = strrev($value);
                                $value = (string)preg_replace('/(\d{3})(?=\d)(?!\d*\.)/', '$1;psbn&', $value);
                                $value = strrev($value);
                                $tpl->row->col->assign('[VALUE]', $value);
                                break;

                            case 'html':
                                $tpl->row->col->assign('[VALUE]', htmlspecialchars_decode($value));
                                break;

                            case 'date':
                                $date = $value ? date($this->date_mask, strtotime($value)) : '';
                                $tpl->row->col->assign('[VALUE]', $date);
                                break;

                            case 'datetime':
                                $date = $value ? date($this->datetime_mask, strtotime($value)) : '';
                                $tpl->row->col->assign('[VALUE]', $date);
                                break;

                            case 'status':
                                if ($value == 'Y' || $value == 1) {
                                    $img = "<img src=\"{$this->theme_src}/img/lightbulb_on.png\" alt=\"[#on#]\" title=\"[#on#]/[#off#]\" data-value=\"{$value}\"/>";
                                } else {
                                    $img = "<img src=\"{$this->theme_src}/img/lightbulb_off.png\" alt=\"[#off#]\" title=\"[#on#]/[#off#]\" data-value=\"{$value}\"/>";
                                }
                                $tpl->row->col->assign('[VALUE]', $img);
                                break;
                        }

                        // Атрибуты ячейки
                        $column_attributes = $cell->getAttribs();
                        $attributes        = array();
                        foreach ($column_attributes as $attr => $value) {
                            $attributes[] = "$attr=\"{$value}\"";
                        }
                        $implode_attributes = implode(' ', $attributes);
                        $implode_attributes = $implode_attributes ? ' ' . $implode_attributes : '';

                        $tpl->row->col->assign('<td>', "<td{$implode_attributes}>");

                        if (end($this->columns) != $column) $tpl->row->col->reassign();
                    }
                }


                $attribs = $row->getAttribs();

                if ( ! empty($attribs)) {
                    $attribs_string = '';
                    foreach ($attribs as $name => $attr) {
                        $attribs_string .= " {$name}=\"{$attr}\"";
                    }
                    $tpl->row->assign('<tr', '<tr ' . $attribs_string);
                }

                if ($this->show_checkboxes) {
                    $tpl->row->checkboxes->assign('[ID]',       $row->id);
                    $tpl->row->checkboxes->assign('[RESOURCE]', $this->resource);
                    $tpl->row->checkboxes->assign('[#]',        $row_index);
                    $row_index++;
                }

                $row_number++;

                $tpl->row->reassign();
            }

        } else {
            $tpl->touchBlock('no_rows');
        }





        // Pagination
        $count_pages = ceil($this->record_count / $this->records_per_page);
        $tpl->pages->assign('[CURRENT_PAGE]', $this->current_page);
        $tpl->pages->assign('[COUNT_PAGES]',  $count_pages);

        if ($count_pages > 1 || $this->records_per_page > 25) {
            $tpl->pages->touchBlock('gotopage');
            $tpl->pages->touchBlock('per_page');

            if ($this->current_page > 1) {
                $tpl->pages->prev->assign('[PREV_PAGE]', $this->current_page - 1);
            }
            if ($this->current_page < $count_pages) {
                $tpl->pages->next->assign('[NEXT_PAGE]', $this->current_page + 1);
            }
        }

        $tpl->pages->fillDropDown(
            'records-per-page-[RESOURCE]',
            array(
                '25'  => '25',
                '50'  => '50',
                '100' => '100',
                '0'   => $this->getLocution('All')
            ),
            $this->records_per_page == 1000000000 ? 0 : $this->records_per_page
        );





        // Перевод
        if ( ! empty($this->locutions[$this->lang])) {
            foreach ($this->locutions[$this->lang] as $locution => $translate) {
                $tpl->assign("[#{$locution}#]", $translate);
            }
        }
        $this->HTML .= $tpl->render();
	}


    /**
     * @param  string $locution
     * @return mixed
     */
    protected function getLocution($locution) {
        return isset($this->locutions[$this->lang][$locution])
            ? htmlspecialchars($this->locutions[$this->lang][$locution])
            : htmlspecialchars($locution);
    }


    /**
     * Замена TCOL_ на значение указанного поля
     *
     * @param array $row Данные
     * @param string $str Строка с TCOL_ вставками
     *
     * @return string
     */
    protected function replaceTCOL($row, $str) {

        if (strpos($str, 'TCOL_') !== false) {
            foreach ($row as $field => $value) {
                $value = urlencode($value);
                $str   = str_replace('TCOL_' . strtoupper($field), $value, $str);
            }
            return $str;
        } else {
            return $str;
        }
    }
}