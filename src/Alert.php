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
     * @param string $status
     * @param string $message
     * @return string
     */
    public static function get($status, $message) {
        $scripts = self::getScripts();
        return "{$scripts}<div class=\"cmb-alert cmb-alert-{$status}\">{$message}</div>";
    }


    /**
     * Возвращает сообщение об успешном выполнении
     * @param string $message
     * @return string
     */
    public static function getSuccess($message) {
        return self::get('success', $message);
    }


    /**
     * Возвращает сообщение с информацией
     * @param string $message
     * @return string
     */
    public static function getInfo($message) {
        return self::get('info', $message);
    }


    /**
     * Возвращает сообщение с предупреждением
     * @param string $message
     * @return string
     */
    public static function getWarning($message) {
        return self::get('warning', $message);
    }


    /**
     * Возвращает сообщение об ошибке или опасности
     * @param string $message
     * @return string
     */
    public static function getDanger($message) {
        return self::get('danger', $message);
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