<?php  namespace Guzzle\Cache;
class CacheAdapterFactory implements \Guzzle\Common\FromConfigInterface 
{
	public static function fromCache($cache) 
	{
		if( !is_object($cache) ) 
		{
			throw new \Guzzle\Common\Exception\InvalidArgumentException("Cache must be one of the known cache objects");
		}
		if( $cache instanceof CacheAdapterInterface ) 
		{
			return $cache;
		}
		if( $cache instanceof \Doctrine\Common\Cache\Cache ) 
		{
			return new DoctrineCacheAdapter($cache);
		}
		if( $cache instanceof \Zend\Cache\Storage\StorageInterface ) 
		{
			return new Zf2CacheAdapter($cache);
		}
		throw new \Guzzle\Common\Exception\InvalidArgumentException("Unknown cache type: " . get_class($cache));
	}
	public static function factory($config = array( )) 
	{
		\Guzzle\Common\Version::warn("Guzzle\\Cache\\CacheAdapterFactory::factory" . " is deprecated");
		if( !is_array($config) ) 
		{
			throw new \Guzzle\Common\Exception\InvalidArgumentException("\$config must be an array");
		}
		if( !isset($config["cache.adapter"]) && !isset($config["cache.provider"]) ) 
		{
			$config["cache.adapter"] = "Guzzle\\Cache\\NullCacheAdapter";
			$config["cache.provider"] = null;
		}
		else 
		{
			foreach( array( "cache.adapter", "cache.provider" ) as $required ) 
			{
				if( !isset($config[$required]) ) 
				{
					throw new \Guzzle\Common\Exception\InvalidArgumentException((string) $required . " is a required CacheAdapterFactory option");
				}
				if( is_string($config[$required]) ) 
				{
					$config[$required] = str_replace(".", "\\", $config[$required]);
					if( !class_exists($config[$required]) ) 
					{
						throw new \Guzzle\Common\Exception\InvalidArgumentException((string) $config[$required] . " is not a valid class for " . $required);
					}
				}
			}
			if( is_string($config["cache.provider"]) ) 
			{
				$args = (isset($config["cache.provider.args"]) ? $config["cache.provider.args"] : null);
				$config["cache.provider"] = self::createObject($config["cache.provider"], $args);
			}
		}
		if( is_string($config["cache.adapter"]) ) 
		{
			$args = (isset($config["cache.adapter.args"]) ? $config["cache.adapter.args"] : array( ));
			array_unshift($args, $config["cache.provider"]);
			$config["cache.adapter"] = self::createObject($config["cache.adapter"], $args);
		}
		return $config["cache.adapter"];
	}
	private static function createObject($className, array $args = NULL) 
	{
		try 
		{
			if( !$args ) 
			{
				return new $className();
			}
			$c = new \ReflectionClass($className);
			return $c->newInstanceArgs($args);
		}
		catch( \Exception $e ) 
		{
			throw new \Guzzle\Common\Exception\RuntimeException($e->getMessage(), $e->getCode(), $e);
		}
	}
}
?>