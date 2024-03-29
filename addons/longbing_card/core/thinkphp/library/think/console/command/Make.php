<?php  namespace think\console\command;
abstract class Make extends \think\console\Command 
{
	protected $type = NULL;
	abstract protected function getStub();
	protected function configure() 
	{
		$this->addArgument("name", \think\console\input\Argument::REQUIRED, "The name of the class");
	}
	protected function execute(\think\console\Input $input, \think\console\Output $output) 
	{
		$name = trim($input->getArgument("name"));
		$classname = $this->getClassName($name);
		$pathname = $this->getPathName($classname);
		if( is_file($pathname) ) 
		{
			$output->writeln("<error>" . $this->type . " already exists!</error>");
			return false;
		}
		if( !is_dir(dirname($pathname)) ) 
		{
			mkdir(strtolower(dirname($pathname)), 493, true);
		}
		file_put_contents($pathname, $this->buildClass($classname));
		$output->writeln("<info>" . $this->type . " created successfully.</info>");
	}
	protected function buildClass($name) 
	{
		$stub = file_get_contents($this->getStub());
		$namespace = trim(implode("\\", array_slice(explode("\\", $name), 0, -1)), "\\");
		$class = str_replace($namespace . "\\", "", $name);
		return str_replace(array( "{%className%}", "{%namespace%}", "{%app_namespace%}" ), array( $class, $namespace, \think\App::$namespace ), $stub);
	}
	protected function getPathName($name) 
	{
		$name = str_replace(\think\App::$namespace . "\\", "", $name);
		return APP_PATH . str_replace("\\", "/", $name) . ".php";
	}
	protected function getClassName($name) 
	{
		$appNamespace = \think\App::$namespace;
		if( strpos($name, $appNamespace . "\\") === 0 ) 
		{
			return $name;
		}
		if( \think\Config::get("app_multi_module") ) 
		{
			if( strpos($name, "/") ) 
			{
				list($module, $name) = explode("/", $name, 2);
			}
			else 
			{
				$module = "common";
			}
		}
		else 
		{
			$module = null;
		}
		if( strpos($name, "/") !== false ) 
		{
			$name = str_replace("/", "\\", $name);
		}
		return $this->getNamespace($appNamespace, $module) . "\\" . $name;
	}
	protected function getNamespace($appNamespace, $module) 
	{
		return ($module ? $appNamespace . "\\" . $module : $appNamespace);
	}
}
?>