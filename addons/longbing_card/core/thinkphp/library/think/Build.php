<?php  namespace think;
class Build 
{
	public static function run(array $build = array( ), $namespace = "app", $suffix = false) 
	{
		$lock = APP_PATH . "build.lock";
		if( !is_writable($lock) ) 
		{
			if( !touch($lock) ) 
			{
				throw new Exception("应用目录[" . APP_PATH . "]不可写，目录无法自动生成！<BR>请手动生成项目目录~", 10006);
			}
			foreach( $build as $module => $list ) 
			{
				if( "__dir__" == $module ) 
				{
					self::buildDir($list);
				}
				else 
				{
					if( "__file__" == $module ) 
					{
						self::buildFile($list);
					}
					else 
					{
						self::module($module, $list, $namespace, $suffix);
					}
				}
			}
			unlink($lock);
		}
	}
	protected static function buildDir($list) 
	{
		foreach( $list as $dir ) 
		{
			!is_dir(APP_PATH . $dir) and mkdir(APP_PATH . $dir, 493, true);
		}
	}
	protected static function buildFile($list) 
	{
		foreach( $list as $file ) 
		{
			if( !is_dir(APP_PATH . dirname($file)) ) 
			{
				mkdir(APP_PATH . dirname($file), 493, true);
			}
			if( !is_file(APP_PATH . $file) ) 
			{
				file_put_contents(APP_PATH . $file, ("php" == pathinfo($file, PATHINFO_EXTENSION) ? "<?php\n" : ""));
			}
		}
	}
	public static function module($module = "", $list = array( ), $namespace = "app", $suffix = false) 
	{
		$module = ($module ?: "");
		!is_dir(APP_PATH . $module) and mkdir(APP_PATH . $module);
		if( basename(RUNTIME_PATH) != $module ) 
		{
			self::buildCommon($module);
			self::buildHello($module, $namespace, $suffix);
		}
		if( empty($list) ) 
		{
			$list = array( "__file__" => array( "config.php", "common.php" ), "__dir__" => array( "controller", "model", "view" ) );
		}
		foreach( $list as $path => $file ) 
		{
			$modulePath = APP_PATH . $module . DS;
			if( "__dir__" == $path ) 
			{
				foreach( $file as $dir ) 
				{
					self::checkDirBuild($modulePath . $dir);
				}
			}
			else 
			{
				if( "__file__" == $path ) 
				{
					foreach( $file as $name ) 
					{
						if( !is_file($modulePath . $name) ) 
						{
							file_put_contents($modulePath . $name, ("php" == pathinfo($name, PATHINFO_EXTENSION) ? "<?php\n" : ""));
						}
					}
				}
				else 
				{
					foreach( $file as $val ) 
					{
						$val = trim($val);
						$filename = $modulePath . $path . DS . $val . (($suffix ? ucfirst($path) : "")) . EXT;
						$space = $namespace . "\\" . (($module ? $module . "\\" : "")) . $path;
						$class = $val . (($suffix ? ucfirst($path) : ""));
						switch( $path ) 
						{
							case "controller": $content = "<?php\nnamespace " . $space . ";\n\nclass " . $class . "\n{\n\n}";
							break;
							case "model": $content = "<?php\nnamespace " . $space . ";\n\nuse think\\Model;\n\nclass " . $class . " extends Model\n{\n\n}";
							break;
							case "view": $filename = $modulePath . $path . DS . $val . ".html";
							self::checkDirBuild(dirname($filename));
							$content = "";
							break;
							default: $content = "<?php\nnamespace " . $space . ";\n\nclass " . $class . "\n{\n\n}";
						}
						if( !is_file($filename) ) 
						{
							file_put_contents($filename, $content);
						}
					}
				}
			}
		}
	}
	protected static function buildHello($module, $namespace, $suffix = false) 
	{
		$filename = APP_PATH . (($module ? $module . DS : "")) . "controller" . DS . "Index" . (($suffix ? "Controller" : "")) . EXT;
		if( !is_file($filename) ) 
		{
			$module = ($module ? $module . "\\" : "");
			$suffix = ($suffix ? "Controller" : "");
			$content = str_replace(array( "{\$app}", "{\$module}", "{layer}", "{\$suffix}" ), array( $namespace, $module, "controller", $suffix ), file_get_contents(THINK_PATH . "tpl" . DS . "default_index.tpl"));
			self::checkDirBuild(dirname($filename));
			file_put_contents($filename, $content);
		}
	}
	protected static function buildCommon($module) 
	{
		$config = CONF_PATH . (($module ? $module . DS : "")) . "config.php";
		self::checkDirBuild(dirname($config));
		if( !is_file($config) ) 
		{
			file_put_contents($config, "<?php\n//配置文件\nreturn [\n\n];");
		}
		$common = APP_PATH . (($module ? $module . DS : "")) . "common.php";
		if( !is_file($common) ) 
		{
			file_put_contents($common, "<?php\n");
		}
	}
	protected static function checkDirBuild($dirname) 
	{
		!is_dir($dirname) and mkdir($dirname, 493, true);
	}
}
?>