<?php  namespace think\console\command;
class Build extends \think\console\Command 
{
	protected function configure() 
	{
		$this->setName("build")->setDefinition(array( new \think\console\input\Option("config", null, \think\console\input\Option::VALUE_OPTIONAL, "build.php path"), new \think\console\input\Option("module", null, \think\console\input\Option::VALUE_OPTIONAL, "module name") ))->setDescription("Build Application Dirs");
	}
	protected function execute(\think\console\Input $input, \think\console\Output $output) 
	{
		if( $input->hasOption("module") ) 
		{
			\think\Build::module($input->getOption("module"));
			$output->writeln("Successed");
		}
		else 
		{
			if( $input->hasOption("config") ) 
			{
				$build = include($input->getOption("config"));
			}
			else 
			{
				$build = include(APP_PATH . "build.php");
			}
			if( empty($build) ) 
			{
				$output->writeln("Build Config Is Empty");
			}
			else 
			{
				\think\Build::run($build);
				$output->writeln("Successed");
			}
		}
	}
}
?>