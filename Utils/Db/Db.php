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
     * @param \PDO|\mysqli $db
     * @throws Exception
     */
    public function __construct($db) {
        $this->setDbConnection($db);
    }


    /**
     * @param \PDO|\mysqli $db
     * @throws Exception
     */
    public function setDbConnection($db) {

        if ($db instanceof \PDO) {
            require_once 'Adapters/PDO.php';
            $this->adapter = new Adapters\PDO($db);

        } elseif ($db instanceof \mysqli) {
            require_once 'Adapters/Mysqli.php';
            $this->adapter = new Adapters\Mysqli($db);

        } else {
            throw new Exception("Incorrect db connection");
        }
    }


    /**
     * @return Adapters\PDO|Adapters\Mysqli
     */
    public function getAdapter() {
        return $this->adapter;
    }
}