<?php  namespace think\console\command;
class Clear extends \think\console\Command 
{
	protected function configure() 
	{
		$this->setName("clear")->addArgument("type", \think\console\input\Argument::OPTIONAL, "type to clear", null)->addOption("path", "d", \think\console\input\Option::VALUE_OPTIONAL, "path to clear", null)->setDescription("Clear runtime file");
	}
	protected function execute(\think\console\Input $input, \think\console\Output $output) 
	{
		$path = ($input->getOption("path") ?: RUNTIME_PATH);
		$type = $input->getArgument("type");
		if( $type == "route" ) 
		{
			\think\Cache::clear("route_check");
		}
		else 
		{
			if( is_dir($path) ) 
			{
				$this->clearPath($path);
			}
		}
		$output->writeln("<info>Clear Successed</info>");
	}
	protected function clearPath($path) 
	{
		$path = realpath($path) . DS;
		$files = scandir($path);
		if( $files ) 
		{
			foreach( $files as $file ) 
			{
				if( "." != $file && ".." != $file && is_dir($path . $file) ) 
				{
					$this->clearPath($path . $file);
				}
				else 
				{
					if( ".gitignore" != $file && is_file($path . $file) ) 
					{
						unlink($path . $file);
					}
				}
			}
		}
	}
}
?>