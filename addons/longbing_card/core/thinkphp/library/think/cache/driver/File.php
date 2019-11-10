<?php  namespace think\cache\driver;
class File extends \think\cache\Driver 
{
	protected $options = NULL;
	protected $expire = NULL;
	public function __construct($options = array( )) 
	{
		if( !empty($options) ) 
		{
			$this->options = array_merge($this->options, $options);
		}
		if( substr($this->options["path"], -1) != DS ) 
		{
			$this->options["path"] .= DS;
		}
		$this->init();
	}
	private function init() 
	{
		if( !is_dir($this->options["path"]) && mkdir($this->options["path"], 493, true) ) 
		{
			return true;
		}
		return false;
	}
	protected function getCacheKey($name, $auto = false) 
	{
		$name = md5($name);
		if( $this->options["cache_subdir"] ) 
		{
			$name = substr($name, 0, 2) . DS . substr($name, 2);
		}
		if( $this->options["prefix"] ) 
		{
			$name = $this->options["prefix"] . DS . $name;
		}
		$filename = $this->options["path"] . $name . ".php";
		$dir = dirname($filename);
		if( $auto && !is_dir($dir) ) 
		{
			mkdir($dir, 493, true);
		}
		return $filename;
	}
	public function has($name) 
	{
		return ($this->get($name) ? true : false);
	}
	public function get($name, $default = false) 
	{
		$filename = $this->getCacheKey($name);
		if( !is_file($filename) ) 
		{
			return $default;
		}
		$content = file_get_contents($filename);
		$this->expire = null;
		if( false !== $content ) 
		{
			$expire = (int) substr($content, 8, 12);
			if( 0 != $expire && filemtime($filename) + $expire < time() ) 
			{
				return $default;
			}
			$this->expire = $expire;
			$content = substr($content, 32);
			if( $this->options["data_compress"] && function_exists("gzcompress") ) 
			{
				$content = gzuncompress($content);
			}
			$content = unserialize($content);
			return $content;
		}
		return $default;
	}
	public function set($name, $value, $expire = NULL) 
	{
		if( is_null($expire) ) 
		{
			$expire = $this->options["expire"];
		}
		if( $expire instanceof \DateTime ) 
		{
			$expire = $expire->getTimestamp() - time();
		}
		$filename = $this->getCacheKey($name, true);
		if( $this->tag && !is_file($filename) ) 
		{
			$first = true;
		}
		$data = serialize($value);
		if( $this->options["data_compress"] && function_exists("gzcompress") ) 
		{
			$data = gzcompress($data, 3);
		}
		$data = "<?php\n//" . sprintf("%012d", $expire) . "\n exit();?>\n" . $data;
		$result = file_put_contents($filename, $data);
		if( $result ) 
		{
			isset($first) and $this->setTagItem($filename);
			clearstatcache();
			return true;
		}
		return false;
	}
	public function inc($name, $step = 1) 
	{
		if( $this->has($name) ) 
		{
			$value = $this->get($name) + $step;
			$expire = $this->expire;
		}
		else 
		{
			$value = $step;
			$expire = 0;
		}
		return ($this->set($name, $value, $expire) ? $value : false);
	}
	public function dec($name, $step = 1) 
	{
		if( $this->has($name) ) 
		{
			$value = $this->get($name) - $step;
			$expire = $this->expire;
		}
		else 
		{
			$value = 0 - $step;
			$expire = 0;
		}
		return ($this->set($name, $value, $expire) ? $value : false);
	}
	public function rm($name) 
	{
		$filename = $this->getCacheKey($name);
		try 
		{
			return $this->unlink($filename);
		}
		catch( \Exception $e ) 
		{
		}
	}
	public function clear($tag = NULL) 
	{
		if( $tag ) 
		{
			$keys = $this->getTagItem($tag);
			foreach( $keys as $key ) 
			{
				$this->unlink($key);
			}
			$this->rm("tag_" . md5($tag));
			return true;
		}
		else 
		{
			$files = (array) glob($this->options["path"] . (($this->options["prefix"] ? $this->options["prefix"] . DS : "")) . "*");
			foreach( $files as $path ) 
			{
				if( is_dir($path) ) 
				{
					$matches = glob($path . "/*.php");
					if( is_array($matches) ) 
					{
						array_map("unlink", $matches);
					}
					rmdir($path);
				}
				else 
				{
					unlink($path);
				}
			}
			return true;
		}
	}
	private function unlink($path) 
	{
		return is_file($path) && unlink($path);
	}
}