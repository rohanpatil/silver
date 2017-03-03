<?php
/**
 * Place this file in App/View/SmartyView.php
 *
 * Create or return a existing SMARTY instance
 *
 * requires Smarty 3.x
 *
 * @link http://www.smarty.net/docs/en/installing.smarty.extended.tpl
 */
class SmartySingleton {

	static private $instance = null;

	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}

	static public function instance() {
		if (is_null(self::$instance)) {
			
		    App::import( 'Vendor' , 'lib', array('file' => 'smarty'.DS.'libs'.DS. 'Smarty.class.php') );
  		 
		    $smarty = new Smarty;

		    $smarty->template_dir = APP . 'View' . DS;

			$smarty->compile_dir = TMP . 'smarty' . DS . 'compile' . DS;
			$smarty->cache_dir = TMP . 'smarty' . DS . 'cache' . DS;

			$smarty->plugins_dir = array(
				APP . 'Plugin' . DS . 'SmartyView' . DS . 'Lib' . DS . 'Plugins',
				VENDORS . 'smarty' . DS . 'smarty' . DS . 'distribution' . DS . 'libs' . DS . 'plugins'
			);

			$smarty->config_dir = APP . 'Plugin' . DS . 'SmartyView' . DS . 'Lib' . DS . 'Configs' . DS;
			$smarty->debug_tpl = APP . DS . 'Plugin' . DS . 'SmartyView' . DS . 'View' . DS . 'debug.tpl';

			// Other settings can be set here
			// $smarty->default_modifiers = array('escape:"html"');

			if (Configure::read('debug') == 2) {
				$smarty->debugging = true;
				$smarty->error_reporting = E_ALL & ~E_NOTICE;
				$smarty->compile_check = true;
				$smarty->force_compile = true;
			} else {
				$smarty->debugging = false;
				$smarty->error_reporting = null;
				$smarty->compile_check = false;
			}

			self::$instance = $smarty;
		}
		return self::$instance;
	}

}

class SmartyView extends View {

	public function __construct(\Controller $controller = null) {
		parent::__construct($controller);

		$this->Smarty = SmartySingleton::instance();
		$this->ext= '.tpl';
		$this->viewVars['params'] = $this->params;
	}

/**
 * Renders and returns output for given view filename with its
 * array of data (using smarty), also handles parent/extended views.
 *
 * @param string $viewFile Filename of the view
 * @param array $data Data to include in rendered view. If empty the current View::$viewVars will be used.
 * @return string Rendered output
 */

	protected function _render($viewFile, $data = array()) {
		$viewInfo = pathinfo($viewFile);

		if ($viewInfo['extension'] === 'ctp') {
			return parent::_render($viewFile, $data);
		}

		$this->_current = $viewFile;
		$initialBlocks = count($this->Blocks->unclosed());

		$eventManager = $this->getEventManager();
		$beforeEvent = new CakeEvent('View.beforeRenderFile', $this, array($viewFile));

		$eventManager->dispatch($beforeEvent);

		if (empty($data)) {
			$data = $this->viewVars;
		}

		foreach ($data as $key => $value) {
			if (!is_object($key)) {
				$this->Smarty->assign($key, $value);
			}
		}

		$helpers = HelperCollection::normalizeObjectArray($this->helpers);

		foreach ($helpers as $name => $properties) {
			list($plugin, $class) = pluginSplit($properties['class']);
			$this->{$class} = $this->Helpers->load($properties['class'], $properties['settings']);
			$this->Smarty->assignByRef(strtolower($name), $this->{$class});
		}

		$this->Smarty->assignByRef('this', $this);

		$content = $this->Smarty->fetch($viewFile);

		$afterEvent = new CakeEvent('View.afterRenderFile', $this, array($viewFile, $content));

		$afterEvent->modParams = 1;
		$eventManager->dispatch($afterEvent);
		$content = $afterEvent->data[1];

		if (isset($this->_parents[$viewFile])) {
			$this->_stack[] = $this->fetch('content');
			$this->assign('content', $content);

			$content = $this->_render($this->_parents[$viewFile]);
			$this->assign('content', array_pop($this->_stack));
		}

		$remainingBlocks = count($this->Blocks->unclosed());

		if ($initialBlocks !== $remainingBlocks) {
			throw new CakeException(__d('cake_dev', 'The "%s" block was left open. Blocks are not allowed to cross files.', $this->Blocks->active()));
		}

		return $content;
	}
}