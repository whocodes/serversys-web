<?php
if(!defined('IN_SYS')) die('Wrong number.');

class SysHandler {
	/*
	 * Data array
	 *
	 * Used to transfer data to the templates.
	 */
	public $Data = ['Page' => []];

	/*
	 * Hooks array
	 *
	 * Key: index
	 * Value: [hook_name, callable callback]
	 */
	private $Hooks = [];
	/*
	 * Templates array
	 *
	 * Key: name
	 * Value: file_name
	 */
	private $Templates = [];
	public $LangName_Default = "en";

	public $LangName;
	public $LangInfo = [];
	public $Lang = [];

	function __construct(){
		if(isset($_COOKIE['server-sys-weblang']) && $this->valid_lang($_COOKIE['server-sys-weblang'])){
			$this->LangName = $_COOKIE['server-sys-weblang'];
		}else
			$this->LangName = $this->LangName_Default;

		$this->load_language($this->LangName);
	}

	/*
	 * Checks if a template is loaded into our system by name.
	 *
	 * @param name			Name of the template.
	 */
	public function template_loaded($name){
		return isset($this->Templates[$name]);
	}

	/*
	 * Checks if a template is loaded into our system by file.
	 *
	 * @param file			File name of the template.
	 */
	public function template_file_loaded($file){
		return in_array($file, $this->Templates);
	}

	/*
	 * Registers a template for later loading.
	 *
	 * @param
	 */
	public function register_template($name, $file){
		if($this->template_loaded($name))
			return false;

		if(!file_exists($file))
			return false;

		$this->Templates[$name] = $file;
		return true;
	}

	/*
	 * Loads a template (by requiring). You should
	 * have typically filled out some variables to
	 * use in your template first.
	 *
	 * @param name		Name of the template to load.
	 */
	public function load_template($name){
		global $Sys;

		if(!isset($this->Templates[$name]))
			return false;


		return require_once $this->Templates[$name];
	}

	/*
	 * Checks if a hook exists without calling it.
	 *
	 * @param name			Name of the hook.
	 * @return				Number of hooks if any, or false if none.
	 */
	public function hook_exists($hook){
		$count = 0;
		foreach($this->Hooks as $name => $func){
			if($hook == $name)
				$count++;
		}

		return (($count > 0) ? $count : false);
	}

	/*
	 * Registers a hook. Multiple of same name are fine.
	 *
	 * @param hook			Name of the hook.
	 * @param callback		Callable callback function.
	 */
	public function register_hook($hook, callable $callback){
		$this->Hooks[] = [$hook, $callback];
	}

	/*
	 * Calls a hook by name.
	 *
	 * @param hook			Name of the hook.
	 * @param params		Params to send to the callback.
	 */
	public function call_hook($hook, $params = null){
		foreach($this->Hooks as $row){
			if(in_array($hook, $row)){
				foreach($row as $item){
					if(is_callable($item))
						call_user_func($item, $params);
				}
			}
		}
	}

	/*
	 * Checks if a language is valid by name.
	 *
	 * @param name			Name of language (ex: en)
	 * @return 				True if valid, false elsewise.
	 */
	public function valid_lang($name){
		return file_exists(__DIR__ . '/../sys-language/' . $name . '.php');
	}
	/*
	 * Loads a language by name. This requires the core
	 * <language>.php file from sys-language/ and also
	 * sys-language/<language>/*.lang.php (in that order).
	 *
	 * <language>.php is meant to fill out $langinfo
	 * see en.php for examples.
	 *
	 * <language>/*.lang.php is meant to add to $l
	 * see en/common.lang.php for examples.
	 *
	 * @param name			Language name (ex: en)
	 */
	public function load_language($name){
		$langinfo = [];
		require_once __DIR__ . '/../sys-language/' . $name . '.php';
		$this->LangInfo = $langinfo;

		$l = [];

		foreach(glob(__DIR__ . '/../sys-language/' . $name . '/*.lang.php') as $file){
			require_once $file;
		}

		$this->Lang = $l;
	}
}
