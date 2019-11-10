<?php  namespace think\console\command\optimize;
class Config extends \think\console\Command 
{
	protected $output = NULL;
	protected function configure() 
	{
		$this->setName("optimize:config")->addArgument("module", \think\console\input\Argument::OPTIONAL, "Build module config cache .")->setDescription("Build config and common file cache.");
	}
	protected function execute(\think\console\Input $input, \think\console\Output $output) 
	{
		if( $input->getArgument("module") ) 
		{
			$module = $input->getArgument("module") . DS;
		}
		else 
		{
			$module = "";
		}
		$content = "<?php " . PHP_EOL . $this->buildCacheContent($module);
		if( !is_dir(RUNTIME_PATH . $module) ) 
		{
			@mkdir(RUNTIME_PATH . $module, 493, true);
		}
		file_put_contents(RUNTIME_PATH . $module . "init" . EXT, $content);
		$output->writeln("<info>Succeed!</info>");
	}
	protected function buildCacheContent($module) 
	{
		$content = "";
		$path = realpath(APP_PATH . $module) . DS;
		if( $module ) 
		{
			$config = \think\Config::load(CONF_PATH . $module . "config" . CONF_EXT);
			$filename = CONF_PATH . $module . "database" . CONF_EXT;
			\think\Config::load($filename, "database");
			if( $config["app_status"] ) 
			{
				$config = \think\Config::load(CONF_PATH . $module . $config["app_status"] . CONF_EXT);
			}
			if( is_dir(CONF_PATH . $module . "extra") ) 
			{
				$dir = CONF_PATH . $module . "extra";
				$files = scandir($dir);
				foreach( $files as $file ) 
				{
					if( strpos($file, CONF_EXT) ) 
					{
						$filename = $dir . DS . $file;
						\think\Config::load($filename, pathinfo($file, PATHINFO_FILENAME));
					}
				}
			}
		}
		if( is_file(CONF_PATH . $module . "tags" . EXT) ) 
		{
			$content .= "\\think\\Hook::import(" . var_export(include(CONF_PATH . $module . "tags" . EXT), true) . ");" . PHP_EOL;
		}
		if( is_file($path . "common" . EXT) ) 
		{
			$content .= substr(php_strip_whitespace($path . "common" . EXT), 5) . PHP_EOL;
		}
		$content .= "\\think\\Config::set(" . var_export(\think\Config::get(), true) . ");";
		return $content;
	}
}
?>