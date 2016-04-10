<?php

namespace Combine\Table\classes;


/**
 * Class Filter
 * @package Combine\Table\classes
 */
class Filter {


    /**
     * Поиск
     *
     * @param array $data
     * @param array $search_rules
     * @param array $search_data
     *
     * @return mixed
     */
    public function searchData($data, $search_rules, $search_data) {

        foreach ($data as $key => $row) {

            foreach ($search_data as $key2 => $search_value) {
                $search_column = $search_rules[$key2];

                if ($search_column instanceof Search) {
                    if ($search_value == '') {
                        continue;
                    }

                    $search_field = $search_column->getField();

                    if ( ! array_key_exists($search_field, $row)) {
                        continue;
                    }

                    switch ($search_column->getType()) {
                        case 'date':
                            if ($search_value[0] && ! $search_value[1]) {
                                if (strtotime($row[$search_field]) < strtotime($search_value[0])) {
                                    unset($data[$key]);
                                    continue 2;
                                }

                            } elseif ( ! $search_value[0] && $search_value[1]) {
                                if (strtotime($row[$search_field]) > strtotime($search_value[1])) {
                                    unset($data[$key]);
                                    continue 2;
                                }

                            } elseif ($search_value[0] && $search_value[1]) {
                                if (strtotime($row[$search_field]) < strtotime($search_value[0]) ||
                                    strtotime($row[$search_field]) > strtotime($search_value[1])
                                ) {
                                    unset($data[$key]);
                                    continue 2;
                                }
                            }
                            break;

                        case 'select':
                            if ($row[$search_field] != $search_value) {
                                unset($data[$key]);
                                continue 2;
                            }
                            break;

                        case 'multiselect':
                            if ( ! in_array('', $search_value) && ! in_array($row[$search_field], $search_value)) {
                                unset($data[$key]);
                                continue 2;
                            }
                            break;

                        case 'text':
                            if (mb_stripos($row[$search_field], $search_value, null, 'utf8') === false) {
                                unset($data[$key]);
                                continue 2;
                            }
                            break;

                        case 'radio':
                            if ($row[$search_field] != $search_value) {
                                unset($data[$key]);
                                continue 2;
                            }
                            break;

                        case 'checkbox':
                            if ( ! in_array('', $search_value) && ! in_array($row[$search_field], $search_value)) {
                                unset($data[$key]);
                                continue 2;
                            }
                            break;
                    }
                }
            }
        }

        return $data;
    }


    /**
     * Сортировка
     *
     * @param array $data
     * @param int $order_num_column
     * @param string $order_type
     *
     * @return array
     */
    public function orderData($data, $order_num_column, $order_type = 'ASC') {

        $new_array      = array();
        $sortable_array = array();

        if (count($data) > 0) {
            foreach ($data as $k => $v) {
                if (is_array($v)) {
                    $i = 1;
                    foreach ($v as $k2 => $v2) {
                        if ($i++ == $order_num_column) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order_type) {
                case 'ASC':
                    asort($sortable_array);
                    break;

                case 'DESC':
                    arsort($sortable_array);
                    break;
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $data[$k];
            }
        }

        return $new_array;
    }


    /**
     * Страница
     *
     * @param array $data
     * @param int $records_per_page
     * @param int $current_page
     *
     * @return mixed
     */
    public function pageData($data, $records_per_page, $current_page = 1) {

        $new_data = array();
        $offset   = ($current_page - 1) * $records_per_page;
        $i        = 1;

        foreach ($data as $key => $row) {
            if ($current_page == 1) {
                if ($i <= $records_per_page) {
                    $new_data[$key] = $row;
                } else {
                    break;
                }

            } elseif ($current_page > 1) {
                if ($offset < $i && $i <= $offset + $records_per_page) {
                    $new_data[$key] = $row;

                } elseif ($offset < $i && $i > $offset + $records_per_page) {
                    break;
                }

            } else {
                break;
            }

            $i++;
        }

        return $new_data;
    }
} 