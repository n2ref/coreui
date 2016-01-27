<?php

namespace Combine;

require_once 'Registry.php';


/**
 * Контекстные сообщения
 * Class Alert
 */
class Alert {

    protected static $added_script = false;

    /**
     * Возвращает сообщение об успешном выполнении
     * @param string $str
     * @return string
     */
    public static function getSuccess($str) {
        $scripts = self::getScripts();
        return "{$scripts}<div class=\"cmb-alert cmb-alert-success\">{$str}</div>";
    }


    /**
     * Возвращает сообщение с информацией
     * @param string $str
     * @return string
     */
    public static function getInfo($str) {
        $scripts = self::getScripts();
        return "{$scripts}<div class=\"cmb-alert cmb-alert-info\">{$str}</div>";
    }


    /**
     * Возвращает сообщение с предупреждением
     * @param string $str
     * @return string
     */
    public static function getWarning($str) {
        $scripts = self::getScripts();
        return "{$scripts}<div class=\"cmb-alert cmb-alert-warning\">{$str}</div>";
    }


    /**
     * Возвращает сообщение об ошибке или опасности
     * @param string $str
     * @return string
     */
    public static function getDanger($str) {
        $scripts = self::getScripts();
        return "{$scripts}<div class=\"cmb-alert cmb-alert-danger\">{$str}</div>";
    }


    /**
     * Скрипты
     * @return string
     */
    protected static function getScripts() {
        if ( ! self::$added_script) {
            self::$added_script = true;
            $theme_src = Registry::getThemeSrc();
            $scripts   = "<link rel=\"stylesheet\" href=\"{$theme_src}/css/alert.css\"/>";
        } else {
            $scripts = '';
        }
        return $scripts;
    }
}