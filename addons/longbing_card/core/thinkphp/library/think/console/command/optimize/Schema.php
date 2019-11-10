<?php  namespace think\console\command\optimize;
class Schema extends \think\console\Command 
{
	protected $output = NULL;
	protected function configure() 
	{
		$this->setName("optimize:schema")->addOption("config", null, \think\console\input\Option::VALUE_REQUIRED, "db config .")->addOption("db", null, \think\console\input\Option::VALUE_REQUIRED, "db name .")->addOption("table", null, \think\console\input\Option::VALUE_REQUIRED, "table name .")->addOption("module", null, \think\console\input\Option::VALUE_REQUIRED, "module name .")->setDescription("Build database schema cache.");
	}
	protected function execute(\think\console\Input $input, \think\console\Output $output) 
	{
		if( !is_dir(RUNTIME_PATH . "schema") ) 
		{
			@mkdir(RUNTIME_PATH . "schema", 493, true);
		}
		$config = array( );
		if( $input->hasOption("config") ) 
		{
			$config = $input->getOption("config");
		}
		if( $input->hasOption("module") ) 
		{
			$module = $input->getOption("module");
			$path = APP_PATH . $module . DS . "model";
			$list = (is_dir($path) ? scandir($path) : array( ));
			$app = \think\App::$namespace;
			foreach( $list as $file ) 
			{
				if( 0 === strpos($file, ".") ) 
				{
					continue;
				}
				$class = "\\" . $app . "\\" . $module . "\\model\\" . pathinfo($file, PATHINFO_FILENAME);
				$this->buildModelSchema($class);
			}
			$output->writeln("<info>Succeed!</info>");
			return NULL;
		}
		else 
		{
			if( $input->hasOption("table") ) 
			{
				$table = $input->getOption("table");
				if( !strpos($table, ".") ) 
				{
					$dbName = \think\Db::connect($config)->getConfig("database");
				}
				$tables[] = $table;
			}
			else 
			{
				if( $input->hasOption("db") ) 
				{
					$dbName = $input->getOption("db");
					$tables = \think\Db::connect($config)->getTables($dbName);
				}
				else 
				{
					if( !\think\Config::get("app_multi_module") ) 
					{
						$app = \think\App::$namespace;
						$path = APP_PATH . "model";
						$list = (is_dir($path) ? scandir($path) : array( ));
						foreach( $list as $file ) 
						{
							if( 0 === strpos($file, ".") ) 
							{
								continue;
							}
							$class = "\\" . $app . "\\model\\" . pathinfo($file, PATHINFO_FILENAME);
							$this->buildModelSchema($class);
						}
						$output->writeln("<info>Succeed!</info>");
						return NULL;
					}
					else 
					{
						$tables = \think\Db::connect($config)->getTables();
					}
				}
			}
			$db = (isset($dbName) ? $dbName . "." : "");
			$this->buildDataBaseSchema($tables, $db, $config);
			$output->writeln("<info>Succeed!</info>");
		}
	}
	protected function buildModelSchema($class) 
	{
		$reflect = new \ReflectionClass($class);
		if( !$reflect->isAbstract() && $reflect->isSubclassOf("\\think\\Model") ) 
		{
			$table = $class::getTable();
			$dbName = $class::getConfig("database");
			$content = "<?php " . PHP_EOL . "return ";
			$info = $class::getConnection()->getFields($table);
			$content .= var_export($info, true) . ";";
			file_put_contents(RUNTIME_PATH . "schema" . DS . $dbName . "." . $table . EXT, $content);
		}
	}
	protected function buildDataBaseSchema($tables, $db, $config) 
	{
		if( "" == $db ) 
		{
			$dbName = \think\Db::connect($config)->getConfig("database") . ".";
		}
		else 
		{
			$dbName = $db;
		}
		foreach( $tables as $table ) 
		{
			$content = "<?php " . PHP_EOL . "return ";
			$info = \think\Db::connect($config)->getFields($db . $table);
			$content .= var_export($info, true) . ";";
			file_put_contents(RUNTIME_PATH . "schema" . DS . $dbName . $table . EXT, $content);
		}
	}
}
?>