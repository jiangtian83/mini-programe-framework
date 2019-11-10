<?php  namespace think\log\driver;
class File 
{
	protected $config = NULL;
	protected $writed = array( );
	public function __construct($config = array( )) 
	{
		if( is_array($config) ) 
		{
			$this->config = array_merge($this->config, $config);
		}
	}
	public function save(array $log = array( )) 
	{
		if( $this->config["single"] ) 
		{
			$destination = $this->config["path"] . "single.log";
		}
		else 
		{
			$cli = (IS_CLI ? "_cli" : "");
			if( $this->config["max_files"] ) 
			{
				$filename = date("Ymd") . $cli . ".log";
				$files = glob($this->config["path"] . "*.log");
				if( $this->config["max_files"] < count($files) ) 
				{
					unlink($files[0]);
				}
			}
			else 
			{
				$filename = date("Ym") . "/" . date("d") . $cli . ".log";
			}
			$destination = $this->config["path"] . $filename;
		}
		$path = dirname($destination);
		!is_dir($path) and mkdir($path, 493, true);
		$info = "";
		foreach( $log as $type => $val ) 
		{
			$level = "";
			foreach( $val as $msg ) 
			{
				if( !is_string($msg) ) 
				{
					$msg = var_export($msg, true);
				}
				$level .= "[ " . $type . " ] " . $msg . "\r\n";
			}
			if( in_array($type, $this->config["apart_level"]) ) 
			{
				if( $this->config["single"] ) 
				{
					$filename = $path . DS . $type . ".log";
				}
				else 
				{
					if( $this->config["max_files"] ) 
					{
						$filename = $path . DS . date("Ymd") . "_" . $type . $cli . ".log";
					}
					else 
					{
						$filename = $path . DS . date("d") . "_" . $type . $cli . ".log";
					}
				}
				$this->write($level, $filename, true);
			}
			else 
			{
				$info .= $level;
			}
		}
		if( $info ) 
		{
			return $this->write($info, $destination);
		}
		return true;
	}
	protected function write($message, $destination, $apart = false) 
	{
		if( is_file($destination) && floor($this->config["file_size"]) <= filesize($destination) ) 
		{
			try 
			{
				rename($destination, dirname($destination) . DS . time() . "-" . basename($destination));
			}
			catch( \Exception $e ) 
			{
			}
			$this->writed[$destination] = false;
		}
		if( empty($this->writed[$destination]) && !IS_CLI ) 
		{
			if( \think\App::$debug && !$apart ) 
			{
				if( isset($_SERVER["HTTP_HOST"]) ) 
				{
					$current_uri = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
				}
				else 
				{
					$current_uri = "cmd:" . implode(" ", $_SERVER["argv"]);
				}
				$runtime = round(microtime(true) - THINK_START_TIME, 10);
				$reqs = (0 < $runtime ? number_format(1 / $runtime, 2) : "∞");
				$time_str = " [运行时间：" . number_format($runtime, 6) . "s][吞吐率：" . $reqs . "req/s]";
				$memory_use = number_format((memory_get_usage() - THINK_START_MEM) / 1024, 2);
				$memory_str = " [内存消耗：" . $memory_use . "kb]";
				$file_load = " [文件加载：" . count(get_included_files()) . "]";
				$message = "[ info ] " . $current_uri . $time_str . $memory_str . $file_load . "\r\n" . $message;
			}
			$now = date($this->config["time_format"]);
			$ip = \think\Request::instance()->ip();
			$method = (isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : "CLI");
			$uri = (isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : "");
			$message = "---------------------------------------------------------------\r\n[" . $now . "] " . $ip . " " . $method . " " . $uri . "\r\n" . $message;
			$this->writed[$destination] = true;
		}
		if( IS_CLI ) 
		{
			$now = date($this->config["time_format"]);
			$message = "[" . $now . "]" . $message;
		}
		return error_log($message, 3, $destination);
	}
}
?>