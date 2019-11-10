<?php  namespace think\console\command\make;
class Controller extends \think\console\command\Make 
{
	protected $type = "Controller";
	protected function configure() 
	{
		parent::configure();
		$this->setName("make:controller")->addOption("plain", null, \think\console\input\Option::VALUE_NONE, "Generate an empty controller class.")->setDescription("Create a new resource controller class");
	}
	protected function getStub() 
	{
		if( $this->input->getOption("plain") ) 
		{
			return __DIR__ . "/stubs/controller.plain.stub";
		}
		return __DIR__ . "/stubs/controller.stub";
	}
	protected function getClassName($name) 
	{
		return parent::getClassName($name) . ((\think\Config::get("controller_suffix") ? ucfirst(\think\Config::get("url_controller_layer")) : ""));
	}
	protected function getNamespace($appNamespace, $module) 
	{
		return parent::getNamespace($appNamespace, $module) . "\\controller";
	}
}
?>