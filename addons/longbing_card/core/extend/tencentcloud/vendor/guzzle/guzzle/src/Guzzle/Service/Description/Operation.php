<?php  namespace Guzzle\Service\Description;
class Operation implements OperationInterface 
{
	protected static $properties = array( "name" => true, "httpMethod" => true, "uri" => true, "class" => true, "responseClass" => true, "responseType" => true, "responseNotes" => true, "notes" => true, "summary" => true, "documentationUrl" => true, "deprecated" => true, "data" => true, "parameters" => true, "additionalParameters" => true, "errorResponses" => true );
	protected $parameters = array( );
	protected $additionalParameters = NULL;
	protected $name = NULL;
	protected $httpMethod = NULL;
	protected $summary = NULL;
	protected $notes = NULL;
	protected $documentationUrl = NULL;
	protected $uri = NULL;
	protected $class = NULL;
	protected $responseClass = NULL;
	protected $responseType = NULL;
	protected $responseNotes = NULL;
	protected $deprecated = NULL;
	protected $errorResponses = NULL;
	protected $description = NULL;
	protected $data = NULL;
	const DEFAULT_COMMAND_CLASS = "Guzzle\\Service\\Command\\OperationCommand";
	public function __construct(array $config = array( ), ServiceDescriptionInterface $description = NULL) 
	{
		$this->description = $description;
		foreach( array_intersect_key($config, self::$properties) as $key => $value ) 
		{
			$this->$key = $value;
		}
		$this->class = ($this->class ?: self::DEFAULT_COMMAND_CLASS);
		$this->deprecated = (bool) $this->deprecated;
		$this->errorResponses = ($this->errorResponses ?: array( ));
		$this->data = ($this->data ?: array( ));
		if( !$this->responseClass ) 
		{
			$this->responseClass = "array";
			$this->responseType = "primitive";
		}
		else 
		{
			if( $this->responseType ) 
			{
				$this->setResponseType($this->responseType);
			}
			else 
			{
				$this->inferResponseType();
			}
		}
		if( $this->parameters ) 
		{
			foreach( $this->parameters as $name => $param ) 
			{
				if( $param instanceof Parameter ) 
				{
					$param->setName($name)->setParent($this);
				}
				else 
				{
					if( is_array($param) ) 
					{
						$param["name"] = $name;
						$this->addParam(new Parameter($param, $this->description));
					}
				}
			}
		}
		if( $this->additionalParameters ) 
		{
			if( $this->additionalParameters instanceof Parameter ) 
			{
				$this->additionalParameters->setParent($this);
			}
			else 
			{
				if( is_array($this->additionalParameters) ) 
				{
					$this->setadditionalParameters(new Parameter($this->additionalParameters, $this->description));
				}
			}
		}
	}
	public function toArray() 
	{
		$result = array( );
		foreach( array_keys(self::$properties) as $check ) 
		{
			if( $value = $this->$check ) 
			{
				$result[$check] = $value;
			}
		}
		unset($result["name"]);
		$result["parameters"] = array( );
		foreach( $this->parameters as $key => $param ) 
		{
			$result["parameters"][$key] = $param->toArray();
		}
		if( $this->additionalParameters instanceof Parameter ) 
		{
			$result["additionalParameters"] = $this->additionalParameters->toArray();
		}
		return $result;
	}
	public function getServiceDescription() 
	{
		return $this->description;
	}
	public function setServiceDescription(ServiceDescriptionInterface $description) 
	{
		$this->description = $description;
		return $this;
	}
	public function getParams() 
	{
		return $this->parameters;
	}
	public function getParamNames() 
	{
		return array_keys($this->parameters);
	}
	public function hasParam($name) 
	{
		return isset($this->parameters[$name]);
	}
	public function getParam($param) 
	{
		return (isset($this->parameters[$param]) ? $this->parameters[$param] : null);
	}
	public function addParam(Parameter $param) 
	{
		$this->parameters[$param->getName()] = $param;
		$param->setParent($this);
		return $this;
	}
	public function removeParam($name) 
	{
		unset($this->parameters[$name]);
		return $this;
	}
	public function getHttpMethod() 
	{
		return $this->httpMethod;
	}
	public function setHttpMethod($httpMethod) 
	{
		$this->httpMethod = $httpMethod;
		return $this;
	}
	public function getClass() 
	{
		return $this->class;
	}
	public function setClass($className) 
	{
		$this->class = $className;
		return $this;
	}
	public function getName() 
	{
		return $this->name;
	}
	public function setName($name) 
	{
		$this->name = $name;
		return $this;
	}
	public function getSummary() 
	{
		return $this->summary;
	}
	public function setSummary($summary) 
	{
		$this->summary = $summary;
		return $this;
	}
	public function getNotes() 
	{
		return $this->notes;
	}
	public function setNotes($notes) 
	{
		$this->notes = $notes;
		return $this;
	}
	public function getDocumentationUrl() 
	{
		return $this->documentationUrl;
	}
	public function setDocumentationUrl($docUrl) 
	{
		$this->documentationUrl = $docUrl;
		return $this;
	}
	public function getResponseClass() 
	{
		return $this->responseClass;
	}
	public function setResponseClass($responseClass) 
	{
		$this->responseClass = $responseClass;
		$this->inferResponseType();
		return $this;
	}
	public function getResponseType() 
	{
		return $this->responseType;
	}
	public function setResponseType($responseType) 
	{
		static $types = NULL;
		if( !isset($types[$responseType]) ) 
		{
			throw new \Guzzle\Common\Exception\InvalidArgumentException("responseType must be one of " . implode(", ", array_keys($types)));
		}
		$this->responseType = $responseType;
		return $this;
	}
	public function getResponseNotes() 
	{
		return $this->responseNotes;
	}
	public function setResponseNotes($notes) 
	{
		$this->responseNotes = $notes;
		return $this;
	}
	public function getDeprecated() 
	{
		return $this->deprecated;
	}
	public function setDeprecated($isDeprecated) 
	{
		$this->deprecated = $isDeprecated;
		return $this;
	}
	public function getUri() 
	{
		return $this->uri;
	}
	public function setUri($uri) 
	{
		$this->uri = $uri;
		return $this;
	}
	public function getErrorResponses() 
	{
		return $this->errorResponses;
	}
	public function addErrorResponse($code, $reason, $class) 
	{
		$this->errorResponses[] = array( "code" => $code, "reason" => $reason, "class" => $class );
		return $this;
	}
	public function setErrorResponses(array $errorResponses) 
	{
		$this->errorResponses = $errorResponses;
		return $this;
	}
	public function getData($name) 
	{
		return (isset($this->data[$name]) ? $this->data[$name] : null);
	}
	public function setData($name, $value) 
	{
		$this->data[$name] = $value;
		return $this;
	}
	public function getAdditionalParameters() 
	{
		return $this->additionalParameters;
	}
	public function setAdditionalParameters($parameter) 
	{
		if( $this->additionalParameters = $parameter ) 
		{
			$this->additionalParameters->setParent($this);
		}
		return $this;
	}
	protected function inferResponseType() 
	{
		static $primitives = array( "array" => 1, "boolean" => 1, "string" => 1, "integer" => 1, "" => 1 );
		if( isset($primitives[$this->responseClass]) ) 
		{
			$this->responseType = self::TYPE_PRIMITIVE;
		}
		else 
		{
			if( $this->description && $this->description->hasModel($this->responseClass) ) 
			{
				$this->responseType = self::TYPE_MODEL;
			}
			else 
			{
				$this->responseType = self::TYPE_CLASS;
			}
		}
	}
}
?>