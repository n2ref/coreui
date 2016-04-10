<?php

namespace Combine;
use Combine\Form\Classes\Control;
use Combine\Form\Classes\Button;
use Combine\Utils\Session\SessionNamespace;

require_once __DIR__ . '/Registry.php';
require_once 'Utils/Session/SessionNamespace.php';


/**
 * Class Form
 * @package Combine
 */
class Form {

	protected $attributes       = array();
	protected $resource	 		= '';
	protected $template	 	    = '[default]';
	protected $current_position = 'default';
	protected $positions 	    = array();
	protected $ajax_request		= true;
	protected $theme_src        = '';
    protected $theme_location   = '';
    protected $buttons_wrapper  = null;

    /**
     * @var SessionNamespace|null
     */
    protected $session          = null;

	protected static $scripts_js  = array();
	protected static $scripts_css = array();

    protected $date_mask = "dd.mm.yyyy";
    protected $lang      = '';
	protected $locutions = array(
		'Save'   		 => 'Сохранить',
		'Select' 		 => 'Выбрать',
		'No read access' => 'Нет доступа для чтения этой записи'
	);

    /**
     * @param string $resource
     */
    public function __construct($resource) {

        $this->resource       = $resource;
        $this->lang           = Registry::getLanguage();
        $this->theme_src      = Registry::getThemeSrc();
        $this->theme_location = Registry::getThemeLocation();

        $this->session = new SessionNamespace($this->resource);
        if ( ! isset($this->session->form)) {
            $this->session->form = new \stdClass();
        }
    }


	/**
	 * @param  string       $name
	 * @param  array        $args
	 * @return Control\Text
     * @throws \Exception
	 */
	public function __call($name, $args) {

        if (strpos($name, 'add') === 0) {
            $label        = isset($args[0]) ? $args[0] : '';
            $control_name = isset($args[1]) ? $args[1] : null;

            $control = new Control\Text($label, $control_name);
            $control->setAttr('type', strtolower(substr($name, 3)));

            $this->positions[$this->current_position]['controls'][] = $control;
            $this->current_position = 'default';
            return $control;

        } else {
            throw new \Exception("Incorrect name magic function '{$name}'");
        }
	}


	/**
	 * @param  string $name
	 * @return self
	 */
	public function __get($name) {
		$this->current_position = $name;
		return $this;
	}


	/**
	 * Set HTML layout for the form
	 * @param  string $template
	 * @return bool
	 */
	public function setTemplate($template) {
        if (is_string($template)) {
			$this->template = $template;
			return true;
		} else {
			return false;
		}
	}


    /**
     * @param string $url
     */
    public function setBackUrl($url) {
        $this->setSessData('back_url', $url);
    }


	/**
	 * @param  string     $name
	 * @param  string     $value
	 * @return self
	 * @throws \Exception
	 */
	public function setAttr($name, $value) {

		if (is_string($name) && (is_string($value) || is_numeric($value))) {
            $this->attributes[$name] = $value;

		} else {
			throw new \Exception("Attribute not valid type. Need string or number");
		}

		return $this;
	}


	/**
	 * @param  array $attributes
	 * @return self
	 */
	public function setAttribs($attributes) {
		foreach ($attributes as $name => $value) {
			$this->setAttr($name, $value);
		}
		return $this;
	}


	/**
	 * @param bool $arg
	 */
	public function setAjaxRequest($arg = true) {
		$this->ajax_request = (bool)$arg;
	}


	/**
	 * @param $name
	 * @param $value
	 */
	public function setSessData($name, $value) {
        $this->session->form->$name = $value;
	}


    /**
     * @param  string     $name
     * @return mixed|null
     */
	public function getSessData($name) {
        if (isset($this->session->form) && isset($this->session->form->$name)) {
            return $this->session->form->$name;
        }
        return null;
	}


	/**
	 * @param string $html
	 */
	public function setButtonsWrapper($html) {
        $this->buttons_wrapper = $html;
	}


	/**
	 * @param  string $label
	 * @param  string $type
	 * @param  string $name
	 * @return Control|Control\Text|Control\Upload|Control\Select|Control\Wysiwyg
     * @throws Exception
	 */
	public function addControl($label, $type, $name = '') {

        $type      = ucfirst(strtolower($type));
        $file_type = __DIR__ . '/Form/Classes/Control/' . $type . '.php';

        if ( ! file_exists($file_type)) {
            throw new Exception("Type '{$type}' not found");
        }

        require_once $file_type;

        $class_name = 'Combine\\Form\\Classes\\Control\\'.$type;
        if ( ! class_exists($class_name)) {
            throw new Exception("Type '{$type}' broken. Not found class");
        }

        $control = new $class_name($label, $name);
        $control->setResource($this->resource);
        $this->positions[$this->current_position]['controls'][] = $control;
        $this->current_position = 'default';

        if ($name) {
            $this->session->form->controls[$name] = $type;
        }

        return $control;
    }


	/**
	 * @param  string $label
	 * @param  string $name
	 * @return Control\Text
	 */
	public function addText($label, $name = '') {
        require_once __DIR__ . '/Form/Classes/Control/Text.php';
        $control = new Control\Text($label, $name);
        $this->positions[$this->current_position]['controls'][] = $control;
        $this->current_position = 'default';

        if ($name) {
            $this->session->form->controls[$name] = 'text';
        }

        return $control;
    }


	/**
	 * @param  string $label
	 * @param  string $html
	 * @return Control\Custom
	 */
	public function addCustom($label, $html) {
        require_once __DIR__ . '/Form/Classes/Control/Custom.php';
        $control = new Control\Custom($label, $html);
        $this->positions[$this->current_position]['controls'][] = $control;
        $this->current_position = 'default';
        return $control;
    }


	/**
	 * @param  string $label
	 * @param  string $name
	 * @return Control\Password
	 */
	public function addPassword($label, $name = '') {
        require_once __DIR__ . '/Form/Classes/Control/Password.php';
        $control = new Control\Password($label, $name);
        $this->positions[$this->current_position]['controls'][] = $control;
        $this->current_position = 'default';
        if ($name) {
            $this->session->form->controls[$name] = 'password';
        }
        return $control;
    }


	/**
	 * @param  string $label
	 * @param  string $name
	 * @return Control\Email
	 */
	public function addEmail($label, $name = '') {
        require_once __DIR__ . '/Form/Classes/Control/Email.php';
        $control = new Control\Email($label, $name);
        $this->positions[$this->current_position]['controls'][] = $control;
        $this->current_position = 'default';
        if ($name) {
            $this->session->form->controls[$name] = 'email';
        }
        return $control;
    }


	/**
	 * @param  string $label
	 * @param  string $name
	 * @return Control\Select
	 */
	public function addSelect($label, $name = '') {
        require_once __DIR__ . '/Form/Classes/Control/Select.php';
        $control = new Control\Select($label, $name);
        $this->positions[$this->current_position]['controls'][] = $control;
        $this->current_position = 'default';
        if ($name) {
            $this->session->form->controls[$name] = 'select';
        }
        return $control;
    }


	/**
	 * @param  string $label
	 * @param  string $name
	 * @return Control\Radio
	 */
	public function addRadio($label, $name = '') {
        require_once __DIR__ . '/Form/Classes/Control/Radio.php';
        $control = new Control\Radio($label, $name);
        $this->positions[$this->current_position]['controls'][] = $control;
        $this->current_position = 'default';
        if ($name) {
            $this->session->form->controls[$name] = 'radio';
        }
        return $control;
    }


	/**
	 * @param  string $label
	 * @param  string $name
	 * @return Control\Checkbox
	 */
	public function addCheckbox($label, $name = '') {
        require_once __DIR__ . '/Form/Classes/Control/Checkbox.php';
        $control = new Control\Checkbox($label, $name);
        $this->positions[$this->current_position]['controls'][] = $control;
        $this->current_position = 'default';
        if ($name) {
            $this->session->form->controls[$name] = 'checkbox';
        }
        return $control;
    }


	/**
	 * @param  string $label
	 * @param  string $name
	 * @return Control\Captcha
	 */
	public function addCaptcha($label, $name = '') {
        require_once __DIR__ . '/Form/Classes/Control/Captcha.php';
        $control = new Control\Captcha($label, $name);
        $this->positions[$this->current_position]['controls'][] = $control;
        $this->current_position = 'default';
        if ($name) {
            $this->session->form->controls[$name] = 'captcha';
        }
        return $control;
    }


	/**
	 * @param  string $label
	 * @param  string $name
	 * @return Control\Date
	 */
	public function addDate($label, $name = '') {
        require_once __DIR__ . '/Form/Classes/Control/Date.php';
        $control = new Control\Date($label, $name);
        $this->positions[$this->current_position]['controls'][] = $control;
        $this->current_position = 'default';
        if ($name) {
            $this->session->form->controls[$name] = 'date';
        }
        return $control;
    }


	/**
	 * @param  string $label
	 * @param  string $name
	 * @return Control\Datetime
	 */
	public function addDatetime($label, $name = '') {
        require_once __DIR__ . '/Form/Classes/Control/Datetime.php';
        $control = new Control\Datetime($label, $name);
        $this->positions[$this->current_position]['controls'][] = $control;
        $this->current_position = 'default';
        if ($name) {
            $this->session->form->controls[$name] = 'datetime';
        }
        return $control;
    }


	/**
	 * @param  string $label
	 * @param  string $name
	 * @return Control\File
	 */
	public function addFile($label, $name = '') {
        require_once __DIR__ . '/Form/Classes/Control/File.php';
        $control = new Control\File($label, $name);
        $this->positions[$this->current_position]['controls'][] = $control;
        $this->current_position = 'default';
        if ($name) {
            $this->session->form->controls[$name] = 'file';
        }
        return $control;
    }


	/**
	 * @param  string $label
	 * @param  string $name
	 * @return Control\Upload
	 */
	public function addUpload($label, $name = '') {
        require_once __DIR__ . '/Form/Classes/Control/Upload.php';
        $control = new Control\Upload($label, $name);
        $control->setResource($this->resource);
        $this->positions[$this->current_position]['controls'][] = $control;
        $this->current_position = 'default';
        if ($name) {
            $this->session->form->controls[$name] = 'file_upload';
        }
        return $control;
    }


	/**
	 * @param  string $name
	 * @return Control\Hidden
	 */
	public function addHidden($name) {
        require_once __DIR__ . '/Form/Classes/Control/Hidden.php';
        $control = new Control\Hidden('', $name);
        $this->positions[$this->current_position]['controls'][] = $control;
        $this->current_position = 'default';
        if ($name) {
            $this->session->form->controls[$name] = 'hidden';
        }
        return $control;
    }


	/**
	 * @param  string $label
	 * @param  string $name
	 * @return Control\Textarea
	 */
	public function addTextarea($label, $name = '') {
        require_once __DIR__ . '/Form/Classes/Control/Textarea.php';
        $control = new Control\Textarea($label, $name);
        $this->positions[$this->current_position]['controls'][] = $control;
        $this->current_position = 'default';
        if ($name) {
            $this->session->form->controls[$name] = 'textarea';
        }
        return $control;
    }


	/**
	 * @param  string $label
	 * @param  string $name
	 * @param  string $config
	 * @return Control\Wysiwyg\Ckeditor
	 */
	public function addWysiwygCkeditor($label, $name = '', $config = 'basic') {
        require_once __DIR__ . '/Form/Classes/Control/Wysiwyg/Ckeditor.php';
        $control = new Control\Wysiwyg\Ckeditor($label, $name, $config);
        $this->positions[$this->current_position]['controls'][] = $control;
        $this->current_position = 'default';
        if ($name) {
            $this->session->form->controls[$name] = 'wysiwyg_ckeditor';
        }
        return $control;
    }


	/**
	 * @param  string $label
	 * @param  string $name
	 * @return Control\Markdown
	 */
	public function addMarkdown($label, $name = '') {
        require_once __DIR__ . '/Form/Classes/Control/Markdown.php';
        $control = new Control\Markdown($label, $name);
        $this->positions[$this->current_position]['controls'][] = $control;
        $this->current_position = 'default';
        if ($name) {
            $this->session->form->controls[$name] = 'markdown';
        }
        return $control;
    }


	/**
	 * @param  string $label
	 * @param  string $name
	 * @param  string $title
	 * @return Control\Modal
	 */
	public function addModal($label, $name = '', $title = '') {
        require_once __DIR__ . '/Form/Classes/Control/Modal.php';
        $control = new Control\Modal($label, $name, $title);
        $this->positions[$this->current_position]['controls'][] = $control;
        $this->current_position = 'default';
        if ($name) {
            $this->session->form->controls[$name] = 'modal';
        }
        return $control;
    }


	/**
	 * @param  string $label
	 * @param  string $name
	 * @return Control\Number
	 */
	public function addNumber($label, $name = '') {
        require_once __DIR__ . '/Form/Classes/Control/Number.php';
        $control = new Control\Number($label, $name);
        $this->positions[$this->current_position]['controls'][] = $control;
        $this->current_position = 'default';
        if ($name) {
            $this->session->form->controls[$name] = 'number';
        }
        return $control;
    }


	/**
	 * @param  string $title
	 * @return Button\Submit
	 */
	public function addSubmit($title) {
        require_once __DIR__ . '/Form/Classes/Button/Submit.php';
        $control = new Button\Submit($title);
        $this->positions[$this->current_position]['buttons'][] = $control;
        $this->current_position = 'default';
        return $control;
    }


	/**
	 * @param  string $title
	 * @return Button\Button
	 */
	public function addButton($title) {
        require_once __DIR__ . '/Form/Classes/Button/Button.php';
        $control = new Button\Button($title);
        $this->positions[$this->current_position]['buttons'][] = $control;
        $this->current_position = 'default';
        return $control;
    }


    /**
     * @param  string $name
     * @param  string $value
     * @param  string $active_value
     * @param  string $inactive_value
     * @param  bool   $default
     * @return Button\Switched
     */
	public function addButtonSwitched($name, $value, $active_value, $inactive_value, $default = true) {
        require_once __DIR__ . '/Form/Classes/Button/Switched.php';
        $control = new Button\Switched($name, $value, $active_value, $inactive_value, $default);
        $this->positions[$this->current_position]['buttons'][] = $control;
        $this->current_position = 'default';
        if ($name) {
            $this->session->form->controls[$name] = 'button_switched';
        }
        return $control;
    }



	/**
	 * @param  string $title
	 * @return Button\Reset
	 */
	public function addReset($title) {
        require_once __DIR__ . '/Form/Classes/Button/Reset.php';
        $control = new Button\Reset($title);
        $this->positions[$this->current_position]['buttons'][] = $control;
        $this->current_position = 'default';
        return $control;
    }


	/**
	 * Создание формы
	 * @return string
	 */
	public function render() {

        $token = sha1(uniqid());
        $this->setSessData('__csrf_token', $token);
        $this->attributes['data-csrf-token'] = $token;
        $this->attributes['data-resource']   = $this->resource;


		$attributes = array();

		if ($this->ajax_request && ! isset($this->attributes['onsubmit'])) {
            $this->attributes['onsubmit'] = 'return combine.form.submit(this);';
		}

		if ( ! empty($this->attributes)) {
			foreach ($this->attributes as $attr_name => $value) {
                $attributes[] = "$attr_name=\"$value\"";
			}
		}


		if ( ! empty($this->positions)) {
            $template = $this->template;
			foreach ($this->positions as $name => $position) {
				$controls_html = '';
				if ( ! empty($position['controls'])) {
					foreach ($position['controls'] as $control) {
                        if ($control instanceof Control) {
                            $controls_html .= $control->render();
                        }
					}
				}
				$buttons_html = '';
				if ( ! empty($position['buttons'])) {
                    $buttons_controls = array();
					foreach ($position['buttons'] as $button) {
                        if ($button instanceof Button) {
                            $buttons_controls[] = $button->render();
                        }
					}

                    $buttons_wrapper = $this->buttons_wrapper !== null
                        ? $this->buttons_wrapper
                        : file_get_contents($this->theme_location . '/html/form/wrappers/button.html');

                    $buttons_html = str_replace('[BUTTONS]', implode(' ', $buttons_controls), $buttons_wrapper);
                    $buttons_html = str_replace('[RESOURCE]', $this->resource, $buttons_html);
				}

				$template = str_replace("[{$name}]", $controls_html . $buttons_html, $template);
			}
		} else {
			$template = '';
		}


        // Скрипты
        $scripts_js = array();
        $main_js = "{$this->theme_src}/js/form.js?theme_src={$this->theme_src}";
        if ( ! isset(self::$scripts_js[$main_js])) {
            self::$scripts_js[$main_js] = false;
            $scripts_js[] = "<script src=\"{$main_js}\"></script>";
        }

        // Стили
        $scripts_css = array();
        $main_css = "{$this->theme_src}/css/form.css";
        if ( ! isset(self::$scripts_css[$main_css])) {
            self::$scripts_css[$main_css] = false;
            $scripts_css[] = "<link href=\"{$main_css}\" rel=\"stylesheet\"/>";
        }


        $form = file_get_contents($this->theme_location . '/html/form.html');

		$form = str_replace('[ATTRIBUTES]', implode(' ', $attributes), $form);
		$form = str_replace('[CONTROLS]',   $template, $form);
        $form = str_replace('[RESOURCE]',   $this->resource, $form);
        $form = str_replace('[CSS]',        implode('', $scripts_css), $form);
        $form = str_replace('[JS]',         implode('', $scripts_js), $form);


		return $form;
	}
}