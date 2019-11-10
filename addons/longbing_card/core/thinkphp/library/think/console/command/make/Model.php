<?php  namespace think\console\command\make;
class Model extends \think\console\command\Make 
{
	protected $type = "Model";
	protected function configure() 
	{
		parent::configure();
		$this->setName("make:model")->setDescription("Create a new model class");
	}
	protected function getStub() 
	{
		return __DIR__ . "/stubs/model.stub";
	}
	protected function getNamespace($appNamespace, $module) 
	{
		return parent::getNamespace($appNamespace, $module) . "\\model";
	}
}
?>