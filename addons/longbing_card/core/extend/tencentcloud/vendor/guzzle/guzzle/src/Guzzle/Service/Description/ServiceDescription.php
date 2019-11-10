<?php  namespace Guzzle\Service\Description;
class ServiceDescription implements ServiceDescriptionInterface, \Guzzle\Common\ToArrayInterface 
{
	protected $operations = array( );
	protected $models = array( );
	protected $name = NULL;
	protected $apiVersion = NULL;
	protected $description = NULL;
	protected $extraData = array( );
	protected static $descriptionLoader = NULL;
	protected $baseUrl = NULL;
	public static function factory($config, array $options = array( )) 
	{
		if( !self::$descriptionLoader ) 
		{
			self::$descriptionLoader = new ServiceDescriptionLoader();
		}
		return self::$descriptionLoader->load($config, $options);
	}
	public function __construct(array $config = array( )) 
	{
		$this->fromArray($config);
	}
	public function serialize() 
	{
		return json_encode($this->toArray());
	}
	public function unserialize($json) 
	{
		$this->operations = array( );
		$this->fromArray(json_decode($json, true));
	}
	public function toArray() 
	{
		$result = array( "name" => $this->name, "apiVersion" => $this->apiVersion, "baseUrl" => $this->baseUrl, "description" => $this->description ) + $this->extraData;
		$result["operations"] = array( );
		foreach( $this->getOperations() as $name => $operation ) 
		{
			$result["operations"][($operation->getName() ?: $name)] = $operation->toArray();
		}
		if( !empty($this->models) ) 
		{
			$result["models"] = array( );
			foreach( $this->models as $id => $model ) 
			{
				$result["models"][$id] = ($model instanceof Parameter ? $model->toArray() : $model);
			}
		}
		return array_filter($result);
	}
	public function getBaseUrl() 
	{
		return $this->baseUrl;
	}
	public function setBaseUrl($baseUrl) 
	{
		$this->baseUrl = $baseUrl;
		return $this;
	}
	public function getOperations() 
	{
		foreach( array_keys($this->operations) as $name ) 
		{
			$this->getOperation($name);
		}
		return $this->operations;
	}
	public function hasOperation($name) 
	{
		return isset($this->operations[$name]);
	}
	public function getOperation($name) 
	{
		if( !isset($this->operations[$name]) ) 
		{
			return null;
		}
		if( !$this->operations[$name] instanceof Operation ) 
		{
			$this->operations[$name] = new Operation($this->operations[$name], $this);
		}
		return $this->operations[$name];
	}
	public function addOperation(OperationInterface $operation) 
	{
		$this->operations[$operation->getName()] = $operation->setServiceDescription($this);
		return $this;
	}
	public function getModel($id) 
	{
		if( !isset($this->models[$id]) ) 
		{
			return null;
		}
		if( !$this->models[$id] instanceof Parameter ) 
		{
			$this->models[$id] = new Parameter($this->models[$id] + array( "name" => $id ), $this);
		}
		return $this->models[$id];
	}
	public function getModels() 
	{
		foreach( array_keys($this->models) as $id ) 
		{
			$this->getModel($id);
		}
		return $this->models;
	}
	public function hasModel($id) 
	{
		return isset($this->models[$id]);
	}
	public function addModel(Parameter $model) 
	{
		$this->models[$model->getName()] = $model;
		return $this;
	}
	public function getApiVersion() 
	{
		return $this->apiVersion;
	}
	public function getName() 
	{
		return $this->name;
	}
	public function getDescription() 
	{
		return $this->description;
	}
	public function getData($key) 
	{
		return (isset($this->extraData[$key]) ? $this->extraData[$key] : null);
	}
	public function setData($key, $value) 
	{
		$this->extraData[$key] = $value;
		return $this;
	}
	protected function fromArray(array $config) 
	{
		static $defaultKeys = array( "name", "models", "apiVersion", "baseUrl", "description" );
		foreach( $defaultKeys as $key ) 
		{
			if( isset($config[$key]) ) 
			{
				$this->$key = $config[$key];
			}
		}
		if( isset($config["basePath"]) ) 
		{
			$this->baseUrl = $config["basePath"];
		}
		$this->models = (array) $this->models;
		$this->operations = (array) $this->operations;
		$defaultKeys[] = "operations";
		if( isset($config["operations"]) ) 
		{
			foreach( $config["operations"] as $name => $operation ) 
			{
				if( !$operation instanceof Operation && !is_array($operation) ) 
				{
					throw new \Guzzle\Common\Exception\InvalidArgumentException("Invalid operation in service description: " . gettype($operation));
				}
				$this->operations[$name] = $operation;
			}
		}
		foreach( array_diff(array_keys($config), $defaultKeys) as $key ) 
		{
			$this->extraData[$key] = $config[$key];
		}
	}
}
?>