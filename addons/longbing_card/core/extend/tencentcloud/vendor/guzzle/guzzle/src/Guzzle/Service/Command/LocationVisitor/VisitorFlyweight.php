<?php  namespace Guzzle\Service\Command\LocationVisitor;
class VisitorFlyweight 
{
	protected static $instance = NULL;
	protected static $defaultMappings = array( "request.body" => "Guzzle\\Service\\Command\\LocationVisitor\\Request\\BodyVisitor", "request.header" => "Guzzle\\Service\\Command\\LocationVisitor\\Request\\HeaderVisitor", "request.json" => "Guzzle\\Service\\Command\\LocationVisitor\\Request\\JsonVisitor", "request.postField" => "Guzzle\\Service\\Command\\LocationVisitor\\Request\\PostFieldVisitor", "request.postFile" => "Guzzle\\Service\\Command\\LocationVisitor\\Request\\PostFileVisitor", "request.query" => "Guzzle\\Service\\Command\\LocationVisitor\\Request\\QueryVisitor", "request.response_body" => "Guzzle\\Service\\Command\\LocationVisitor\\Request\\ResponseBodyVisitor", "request.responseBody" => "Guzzle\\Service\\Command\\LocationVisitor\\Request\\ResponseBodyVisitor", "request.xml" => "Guzzle\\Service\\Command\\LocationVisitor\\Request\\XmlVisitor", "response.body" => "Guzzle\\Service\\Command\\LocationVisitor\\Response\\BodyVisitor", "response.header" => "Guzzle\\Service\\Command\\LocationVisitor\\Response\\HeaderVisitor", "response.json" => "Guzzle\\Service\\Command\\LocationVisitor\\Response\\JsonVisitor", "response.reasonPhrase" => "Guzzle\\Service\\Command\\LocationVisitor\\Response\\ReasonPhraseVisitor", "response.statusCode" => "Guzzle\\Service\\Command\\LocationVisitor\\Response\\StatusCodeVisitor", "response.xml" => "Guzzle\\Service\\Command\\LocationVisitor\\Response\\XmlVisitor" );
	protected $mappings = NULL;
	protected $cache = array( );
	public static function getInstance() 
	{
		if( !self::$instance ) 
		{
			self::$instance = new self();
		}
		return self::$instance;
	}
	public function __construct(array $mappings = NULL) 
	{
		$this->mappings = ($mappings === null ? self::$defaultMappings : $mappings);
	}
	public function getRequestVisitor($visitor) 
	{
		return $this->getKey("request." . $visitor);
	}
	public function getResponseVisitor($visitor) 
	{
		return $this->getKey("response." . $visitor);
	}
	public function addRequestVisitor($name, Request\RequestVisitorInterface $visitor) 
	{
		$this->cache["request." . $name] = $visitor;
		return $this;
	}
	public function addResponseVisitor($name, Response\ResponseVisitorInterface $visitor) 
	{
		$this->cache["response." . $name] = $visitor;
		return $this;
	}
	private function getKey($key) 
	{
		if( !isset($this->cache[$key]) ) 
		{
			if( !isset($this->mappings[$key]) ) 
			{
				list($type, $name) = explode(".", $key);
				throw new \Guzzle\Common\Exception\InvalidArgumentException("No " . $type . " visitor has been mapped for " . $name);
			}
			$this->cache[$key] = new $this->mappings[$key]();
		}
		return $this->cache[$key];
	}
}
?>