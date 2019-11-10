<?php  namespace Guzzle\Service;
abstract class AbstractConfigLoader implements ConfigLoaderInterface 
{
	protected $aliases = array( );
	protected $loadedFiles = array( );
	protected static $jsonErrors = NULL;
	public function load($config, array $options = array( )) 
	{
		$this->loadedFiles = array( );
		if( is_string($config) ) 
		{
			$config = $this->loadFile($config);
		}
		else 
		{
			if( !is_array($config) ) 
			{
				throw new \Guzzle\Common\Exception\InvalidArgumentException("Unknown type passed to configuration loader: " . gettype($config));
			}
			$this->mergeIncludes($config);
		}
		return $this->build($config, $options);
	}
	public function addAlias($filename, $alias) 
	{
		$this->aliases[$filename] = $alias;
		return $this;
	}
	public function removeAlias($alias) 
	{
		unset($this->aliases[$alias]);
		return $this;
	}
	abstract protected function build($config, array $options);
	protected function loadFile($filename) 
	{
		if( isset($this->aliases[$filename]) ) 
		{
			$filename = $this->aliases[$filename];
		}
		switch( pathinfo($filename, PATHINFO_EXTENSION) ) 
		{
			case "js": case "json": $level = error_reporting(0);
			$json = file_get_contents($filename);
			error_reporting($level);
			if( $json === false ) 
			{
				$err = error_get_last();
				throw new \Guzzle\Common\Exception\InvalidArgumentException("Unable to open " . $filename . ": " . $err["message"]);
			}
			$config = json_decode($json, true);
			if( $error = json_last_error() ) 
			{
				$message = (isset(self::$jsonErrors[$error]) ? self::$jsonErrors[$error] : "Unknown error");
				throw new \Guzzle\Common\Exception\RuntimeException("Error loading JSON data from " . $filename . ": (" . $error . ") - " . $message);
			}
			break;
			case "php": if( !is_readable($filename) ) 
			{
				throw new \Guzzle\Common\Exception\InvalidArgumentException("Unable to open " . $filename . " for reading");
			}
			$config = require($filename);
			if( !is_array($config) ) 
			{
				throw new \Guzzle\Common\Exception\InvalidArgumentException("PHP files must return an array of configuration data");
			}
			break;
			default: throw new \Guzzle\Common\Exception\InvalidArgumentException("Unknown file extension: " . $filename);
		}
		$this->loadedFiles[$filename] = true;
		$this->mergeIncludes($config, dirname($filename));
		return $config;
	}
	protected function mergeIncludes(&$config, $basePath = NULL) 
	{
		if( !empty($config["includes"]) ) 
		{
			foreach( $config["includes"] as &$path ) 
			{
				if( $path[0] != DIRECTORY_SEPARATOR && !isset($this->aliases[$path]) && $basePath ) 
				{
					$path = (string) $basePath . "/" . $path;
				}
				if( !isset($this->loadedFiles[$path]) ) 
				{
					$this->loadedFiles[$path] = true;
					$config = $this->mergeData($this->loadFile($path), $config);
				}
			}
		}
	}
	protected function mergeData(array $a, array $b) 
	{
		return array_merge_recursive($a, $b);
	}
}
?>