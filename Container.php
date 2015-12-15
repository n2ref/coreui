<?php

namespace Combine;


require_once 'Registry.php';


/**
 * Class Container
 * @package Combine
 */
class Container {

    protected $content = '';
    protected $header  = '';

    protected static $added_script = false;


    /**
     * @param string $header
     */
    public function __construct($header) {

        $this->theme_src      = Registry::getThemeSrc();
        $this->theme_location = Registry::getThemeLocation();
        $this->header         = $header;
    }


    /**
     * Установка содержимого для таба
     * @param string $content
     */
    public function setContent($content) {
        $this->content = $content;
    }


    /**
     * Создание и возврат табов
     * @return string
     */
    public function render() {

        if ( ! self::$added_script) {
            $scripts = $this->getScripts();
            $content = $this->make();
            $content = $scripts . $content;
            self::$added_script = true;

        } else {
            $content = $this->make();
        }

        return $content;
    }


    /**
     * Скрипты
     * @return string
     */
    protected function getScripts() {
        $scripts = "<link rel=\"stylesheet\" href=\"{$this->theme_src}/css/container.css\"/>";

        return $scripts;
    }


    /**
     * Создание контейнера
     * @return string
     */
    protected function make() {

        $tpl = file_get_contents($this->theme_location . '/html/container.html');
        $tpl = str_replace('[HEADER]',  $this->header,  $tpl);
        $tpl = str_replace('[CONTENT]', $this->content, $tpl);
        return $tpl;
    }
}