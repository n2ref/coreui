<?php

namespace Combine;
use Combine\Panel\classes\ComboTab;
use Combine\Utils\Mtpl;

require_once 'Utils/Mtpl/Mtpl.php';
require_once 'Panel/classes/ComboTab.php';
require_once 'Registry.php';


/**
 * Class Panel
 * @package Combine
 */
class Panel {

    protected $active_tab     = '';
    protected $title          = '';
    protected $content        = '';
    protected $resource       = '';
    protected $url            = '';
    protected $tabs           = array();
    protected $theme_src      = '';
    protected $theme_location = '';

    protected static $added_script = false;


    /**
     * @param string $resource
     * @param string $url
     */
    public function __construct($resource, $url = '') {

        $this->theme_src      = Registry::getThemeSrc();
        $this->theme_location = Registry::getThemeLocation();

        $this->resource = $resource;
        $this->url      = $url;

        if (isset($_GET[$this->resource])) $this->active_tab = $_GET[$this->resource];
    }


    /**
     * @param {string} $title
     */
    public function setTitle($title) {
        $this->title = $title;
    }


    /**
     * Добавление таба
     *
     * @param string $title
     * @param string $id
     * @param bool   $disabled
     */
    public function addTab($title, $id, $disabled = false) {
        $this->tabs[] = array(
            'title'    => $title,
            'id'       => $id,
            'disabled' => $disabled
        );
    }


    /**
     * Добавление комбо таба
     * @param  string   $title
     * @return ComboTab
     */
    public function addComboTab($title) {
        $combo_tab = new ComboTab($title);
        $this->tabs[] = $combo_tab;
        return $combo_tab;
    }

    /**
     * Установка содержимого для контейнера
     * @param string $content
     */
    public function setContent($content) {
        $this->content = $content;
    }


    /**
     * Скрипты
     * @return string
     */
    public function getScripts() {
        $scripts  = "<script src=\"{$this->theme_src}/js/panel.js\"></script>";
        $scripts .= "<link rel=\"stylesheet\" href=\"{$this->theme_src}/css/panel.css\"/>";

        return $scripts;
    }



    /**
     * Получение идентификатора активного таба
     * @return string
     */
    public function getActiveTab() {

        if ($this->active_tab == '' && ! empty($this->tabs)) {
            reset($this->tabs);
            $tab = current($this->tabs);

            if ($tab instanceof ComboTab) {
                $elements = $tab->getElements();
                foreach ($elements as $element) {
                    if ($element['type'] == $tab::ELEMENT_ITEM) {
                        $this->active_tab = $element['id'];
                        break;
                    }
                }
            } else {
                $this->active_tab = $tab['id'];
            }
        }

        return $this->active_tab;
    }


    /**
     * Создание и возврат контейнера
     * @return string
     */
    public function render() {

        if ( ! self::$added_script) {
            $scripts   = $this->getScripts();
            $container = $this->make();
            $container = $scripts . $container;
            self::$added_script = true;

        } else {
            $container = $this->make();
        }

        return $container;
    }


    /**
     * Создание контейнера
     * @return string
     */
    protected function make() {

        $tpl = new Mtpl($this->theme_location . '/html/panel.html');

        $tpl->assign('[ID]',      $this->resource);
        $tpl->assign('[CONTENT]', $this->content);

        if ( ! empty($this->title)) {
            $tpl->title->assign('[TITLE]', $this->title);
        }

        if ( ! empty($this->tabs)) {
            foreach ($this->tabs as $tab) {
                if ($tab instanceof ComboTab) {
                    $elements = $tab->getElements();
                    if ( ! empty($elements)) {
                        $combo_tab_class = '';
                        foreach ($elements as $element) {
                            if ($element['type'] == $tab::ELEMENT_BREAK) {
                                $tpl->tabs->elements->combo_tab->elements->touchBlock('break');

                            } else {
                                $url = $this->url . '&' . $this->resource . '=' . $element['id'];
                                if ($element['disabled']) {
                                    $class = 'disabled';
                                    $url   = 'javascript:void(0);';
                                } elseif ($this->active_tab == $element['id']) {
                                    $class = 'active';
                                    $combo_tab_class = 'active';
                                } else {
                                    $class = '';
                                }

                                $tpl->tabs->elements->combo_tab->elements->element->assign('[CLASS]', $class);
                                $tpl->tabs->elements->combo_tab->elements->element->assign('[TITLE]', $element['title']);
                                $tpl->tabs->elements->combo_tab->elements->element->assign('[URL]',   $url);
                            }
                            $tpl->tabs->elements->combo_tab->elements->reassign();
                        }

                        $tpl->tabs->elements->combo_tab->assign('[TITLE]', $tab->getTitle());
                        $tpl->tabs->elements->combo_tab->assign('[CLASS]', $combo_tab_class);
                    }
                } else {
                    $url = $this->url . '&' . $this->resource . '=' . $tab['id'];
                    if ($tab['disabled']) {
                        $class = 'disabled';
                        $url   = 'javascript:void(0);';
                    } elseif ($this->active_tab == $tab['id']) {
                        $class = 'active';
                    } else {
                        $class = '';
                    }

                    $tpl->tabs->elements->tab->assign('[CLASS]', $class);
                    $tpl->tabs->elements->tab->assign('[TITLE]', $tab['title']);
                    $tpl->tabs->elements->tab->assign('[URL]',   $url);
                }

                $tpl->tabs->elements->reassign();
            }
        }

        return $tpl->render();
    }
} 