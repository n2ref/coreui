<?php

namespace Combine;
use Combine\Utils\Session\SessionNamespace;
use Combine\Utils\Db\Adapters;
use Combine\Registry;
use Combine\Exception;

require_once 'Exception.php';
require_once 'Registry.php';
require_once 'Utils/Session/SessionNamespace.php';


/**
 * Class Handlers
 * @package Combine
 */
class Handlers {

    protected $resource = '';
    protected $process  = '';
    protected $response = array();

    /**
     * @var Adapters\PDO|Adapters\Mysqli
     */
    protected $db;


    public function __construct() {
        $this->resource = ! empty($_SERVER['HTTP_X_CMB_RESOURCE']) ? $_SERVER['HTTP_X_CMB_RESOURCE'] : '';
        $this->process  = ! empty($_SERVER['HTTP_X_CMB_PROCESS']) ? $_SERVER['HTTP_X_CMB_PROCESS'] : '';
        $this->db       = Registry::getDbConnection()->getAdapter();
    }



    /**
     * @param  string     $name
     * @return mixed|null
     */
    public function getSessData($name) {

        $session = new SessionNamespace($this->resource);
        if (isset($session->form) && isset($session->form->$name)) {
            return $session->form->$name;
        }
        return null;
    }


    /**
     * @return string
     */
    public function getProcess() {
        return $this->process;
    }


    /**
     * @return string
     */
    public function getResource() {
        return $this->resource;
    }


    /**
     * @return bool
     */
    public function isHandler() {
        return ! empty($this->process) &&
               in_array($this->process, array(
                   'save', 'delete', 'order',
                   'status', 'search', 'clear_search',
                   'records_per_page', 'upload'
               ));
    }


    /**
     * Получение ответа
     * @return string
     */
    public function getResponse() {

        $session = new SessionNamespace($this->resource);
        if (isset($session->form) && isset($session->form->back_url)) {
            $this->response['back_url'] = $session->form->back_url;
        }

        if ( ! isset($this->response['status'])) {
            $this->response['status']  = 'success';
        }

        return json_encode($this->response);
    }


    /**
     * @return bool
     */
    public function process() {

        if ($this->isHandler()) {
            try {
                if (empty($this->resource)) {
                    throw new Exception('Resource empty');
                }

                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $data = $_POST;

                } elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
                    $data = $_GET;

                } else {
                    $data = array();
                    parse_str(file_get_contents('php://input'), $data);
                }

                switch ($this->process) {
                    case 'save' :             $this->saveData($data); break;
                    case 'delete' :           $this->deleteData($data); break;
                    case 'search' :           $this->setSearch($data); break;
                    case 'clear_search' :     $this->setClearSearch(); break;
                    case 'records_per_page' : $this->setRecordPerPage($data); break;
                    case 'status' :           $this->setStatus($data); break;
                    case 'order' :            $this->setOrder($data); break;
                    case 'upload' :           $this->uploadFile(); break;
                    case 'export' :
                    case 'sequence' :
                    case 'sort' :
                    default : throw new Exception('Unknown process name'); break;
                }

                $this->response['status']  = 'success';
                return true;

            } catch (Exception $e) {
                $this->addError($e->getMessage());
                return false;
            }
        }

        return false;
    }


    /**
     * Установка текста ошибки
     * @param string $message
     */
    protected function addError($message) {

        $this->response['message'] = $message;
        $this->response['status']  = 'error';
    }


    /**
     * @return mixed
     */
    protected function getRecordId() {
        $session = new SessionNamespace($this->resource);
        if (isset($session->form) && isset($session->form->record_id)) {
            return $session->form->record_id;
        }
        return null;
    }


    /**
     * @return bool
     */
    protected function isValidRequest($component) {

        if ( ! empty($this->resource)) {
            $session = new SessionNamespace($this->resource);

            if (isset($session->$component) &&
                isset($session->$component->__csrf_token) &&
                isset($_SERVER['HTTP_X_CMB_CSRF_TOKEN'])
            ) {
                return $session->$component->__csrf_token === $_SERVER['HTTP_X_CMB_CSRF_TOKEN'];
            }
        }

        return false;
    }


    /**
     * @param  array $data
     * @return int
     * @throws Exception
     */
    protected function saveData($data) {

        if ( ! $this->isValidRequest('form')) {
            throw new Exception('Not valid request');
        }

        $session = new SessionNamespace($this->resource);

        if (empty($session->form->table)) {
            throw new Exception('Table empty');
        }

        $table = $session->form->table;
        if ( ! is_string($table)) {
            throw new Exception('Table parameter not string');
        }

        if (empty($session->form->primary_key)) {
            throw new Exception('Primary key empty');
        }

        $primary_key = $session->form->primary_key;
        if ( ! is_string($primary_key) && ! is_numeric($primary_key)) {
            throw new Exception('Primary key not valid');
        }

        if (empty($data) || ! is_array($data)) {
            throw new Exception('Data not valid');
        }

        $record_id = $session->form->record_id;
        if ($record_id) {
            $is_save = $this->db->update($table, $data, "{$primary_key} = {$record_id}");
        } else {
            $is_save = $this->db->insert($table, $data);
            $record_id  = $this->db->lastInsertId($table);
        }

        if ( ! $is_save) {
            throw new Exception('Error save data');
        }

        if (isset($session->form->back_url)) {
            $this->response['back_url'] = $session->form->back_url;
        }

        $this->response['status'] = 'success';

        return $record_id;
    }


    /**
     * Отсеевание необазначенных полей
     * @param array $data
     * @return array
     * @throws \Combine\Exception
     */
    protected function filterControls(array $data) {
        $session = new SessionNamespace($this->resource);

        if (empty($session->form) || empty($session->form->controls)) {
            throw new Exception('Empty controls');
        }

        foreach ($data as $name => $value) {
            if ( ! isset($session->form->controls[$name])) {
                unset($data[$name]);
            }
        }

        return $data;
    }


    /**
     * @param  array $data
     * @throws Exception
     */
    protected function setStatus($data) {

        if ( ! $this->isValidRequest('table')) {
            throw new Exception('Not valid request');
        }

        $session = new SessionNamespace($this->resource);

        if (empty($data['rec_id'])) {
            throw new Exception('Record id empty');

        } elseif ( ! is_string($data['rec_id']) && ! is_numeric($data['rec_id'])) {
            throw new Exception('Record id not valid');

        } elseif (empty($data['new_value']) || ( ! is_string($data['new_value']) && ! is_int($data['new_value']))) {
            throw new Exception('Switched value not valid');

        } elseif (empty($session->access) || empty($session->access->change_status_field)) {
            throw new Exception('Switched field empty');
        }



        if (isset($session->db) && ! empty($session->db->table)) {
            $table = $session->db->table;
        } else {
            throw new Exception('Table not found');
        }

        $primary_key = ! empty($session->db->primary_id)
            ? $session->db->primary_id
            : 'id';

        $primary_key = $this->db->quoteIdentifier($primary_key);
        $where       = $this->db->quoteInto("{$primary_key} = ?", $data['rec_id']);
        $is_update   = $this->db->update($table, array(
            $session->access->change_status_field => $data['new_value']
        ), $where);

        if ( ! $is_update) {
            throw new Exception('Error update data');
        }
    }


    /**
     * @param  array $data
     * @throws Exception
     */
    protected function setSearch($data) {

        if ( ! $this->isValidRequest('table')) {
            throw new Exception('Not valid request');
        }

        $session = new SessionNamespace($this->resource);

        if (isset($data['search'])) {
            $session->table->search = $data['search'];
        }
    }

    
    /**
     * @param  array $data
     * @return bool
     * @throws Exception
     */
    protected function setRecordPerPage($data) {

        if ( ! $this->isValidRequest('table')) {
            throw new Exception('Not valid request');
        }

        $session = new SessionNamespace($this->resource);

        if (isset($data['records_per_page'])) {
            $session->table->records_per_page = (int)$data['records_per_page'];
        }
    }


    /**
     * @throws Exception
     */
    protected function setClearSearch() {

        if ( ! $this->isValidRequest('table')) {
            throw new Exception('Not valid request');
        }

        $session = new SessionNamespace($this->resource);
        $session->table->search = false;
    }


    /**
     * @throws Exception
     */
    protected function uploadFile() {

        if ( ! $this->isValidRequest('form')) {
            throw new Exception('Not valid request');
        }

        if ( ! empty($_FILES['combine_upload'])) {
            if ($_FILES['combine_upload']['error']) {
                $this->response['status'] = 'error';
            } else {
                $new_name = sys_get_temp_dir() . '/' . uniqid('cms');
                rename($_FILES['combine_upload']['tmp_name'], $new_name);
                $this->response['tmp_name'] = $new_name;
                $this->response['filename'] = $_FILES['combine_upload']['name'];
            }

        } else {
            throw new Exception('Empty file');
        }
    }


    /**
     * @param  array $data
     * @throws Exception
     */
    protected function setOrder($data) {

        if ( ! $this->isValidRequest('table')) {
            throw new Exception('Not valid request');
        }

        if (empty($data['column_number'])) {
            throw new Exception('Parameter column_number empty');
        }

        $session       = new SessionNamespace($this->resource);
        $column_number = (int)$data['column_number'];

        if (isset($session->table->order) && $column_number != $session->table->order) {
            $session->table->order_type = 'ASC';
            $session->table->order      = $column_number;

        } else {
            if (isset($session->table->order_type) && $session->table->order_type == 'ASC') {
                $session->table->order_type = 'DESC';
                $session->table->order      = $column_number;

            } elseif (isset($session->table->order_type) && $session->table->order_type == 'DESC') {
                unset($session->table->order);
                unset($session->table->order_type);

            } else {
                $session->table->order_type = 'ASC';
                $session->table->order      = $column_number;
            }
        }
    }


    /**
     * @param  array $data
     * @throws Exception
     */
    protected function deleteData($data) {

        if ( ! $this->isValidRequest('table')) {
            throw new Exception('Not valid request');
        }

        $session = new SessionNamespace($this->resource);

        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            throw new Exception('Invalid request method, need DELETE');

        } elseif (empty($data['id_rows']) || ! is_array($data['id_rows'])) {
            throw new Exception('Records id not valid');

        } elseif (empty($session->db) || empty($session->db->table)) {
            throw new Exception('No set table');

        } elseif (empty($session->access) || empty($session->access->delete) || ! $session->access->delete) {
            throw new Exception('Access denied');
        }

        $table = $session->db->table;
        $primary_key = ! empty($session->db->primary_id)
            ? $session->db->primary_id
            : 'id';

        $primary_key = $this->db->quoteIdentifier($primary_key);
        $where       = $this->db->quoteInto("{$primary_key} IN(?)", $data['id_rows']);
        $is_delete   = $this->db->delete($table, $where);

        if ( ! $is_delete) {
            throw new Exception('Error delete data');
        }
    }
}