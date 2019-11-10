<?php  namespace Guzzle\Service\Builder;
class ServiceBuilder extends \Guzzle\Common\AbstractHasDispatcher implements ServiceBuilderInterface, \ArrayAccess, \Serializable 
{
	protected $builderConfig = array( );
	protected $clients = array( );
	protected static $cachedFactory = NULL;
	protected $plugins = array( );
	public static function factory($config = NULL, array $globalParameters = array( )) 
	{
		if( !static::$cachedFactory ) 
		{
			static::$cachedFactory = new ServiceBuilderLoader();
		}
		return self::$cachedFactory->load($config, $globalParameters);
	}
	public function __construct(array $serviceBuilderConfig = array( )) 
	{
		$this->builderConfig = $serviceBuilderConfig;
	}
	public static function getAllEvents() 
	{
		return array( "service_builder.create_client" );
	}
	public function unserialize($serialized) 
	{
		$this->builderConfig = json_decode($serialized, true);
	}
	public function serialize() 
	{
		return json_encode($this->builderConfig);
	}
	public function addGlobalPlugin(\Symfony\Component\EventDispatcher\EventSubscriberInterface $plugin) 
	{
		$this->plugins[] = $plugin;
		return $this;
	}
	public function getData($name) 
	{
		return (isset($this->builderConfig[$name]) ? $this->builderConfig[$name] : null);
	}
	public function get($name, $throwAway = false) 
	{
		if( !isset($this->builderConfig[$name]) ) 
		{
			if( isset($this->clients[$name]) ) 
			{
				return $this->clients[$name];
			}
			foreach( $this->builderConfig as $actualName => $config ) 
			{
				if( isset($config["alias"]) && $config["alias"] == $name ) 
				{
					return $this->get($actualName, $throwAway);
				}
			}
			throw new \Guzzle\Service\Exception\ServiceNotFoundException("No service is registered as " . $name);
		}
		else 
		{
			if( !$throwAway && isset($this->clients[$name]) ) 
			{
				return $this->clients[$name];
			}
			$builder =& $this->builderConfig[$name];
			foreach( $builder["params"] as &$v ) 
			{
				if( is_string($v) && substr($v, 0, 1) == "{" && substr($v, -1) == "}" ) 
				{
					$v = $this->get(trim($v, "{} "));
				}
			}
			$config = $builder["params"];
			if( is_array($throwAway) ) 
			{
				$config = $throwAway + $config;
			}
			$client = $builder["class"]::factory($config);
			if( !$throwAway ) 
			{
				$this->clients[$name] = $client;
			}
			if( $client instanceof \Guzzle\Service\ClientInterface ) 
			{
				foreach( $this->plugins as $plugin ) 
				{
					$client->addSubscriber($plugin);
				}
				$this->dispatch("service_builder.create_client", array( "client" => $client ));
			}
			return $client;
		}
	}
	public function set($key, $service) 
	{
		if( is_array($service) && isset($service["class"]) && isset($service["params"]) ) 
		{
			$this->builderConfig[$key] = $service;
		}
		else 
		{
			$this->clients[$key] = $service;
		}
		return $this;
	}
	public function offsetSet($offset, $value) 
	{
		$this->set($offset, $value);
	}
	public function offsetUnset($offset) 
	{
		unset($this->builderConfig[$offset]);
		unset($this->clients[$offset]);
	}
	public function offsetExists($offset) 
	{
		return isset($this->builderConfig[$offset]) || isset($this->clients[$offset]);
	}
	public function offsetGet($offset) 
	{
		return $this->get($offset);
	}
}
?>