<?php

namespace Combine\Utils;
use Combine\Exception;
use Combine\Utils\Db\Adapters;


require_once __DIR__ . '/../../Exception.php';


/**
 * Class Db
 * @package Combine\Utils
 */
class Db {

    /**
     * @var Adapters\PDO|Adapters\Mysqli
     */
    protected $adapter;

    /**
     * @param  \PDO|\mysqli $db
     * @param  string       $adapter_name
     * @throws Exception
     */
    public function __construct($db, $adapter_name = 'PDO') {
        $this->setDbConnection($db, $adapter_name);
    }


    /**
     * @param  \PDO|\mysqli $db
     * @param  string       $adapter_name
     * @throws Exception
     */
    public function setDbConnection($db, $adapter_name = 'PDO') {

        switch (strtolower($adapter_name)) {
            case 'pdo':
                require_once 'Adapters/PDO.php';
                $this->adapter = new Adapters\PDO($db);
                break;

            case 'mysqli':
                require_once 'Adapters/Mysqli.php';
                $this->adapter = new Adapters\Mysqli($db);
                break;

            default : throw new Exception("Incorrect adapter '{$adapter_name}'");
        }
    }


    /**
     * @return Adapters\PDO|Adapters\Mysqli
     */
    public function getAdapter() {
        return $this->adapter;
    }
}