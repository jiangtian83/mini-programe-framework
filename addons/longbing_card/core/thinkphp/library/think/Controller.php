<?php  namespace think;
Loader::import("controller/Jump", TRAIT_PATH, EXT);
class Controller 
{
	use \traits\controller\Jump;
	protected $view = NULL;
	protected $request = NULL;
	protected $failException = false;
	protected $batchValidate = false;
	protected $beforeActionList = array( );
	public function __construct(Request $request = NULL) 
	{
		$this->view = View::instance(Config::get("template"), Config::get("view_replace_str"));
		$this->request = (is_null($request) ? Request::instance() : $request);
		$this->_initialize();
		if( $this->beforeActionList ) 
		{
			foreach( $this->beforeActionList as $method => $options ) 
			{
				is_numeric($method);
				(is_numeric($method) ? $this->beforeAction($options) : $this->beforeAction($method, $options));
			}
		}
	}
	protected function _initialize() 
	{
	}
	protected function beforeAction($method, $options = array( )) 
	{
		if( isset($options["only"]) ) 
		{
			if( is_string($options["only"]) ) 
			{
				$options["only"] = explode(",", $options["only"]);
			}
			if( !in_array($this->request->action(), $options["only"]) ) 
			{
				return NULL;
			}
		}
		else 
		{
			if( isset($options["except"]) ) 
			{
				if( is_string($options["except"]) ) 
				{
					$options["except"] = explode(",", $options["except"]);
				}
				if( in_array($this->request->action(), $options["except"]) ) 
				{
					return NULL;
				}
			}
		}
		call_user_func(array( $this, $method ));
	}
	protected function fetch($template = "", $vars = array( ), $replace = array( ), $config = array( )) 
	{
		return $this->view->fetch($template, $vars, $replace, $config);
	}
	protected function display($content = "", $vars = array( ), $replace = array( ), $config = array( )) 
	{
		return $this->view->display($content, $vars, $replace, $config);
	}
	protected function assign($name, $value = "") 
	{
		$this->view->assign($name, $value);
		return $this;
	}
	protected function engine($engine) 
	{
		$this->view->engine($engine);
		return $this;
	}
	protected function validateFailException($fail = true) 
	{
		$this->failException = $fail;
		return $this;
	}
	protected function validate($data, $validate, $message = array( ), $batch = false, $callback = NULL) 
	{
		if( is_array($validate) ) 
		{
			$v = Loader::validate();
			$v->rule($validate);
		}
		else 
		{
			if( strpos($validate, ".") ) 
			{
				list($validate, $scene) = explode(".", $validate);
			}
			$v = Loader::validate($validate);
			!empty($scene) and $v->scene($scene);
		}
		if( $batch || $this->batchValidate ) 
		{
			$v->batch(true);
		}
		if( is_array($message) ) 
		{
			$v->message($message);
		}
		if( $callback && is_callable($callback) ) 
		{
			call_user_func_array($callback, array( $v, $data ));
		}
		if( !$v->check($data) ) 
		{
			if( $this->failException ) 
			{
				throw new exception\ValidateException($v->getError());
			}
			return $v->getError();
		}
		return true;
	}
}
?>