<?php  namespace Guzzle\Service\Command\LocationVisitor\Request;
class XmlVisitor extends AbstractRequestVisitor 
{
	protected $data = NULL;
	protected $contentType = "application/xml";
	public function __construct() 
	{
		$this->data = new \SplObjectStorage();
	}
	public function setContentTypeHeader($header) 
	{
		$this->contentType = $header;
		return $this;
	}
	public function visit(\Guzzle\Service\Command\CommandInterface $command, \Guzzle\Http\Message\RequestInterface $request, \Guzzle\Service\Description\Parameter $param, $value) 
	{
		$xml = (isset($this->data[$command]) ? $this->data[$command] : $this->createRootElement($param->getParent()));
		$this->addXml($xml, $param, $value);
		$this->data[$command] = $xml;
	}
	public function after(\Guzzle\Service\Command\CommandInterface $command, \Guzzle\Http\Message\RequestInterface $request) 
	{
		$xml = null;
		if( isset($this->data[$command]) ) 
		{
			$xml = $this->finishDocument($this->data[$command]);
			unset($this->data[$command]);
		}
		else 
		{
			$operation = $command->getOperation();
			if( $operation->getData("xmlAllowEmpty") ) 
			{
				$xmlWriter = $this->createRootElement($operation);
				$xml = $this->finishDocument($xmlWriter);
			}
		}
		if( $xml ) 
		{
			if( $this->contentType && !$request->hasHeader("Content-Type") ) 
			{
				$request->setHeader("Content-Type", $this->contentType);
			}
			$request->setBody($xml);
		}
	}
	protected function createRootElement(\Guzzle\Service\Description\Operation $operation) 
	{
		static $defaultRoot = array( "name" => "Request" );
		$root = ($operation->getData("xmlRoot") ?: $defaultRoot);
		$encoding = $operation->getData("xmlEncoding");
		$xmlWriter = $this->startDocument($encoding);
		$xmlWriter->startElement($root["name"]);
		if( !empty($root["namespaces"]) ) 
		{
			foreach( (array) $root["namespaces"] as $prefix => $uri ) 
			{
				$nsLabel = "xmlns";
				if( !is_numeric($prefix) ) 
				{
					$nsLabel .= ":" . $prefix;
				}
				$xmlWriter->writeAttribute($nsLabel, $uri);
			}
		}
		return $xmlWriter;
	}
	protected function addXml(\XMLWriter $xmlWriter, \Guzzle\Service\Description\Parameter $param, $value) 
	{
		if( $value === null ) 
		{
			return NULL;
		}
		$value = $param->filter($value);
		$type = $param->getType();
		$name = $param->getWireName();
		$prefix = null;
		$namespace = $param->getData("xmlNamespace");
		if( false !== strpos($name, ":") ) 
		{
			list($prefix, $name) = explode(":", $name, 2);
		}
		if( $type == "object" || $type == "array" ) 
		{
			if( !$param->getData("xmlFlattened") ) 
			{
				$xmlWriter->startElementNS(null, $name, $namespace);
			}
			if( $param->getType() == "array" ) 
			{
				$this->addXmlArray($xmlWriter, $param, $value);
			}
			else 
			{
				if( $param->getType() == "object" ) 
				{
					$this->addXmlObject($xmlWriter, $param, $value);
				}
			}
			if( !$param->getData("xmlFlattened") ) 
			{
				$xmlWriter->endElement();
			}
		}
		else 
		{
			if( $param->getData("xmlAttribute") ) 
			{
				$this->writeAttribute($xmlWriter, $prefix, $name, $namespace, $value);
			}
			else 
			{
				$this->writeElement($xmlWriter, $prefix, $name, $namespace, $value);
			}
		}
	}
	protected function writeAttribute($xmlWriter, $prefix, $name, $namespace, $value) 
	{
		if( empty($namespace) ) 
		{
			$xmlWriter->writeAttribute($name, $value);
		}
		else 
		{
			$xmlWriter->writeAttributeNS($prefix, $name, $namespace, $value);
		}
	}
	protected function writeElement(\XMLWriter $xmlWriter, $prefix, $name, $namespace, $value) 
	{
		$xmlWriter->startElementNS($prefix, $name, $namespace);
		if( strpbrk($value, "<>&") ) 
		{
			$xmlWriter->writeCData($value);
		}
		else 
		{
			$xmlWriter->writeRaw($value);
		}
		$xmlWriter->endElement();
	}
	protected function startDocument($encoding) 
	{
		$xmlWriter = new \XMLWriter();
		$xmlWriter->openMemory();
		$xmlWriter->startDocument("1.0", $encoding);
		return $xmlWriter;
	}
	protected function finishDocument($xmlWriter) 
	{
		$xmlWriter->endDocument();
		return $xmlWriter->outputMemory();
	}
	protected function addXmlArray(\XMLWriter $xmlWriter, \Guzzle\Service\Description\Parameter $param, &$value) 
	{
		if( $items = $param->getItems() ) 
		{
			foreach( $value as $v ) 
			{
				$this->addXml($xmlWriter, $items, $v);
			}
		}
	}
	protected function addXmlObject(\XMLWriter $xmlWriter, \Guzzle\Service\Description\Parameter $param, &$value) 
	{
		$noAttributes = array( );
		foreach( $value as $name => $v ) 
		{
			if( $property = $param->getProperty($name) ) 
			{
				if( $property->getData("xmlAttribute") ) 
				{
					$this->addXml($xmlWriter, $property, $v);
				}
				else 
				{
					$noAttributes[] = array( "value" => $v, "property" => $property );
				}
			}
		}
		foreach( $noAttributes as $element ) 
		{
			$this->addXml($xmlWriter, $element["property"], $element["value"]);
		}
	}
}
?>