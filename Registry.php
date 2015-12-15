<?php

namespace Combine;
use Combine\Utils;

require_once 'Utils/Db/Db.php';


/**
 * Class Init
 * @package Combine
 */
class Registry {

    /**
     * @var Utils\Db
     */
    protected static $db;

    /**
     * @var string
     */
    protected static $lang = 'en';


    /**
     * @var string
     */
    protected static $theme = 'default';


    private function __construct() {}


    /**
     * @param \PDO|\mysqli $db
     */
    public static function setDbConnection( $db) {
        self::$db = new Utils\Db($db);
    }

    /**
     * @return Utils\Db|null
     */
    public static function getDbConnection() {
        return self::$db;
    }


    /**
     * @param string $lang
     */
    public static function setLanguage($lang) {
        self::$lang = $lang;
    }


    /**
     * @return string
     */
    public static function getLanguage() {
        return self::$lang;
    }


    /**
     * @param string $theme
     */
    public static function setTheme($theme) {
        self::$theme = $theme;
    }


    /**
     * @return string
     */
    public static function getTheme() {
        return self::$theme;
    }


    /**
     * @return string
     */
    public static function getThemeLocation() {
        return __DIR__ . '/Themes/' . self::$theme;
    }


    /**
     * @return string
     */
    public static function getThemeSrc() {
        $container_dir = substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']));
        return $container_dir . '/Themes/' . self::$theme;
    }
}