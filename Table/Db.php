<?php

namespace Combine\Table;
use Combine;
use Combine\Table;
use Combine\Table\classes\Row;
use Combine\Table\classes\Button;
use Combine\Table\classes\Column;
use Combine\Table\classes\Cell;
use Combine\Table\classes\Search;
use Combine\Utils;
use Combine\Utils\Mtpl;

require_once __DIR__ . '/../Registry.php';
require_once __DIR__ . '/../Table.php';
require_once __DIR__ . '/../Utils/Db/Db.php';
require_once __DIR__ . '/../Utils/SqlParser/Select.php';
require_once __DIR__ . '/../Utils/Mtpl/Mtpl.php';



/**
 * Class Db
 * @package Combine\Table
 */
class Db extends Table {

    /**
     * @var Utils\Db\Adapters\Mysqli|Utils\Db\Adapters\PDO
     */
    protected $db;

    protected $table       = '';
    protected $primary_key = '';
    protected $sql         = '';
    protected $sql_params  = '';


    /**
     * @param string $resource
     */
    public function __construct($resource) {
        parent::__construct($resource);

        $this->session->access->delete = true;

        $db = Combine\Registry::getDbConnection();
        $this->db = $db->getAdapter();
    }


    /**
     * @param string $table
     */
    public function setTable($table) {
        $this->table = $table;
        $this->session->db->table = $this->table;
    }


    /**
     * @param string $key
     */
    public function setPrimaryKey($key) {
        $this->primary_key = $key;
        $this->session->db->primary_key = $this->primary_key;
    }


    /**
     *
     */
    public function showDelete() {
        $this->show_delete = true;
        $this->session->access->delete = true;
    }


    /**
     *
     */
    public function hideDelete() {
        $this->show_delete = false;
        $this->session->access->delete = false;
    }


    /**
     * @param string       $sql
     * @param array|string $params
     */
    public function setQuery($sql, $params = '') {
        $this->sql        = $sql;
        $this->sql_params = $params;
    }


    /**
     * Получение данных из базы
     * @return array
     */
    public function fetchData() {

        if ( ! $this->is_used_fetch) {
            $this->is_used_fetch = true;

            $select = new Utils\SqlParser\Select($this->sql);


            if ( ! empty($this->search) && ! empty($this->sessData['search'])) {
                foreach ($this->sessData['search'] as $key => $search_value) {
                    $search_column = $this->search[$key];

                    if ($search_column instanceof Search) {
                        $search_field = $search_column->getField();

                        switch ($search_column->getType()) {
                            case 'date':
                            case 'datetime':
                                if ( ! empty($search_value[0]) && empty($search_value[1])) {
                                    $quoted_value = $this->db->quote($search_value[0]);
                                    $select->addWhere("{$search_field} >= {$quoted_value}");

                                } elseif (empty($search_value[0]) && ! empty($search_value[1])) {
                                    $quoted_value = $this->db->quote($search_value[1]);
                                    $select->addWhere("{$search_field} <= {$quoted_value}");

                                } elseif ( ! empty($search_value[0]) && ! empty($search_value[1])) {
                                    $quoted_value1 = $this->db->quote($search_value[0]);
                                    $quoted_value2 = $this->db->quote($search_value[1]);
                                    $select->addWhere("{$search_field} BETWEEN {$quoted_value1} AND {$quoted_value2}");
                                }
                                break;

                            case 'select':
                                if ($search_value != '') {
                                    $quoted_value = $this->db->quote($search_value);
                                    $select->addWhere("{$search_field} = {$quoted_value}");
                                }
                                break;

                            case 'multiselect':
                                if ( ! empty($search_value)) {
                                    $quoted_value = $this->db->quote($search_value);
                                    $select->addWhere("{$search_field} IN ({$quoted_value})");
                                }
                                break;

                            case 'text':
                                if ($search_value != '') {
                                    $quoted_value = $this->db->quote('%' . $search_value . '%');
                                    $select->addWhere("{$search_field} LIKE {$quoted_value}");
                                }
                                break;

                            case 'radio':
                                if ($search_value != '') {
                                    $quoted_value = $this->db->quote($search_value);
                                    $select->addWhere("{$search_field} = {$quoted_value}");
                                }
                                break;

                            case 'checkbox':
                                if ( ! empty($search_value)) {
                                    $quoted_value = $this->db->quote($search_value);
                                    $select->addWhere("{$search_field} IN ({$quoted_value})");
                                }
                                break;
                        }
                    }
                }
            }


            if ( ! empty($this->sessData['order'])) {
                $select->setOrderBy(($this->sessData['order'] + 1) . ' ' . $this->sessData['order_type']);
            }


            if ( ! empty($this->current_page)) {
                if ($this->current_page == 1) {
                    $select->setLimit($this->records_per_page);

                } elseif ($this->current_page > 1) {
                    $offset = ($this->current_page - 1) * $this->records_per_page;
                    $select->setLimit($this->records_per_page, $offset);
                }
            }

            if ( ! $this->table) {
                $this->setTable($select->getTable());
            }


            $sql = $select->getSql();


            if ($this->round_record_count) {
                $explain = $this->db->fetchAll('EXPLAIN ' . $sql, $this->sql_params);
                $this->record_count = 0;
                foreach ($explain as $value) {
                    if ($value['rows'] > $this->record_count) {
                        $this->record_count = $value['rows'];
                    }
                }
                $result = $this->db->fetchAll($sql, $this->sql_params);
            } else {
                $result = $this->db->fetchAll("SELECT SQL_CALC_FOUND_ROWS " . substr(trim($sql), 6), $this->sql_params);
                $this->record_count = $this->db->fetchOne("SELECT FOUND_ROWS()");
            }


            if ( ! empty($result)) {
                foreach ($result as $key => $row) {
                    $this->data[$key] = new Row($row);
                }
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

        $token = sha1(uniqid());
        $this->session->table->__csrf_token = $token;
        $tpl->assign('[TOKEN]',    $token);
        $tpl->assign('[TPL_DIR]',  $this->theme_src);
        $tpl->assign('[RESOURCE]', $this->resource);

        if ( ! empty($this->search)) {
            $search_value = ! empty($this->sessData['search']) ? $this->sessData['search'] : array();

            if ( ! empty($search_value) && count($search_value)) {
                $tpl->search->touchBlock('clear');
            }

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

        if ($this->show_delete) {
            $delete_msg    = $this->getLocution('Are you sure you want to delete this post?');
            $no_select_msg = $this->getLocution('You must select at least one record');
            if ($this->delete_url != '') {
                $tpl->del_button->assign(
                    '[DELETE_ACTION]',
                    "combine.table.del('{$this->resource}', '{$delete_msg}',  '{$no_select_msg}', '{$this->delete_url}')"
                );
            }  else {
                $tpl->del_button->assign(
                    '[DELETE_ACTION]',
                    "combine.table.del('{$this->resource}', '{$delete_msg}',  '{$no_select_msg}')"
                );
            }
        }

        if ( ! $this->is_used_fetch) {
            $this->fetchData();
        }

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

        if ( ! empty($this->data)) {
            $row_index  = 1;
            $row_number = $this->current_page > 1
                ? (($this->current_page - 1) * $this->records_per_page) + 1
                : 1;

            foreach ($this->data as $row) {
                if ( ! ($row instanceof Row)) {
                    if ( ! is_array($row)) {
                        continue;
                    } else {
                        $row = new Row($row);
                    }
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
                        $cell = $row->{$column->getField()};
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
                                $cell->setAppendAttr('onclick', 'event.cancelBubble=true;');
                                if ( ! empty($this->table)) {
                                    $switch_active = " onclick=\"combine.table.switchActive(this, '{$this->resource}', '{$row->id}');\"";
                                } else {
                                    $switch_active = '';
                                }

                                if ($value == 'Y' || $value == 1) {
                                    $img = "<img src=\"{$this->theme_src}/img/lightbulb_on.png\" alt=\"[#on#]\" title=\"[#on#]/[#off#]\"
                                                 data-value=\"{$value}\"{$switch_active}/>";
                                } else {
                                    $img = "<img src=\"{$this->theme_src}/img/lightbulb_off.png\" alt=\"[#off#]\" title=\"[#on#]/[#off#]\"
                                                 data-value=\"{$value}\"{$switch_active}/>";
                                }

                                $tpl->row->col->assign('[VALUE]', $img);
                                break;
                        }

                        // Атрибуты ячейки
                        $column_attributes = $cell->getAttribs();
                        $attributes = array();
                        foreach ($column_attributes as $attr => $attr_value) {
                            $attributes[] = "$attr=\"{$attr_value}\"";
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
                    $tpl->row->checkboxes->assign('[ID]', $row->id);
                    $tpl->row->checkboxes->assign('[#]',  $row_index);
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
}