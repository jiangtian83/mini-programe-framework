<?php  namespace Guzzle\Service\Description;
class Parameter 
{
	protected $name = NULL;
	protected $description = NULL;
	protected $serviceDescription = NULL;
	protected $type = NULL;
	protected $required = NULL;
	protected $enum = NULL;
	protected $pattern = NULL;
	protected $minimum = NULL;
	protected $maximum = NULL;
	protected $minLength = NULL;
	protected $maxLength = NULL;
	protected $minItems = NULL;
	protected $maxItems = NULL;
	protected $default = NULL;
	protected $static = NULL;
	protected $instanceOf = NULL;
	protected $filters = NULL;
	protected $location = NULL;
	protected $sentAs = NULL;
	protected $data = NULL;
	protected $properties = array( );
	protected $additionalProperties = NULL;
	protected $items = NULL;
	protected $parent = NULL;
	protected $ref = NULL;
	protected $format = NULL;
	protected $propertiesCache = NULL;
	public function __construct(array $data = array( ), ServiceDescriptionInterface $description = NULL) 
	{
		if( $description ) 
		{
			if( isset($data["\$ref"]) ) 
			{
				if( $model = $description->getModel($data["\$ref"]) ) 
				{
					$data = $model->toArray() + $data;
				}
			}
			else 
			{
				if( isset($data["extends"]) && ($extends = $description->getModel($data["extends"])) ) 
				{
					$data += $extends->toArray();
				}
			}
		}
		foreach( $data as $key => $value ) 
		{
			$this->$key = $value;
		}
		$this->serviceDescription = $description;
		$this->required = (bool) $this->required;
		$this->data = (array) $this->data;
		if( $this->filters ) 
		{
			$this->setFilters((array) $this->filters);
		}
		if( $this->type == "object" && $this->additionalProperties === null ) 
		{
			$this->additionalProperties = true;
		}
	}
	public function toArray() 
	{
		static $checks = array( "required", "description", "static", "type", "format", "instanceOf", "location", "sentAs", "pattern", "minimum", "maximum", "minItems", "maxItems", "minLength", "maxLength", "data", "enum", "filters" );
		$result = array( );
		if( $this->parent instanceof $this && $this->parent->getType() == "array" && isset($this->name) ) 
		{
			$result["name"] = $this->name;
		}
		foreach( $checks as $c ) 
		{
			if( $value = $this->$c ) 
			{
				$result[$c] = $value;
			}
		}
		if( $this->default !== null ) 
		{
			$result["default"] = $this->default;
		}
		if( $this->items !== null ) 
		{
			$result["items"] = $this->getItems()->toArray();
		}
		if( $this->additionalProperties !== null ) 
		{
			$result["additionalProperties"] = $this->getAdditionalProperties();
			if( $result["additionalProperties"] instanceof $this ) 
			{
				$result["additionalProperties"] = $result["additionalProperties"]->toArray();
			}
		}
		if( $this->type == "object" && $this->properties ) 
		{
			$result["properties"] = array( );
			foreach( $this->getProperties() as $name => $property ) 
			{
				$result["properties"][$name] = $property->toArray();
			}
		}
		return $result;
	}
	public function getValue($value) 
	{
		if( $this->static || $this->default !== null && $value === null ) 
		{
			return $this->default;
		}
		return $value;
	}
	public function filter($value) 
	{
		if( $this->format ) 
		{
			return SchemaFormatter::format($this->format, $value);
		}
		if( $this->type == "boolean" && !is_bool($value) ) 
		{
			$value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
		}
		if( $this->filters ) 
		{
			foreach( $this->filters as $filter ) 
			{
				if( is_array($filter) ) 
				{
					foreach( $filter["args"] as &$data ) 
					{
						if( $data == "@value" ) 
						{
							$data = $value;
						}
						else 
						{
							if( $data == "@api" ) 
							{
								$data = $this;
							}
						}
					}
					$value = call_user_func_array($filter["method"], $filter["args"]);
				}
				else 
				{
					$value = call_user_func($filter, $value);
				}
			}
		}
		return $value;
	}
	public function getName() 
	{
		return $this->name;
	}
	public function getWireName() 
	{
		return ($this->sentAs ?: $this->name);
	}
	public function setName($name) 
	{
		$this->name = $name;
		return $this;
	}
	public function getType() 
	{
		return $this->type;
	}
	public function setType($type) 
	{
		$this->type = $type;
		return $this;
	}
	public function getRequired() 
	{
		return $this->required;
	}
	public function setRequired($isRequired) 
	{
		$this->required = (bool) $isRequired;
		return $this;
	}
	public function getDefault() 
	{
		return $this->default;
	}
	public function setDefault($default) 
	{
		$this->default = $default;
		return $this;
	}
	public function getDescription() 
	{
		return $this->description;
	}
	public function setDescription($description) 
	{
		$this->description = $description;
		return $this;
	}
	public function getMinimum() 
	{
		return $this->minimum;
	}
	public function setMinimum($min) 
	{
		$this->minimum = $min;
		return $this;
	}
	public function getMaximum() 
	{
		return $this->maximum;
	}
	public function setMaximum($max) 
	{
		$this->maximum = $max;
		return $this;
	}
	public function getMinLength() 
	{
		return $this->minLength;
	}
	public function setMinLength($min) 
	{
		$this->minLength = $min;
		return $this;
	}
	public function getMaxLength() 
	{
		return $this->maxLength;
	}
	public function setMaxLength($max) 
	{
		$this->maxLength = $max;
		return $this;
	}
	public function getMaxItems() 
	{
		return $this->maxItems;
	}
	public function setMaxItems($max) 
	{
		$this->maxItems = $max;
		return $this;
	}
	public function getMinItems() 
	{
		return $this->minItems;
	}
	public function setMinItems($min) 
	{
		$this->minItems = $min;
		return $this;
	}
	public function getLocation() 
	{
		return $this->location;
	}
	public function setLocation($location) 
	{
		$this->location = $location;
		return $this;
	}
	public function getSentAs() 
	{
		return $this->sentAs;
	}
	public function setSentAs($name) 
	{
		$this->sentAs = $name;
		return $this;
	}
	public function getData($name = NULL) 
	{
		if( !$name ) 
		{
			return $this->data;
		}
		if( isset($this->data[$name]) ) 
		{
			return $this->data[$name];
		}
		if( isset($this->$name) ) 
		{
			return $this->$name;
		}
		return null;
	}
	public function setData($nameOrData, $data = NULL) 
	{
		if( is_array($nameOrData) ) 
		{
			$this->data = $nameOrData;
		}
		else 
		{
			$this->data[$nameOrData] = $data;
		}
		return $this;
	}
	public function getStatic() 
	{
		return $this->static;
	}
	public function setStatic($static) 
	{
		$this->static = (bool) $static;
		return $this;
	}
	public function getFilters() 
	{
		return ($this->filters ?: array( ));
	}
	public function setFilters(array $filters) 
	{
		$this->filters = array( );
		foreach( $filters as $filter ) 
		{
			$this->addFilter($filter);
		}
		return $this;
	}
	public function addFilter($filter) 
	{
		if( is_array($filter) && !isset($filter["method"]) ) 
		{
			throw new \Guzzle\Common\Exception\InvalidArgumentException("A [method] value must be specified for each complex filter");
		}
		if( !$this->filters ) 
		{
			$this->filters = array( $filter );
		}
		else 
		{
			$this->filters[] = $filter;
		}
		return $this;
	}
	public function getParent() 
	{
		return $this->parent;
	}
	public function setParent($parent) 
	{
		$this->parent = $parent;
		return $this;
	}
	public function getProperties() 
	{
		if( !$this->propertiesCache ) 
		{
			$this->propertiesCache = array( );
			foreach( array_keys($this->properties) as $name ) 
			{
				$this->propertiesCache[$name] = $this->getProperty($name);
			}
		}
		return $this->propertiesCache;
	}
	public function getProperty($name) 
	{
		if( !isset($this->properties[$name]) ) 
		{
			return null;
		}
		if( !$this->properties[$name] instanceof $this ) 
		{
			$this->properties[$name]["name"] = $name;
			$this->properties[$name] = new static($this->properties[$name], $this->serviceDescription);
			$this->properties[$name]->setParent($this);
		}
		return $this->properties[$name];
	}
	public function removeProperty($name) 
	{
		unset($this->properties[$name]);
		$this->propertiesCache = null;
		return $this;
	}
	public function addProperty(Parameter $property) 
	{
		$this->properties[$property->getName()] = $property;
		$property->setParent($this);
		$this->propertiesCache = null;
		return $this;
	}
	public function getAdditionalProperties() 
	{
		if( is_array($this->additionalProperties) ) 
		{
			$this->additionalProperties = new static($this->additionalProperties, $this->serviceDescription);
			$this->additionalProperties->setParent($this);
		}
		return $this->additionalProperties;
	}
	public function setAdditionalProperties($additional) 
	{
		$this->additionalProperties = $additional;
		return $this;
	}
	public function setItems(Parameter $items = NULL) 
	{
		if( $this->items = $items ) 
		{
			$this->items->setParent($this);
		}
		return $this;
	}
	public function getItems() 
	{
		if( is_array($this->items) ) 
		{
			$this->items = new static($this->items, $this->serviceDescription);
			$this->items->setParent($this);
		}
		return $this->items;
	}
	public function getInstanceOf() 
	{
		return $this->instanceOf;
	}
	public function setInstanceOf($instanceOf) 
	{
		$this->instanceOf = $instanceOf;
		return $this;
	}
	public function getEnum() 
	{
		return $this->enum;
	}
	public function setEnum(array $enum = NULL) 
	{
		$this->enum = $enum;
		return $this;
	}
	public function getPattern() 
	{
		return $this->pattern;
	}
	public function setPattern($pattern) 
	{
		$this->pattern = $pattern;
		return $this;
	}
	public function getFormat() 
	{
		return $this->format;
	}
	public function setFormat($format) 
	{
		$this->format = $format;
		return $this;
	}
}
?>