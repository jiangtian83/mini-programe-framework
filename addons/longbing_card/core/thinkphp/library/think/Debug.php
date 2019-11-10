<?php  namespace think;
class Debug 
{
	protected static $info = array( );
	protected static $mem = array( );
	public static function remark($name, $value = "") 
	{
		self::$info[$name] = (is_float($value) ? $value : microtime(true));
		if( "time" != $value ) 
		{
			self::$mem["mem"][$name] = (is_float($value) ? $value : memory_get_usage());
			self::$mem["peak"][$name] = memory_get_peak_usage();
		}
	}
	public static function getRangeTime($start, $end, $dec = 6) 
	{
		if( !isset(self::$info[$end]) ) 
		{
			self::$info[$end] = microtime(true);
		}
		return number_format(self::$info[$end] - self::$info[$start], $dec);
	}
	public static function getUseTime($dec = 6) 
	{
		return number_format(microtime(true) - THINK_START_TIME, $dec);
	}
	public static function getThroughputRate() 
	{
		return number_format(1 / self::getUseTime(), 2) . "req/s";
	}
	public static function getRangeMem($start, $end, $dec = 2) 
	{
		if( !isset(self::$mem["mem"][$end]) ) 
		{
			self::$mem["mem"][$end] = memory_get_usage();
		}
		$size = self::$mem["mem"][$end] - self::$mem["mem"][$start];
		$a = array( "B", "KB", "MB", "GB", "TB" );
		for( $pos = 0; 1024 <= $size; $pos++ ) 
		{
			$size /= 1024;
		}
		return round($size, $dec) . " " . $a[$pos];
	}
	public static function getUseMem($dec = 2) 
	{
		$size = memory_get_usage() - THINK_START_MEM;
		$a = array( "B", "KB", "MB", "GB", "TB" );
		for( $pos = 0; 1024 <= $size; $pos++ ) 
		{
			$size /= 1024;
		}
		return round($size, $dec) . " " . $a[$pos];
	}
	public static function getMemPeak($start, $end, $dec = 2) 
	{
		if( !isset(self::$mem["peak"][$end]) ) 
		{
			self::$mem["peak"][$end] = memory_get_peak_usage();
		}
		$size = self::$mem["peak"][$end] - self::$mem["peak"][$start];
		$a = array( "B", "KB", "MB", "GB", "TB" );
		for( $pos = 0; 1024 <= $size; $pos++ ) 
		{
			$size /= 1024;
		}
		return round($size, $dec) . " " . $a[$pos];
	}
	public static function getFile($detail = false) 
	{
		$files = get_included_files();
		if( $detail ) 
		{
			$info = array( );
			foreach( $files as $file ) 
			{
				$info[] = $file . " ( " . number_format(filesize($file) / 1024, 2) . " KB )";
			}
			return $info;
		}
		else 
		{
			return count($files);
		}
	}
	public static function dump($var, $echo = true, $label = NULL, $flags = ENT_SUBSTITUTE) 
	{
		$label = (null === $label ? "" : rtrim($label) . ":");
		ob_start();
		var_dump($var);
		$output = preg_replace("/\\]\\=\\>\\n(\\s+)/m", "] => ", ob_get_clean());
		if( IS_CLI ) 
		{
			$output = PHP_EOL . $label . $output . PHP_EOL;
		}
		else 
		{
			if( !extension_loaded("xdebug") ) 
			{
				$output = htmlspecialchars($output, $flags);
			}
			$output = "<pre>" . $label . $output . "</pre>";
		}
		if( $echo ) 
		{
			echo $output;
		}
		else 
		{
			return $output;
		}
	}
	public static function inject(Response $response, &$content) 
	{
		$config = Config::get("trace");
		$type = (isset($config["type"]) ? $config["type"] : "Html");
		$class = (false !== strpos($type, "\\") ? $type : "\\think\\debug\\" . ucwords($type));
		unset($config["type"]);
		if( !class_exists($class) ) 
		{
			throw new exception\ClassNotFoundException("class not exists:" . $class, $class);
		}
		$trace = new $class($config);
		if( $response instanceof response\Redirect ) 
		{
		}
		else 
		{
			$output = $trace->output($response, Log::getLog());
			if( is_string($output) ) 
			{
				$pos = strripos($content, "</body>");
				if( false !== $pos ) 
				{
					$content = substr($content, 0, $pos) . $output . substr($content, $pos);
				}
				else 
				{
					$content = $content . $output;
				}
			}
		}
	}
}
?>