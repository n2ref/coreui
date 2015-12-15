<?php

namespace Combine;
use Combine\Utils\Mtpl;

require_once 'Utils/Mtpl/Mtpl.php';
require_once 'Registry.php';


/**
 * Class Tree
 * @package Combine
 */
class Tree {

	protected $resource       = '';
	protected $options        = '';
	protected $data 	      = array();
	protected $theme_src      = '';
	protected $theme_location = '';

	protected static $added_script = false;


	/**
	 * @param string $resource
	 */
	public function __construct($resource) {
		$this->resource       = $resource;
        $this->theme_src      = Registry::getThemeSrc();
        $this->theme_location = Registry::getThemeLocation();
	}


	/**
	 * Общие опции для дерева
	 * @param array|string $options
	 * @throws Exception
	 */
	public function setOptions($options) {

		if (is_array($options)) {
			$this->options = json_encode($options);

		} elseif (is_string($options)) {
			$this->options = $options;

		} else {
			throw new Exception('Not valid option parameter');
		}
	}


	/**
	 * Элементы дерева
	 * @param array $data
	 */
	public function setData(array $data) {
		$this->data = $data;
	}


	/**
	 * Скрипты
	 * @return string
	 */
	public function getScripts() {
		$scripts  = "<script src=\"{$this->theme_src}/js/jstree.min.js\"></script>";
		$scripts .= "<script src=\"{$this->theme_src}/js/jstree.types.js\"></script>";
		$scripts .= "<link rel=\"stylesheet\" href=\"{$this->theme_src}/css/jstree.min.css\"/>";

		return $scripts;
	}


	/**
	 * Дерево
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
	 * Создание дерева
	 * @return string
	 */
    protected function make() {

		$tpl = new Mtpl($this->theme_location . '/html/tree.html');

        $tpl->assign('[RESOURCE]',       $this->resource);
        $tpl->assign('[JSTREE_OPTIONS]', $this->options);

        if ( ! empty($this->data)) {
            $element_tpl = $tpl->getBlock('element');

            $tpl->assign('[HTML_TREE]', $this->buildElements($this->data, $element_tpl));
        }

		return $tpl->render();

	}


    /**
     * Создание элементов дерева
     * @param  array    $elements
     * @param  string   $element_tpl
     * @param  int|null $parent_id
     *
     * @return string
     */
    protected function buildElements($elements, $element_tpl, $parent_id = null) {

        $tpl = new Mtpl();
        $tpl->setTemplate($element_tpl);

        foreach ($elements as $node) {
            if ($node['parent_id'] === $parent_id) {

                $tpl->assign('[URL]',   $node['url']);
                $tpl->assign('[TITLE]', $node['title']);

                $tpl->assign('[ELEMENT_OPTIONS]', ! empty($node['options'])
                    ? json_encode($node['options'])
                    : ''
                );

                $sub_elements = $this->buildElements($elements, $element_tpl, $node['id']);
                $tpl->assign('[SUB_ELEMENTS]', $sub_elements !== $element_tpl
                    ? '<ul>' . $sub_elements . '</ul>'
                    : ''
                );
                $tpl->reassign();
            }
        }

        return $tpl->render();
    }
}