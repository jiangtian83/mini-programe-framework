<?php  namespace Guzzle\Service\Command\LocationVisitor\Response;
class XmlVisitor extends AbstractResponseVisitor 
{
	public function before(\Guzzle\Service\Command\CommandInterface $command, array &$result) 
	{
		$result = json_decode(json_encode($command->getResponse()->xml()), true);
	}
	public function visit(\Guzzle\Service\Command\CommandInterface $command, \Guzzle\Http\Message\Response $response, \Guzzle\Service\Description\Parameter $param, &$value, $context = NULL) 
	{
		$sentAs = $param->getWireName();
		$name = $param->getName();
		if( isset($value[$sentAs]) ) 
		{
			$this->recursiveProcess($param, $value[$sentAs]);
			if( $name != $sentAs ) 
			{
				$value[$name] = $value[$sentAs];
				unset($value[$sentAs]);
			}
		}
	}
	protected function recursiveProcess(\Guzzle\Service\Description\Parameter $param, &$value) 
	{
		$type = $param->getType();
		if( !is_array($value) ) 
		{
			if( $type == "array" ) 
			{
				$this->recursiveProcess($param->getItems(), $value);
				$value = array( $value );
			}
		}
		else 
		{
			if( $type == "object" ) 
			{
				$this->processObject($param, $value);
			}
			else 
			{
				if( $type == "array" ) 
				{
					$this->processArray($param, $value);
				}
				else 
				{
					if( $type == "string" && gettype($value) == "array" ) 
					{
						$value = "";
					}
				}
			}
		}
		if( $value !== null ) 
		{
			$value = $param->filter($value);
		}
	}
	protected function processArray(\Guzzle\Service\Description\Parameter $param, &$value) 
	{
		if( !isset($value[0]) ) 
		{
			if( $param->getItems() && isset($value[$param->getItems()->getWireName()]) ) 
			{
				$value = $value[$param->getItems()->getWireName()];
				if( !isset($value[0]) || !is_array($value) ) 
				{
					$value = array( $value );
				}
			}
			else 
			{
				if( !empty($value) ) 
				{
					$value = array( $value );
				}
			}
		}
		foreach( $value as &$item ) 
		{
			$this->recursiveProcess($param->getItems(), $item);
		}
	}
	protected function processObject(\Guzzle\Service\Description\Parameter $param, &$value) 
	{
		if( !isset($value[0]) && ($properties = $param->getProperties()) ) 
		{
			$knownProperties = array( );
			foreach( $properties as $property ) 
			{
				$name = $property->getName();
				$sentAs = $property->getWireName();
				$knownProperties[$name] = 1;
				if( $property->getData("xmlAttribute") ) 
				{
					$this->processXmlAttribute($property, $value);
				}
				else 
				{
					if( isset($value[$sentAs]) ) 
					{
						$this->recursiveProcess($property, $value[$sentAs]);
						if( $name != $sentAs ) 
						{
							$value[$name] = $value[$sentAs];
							unset($value[$sentAs]);
						}
					}
				}
			}
			if( $param->getAdditionalProperties() === false ) 
			{
				$value = array_intersect_key($value, $knownProperties);
			}
		}
	}
	protected function processXmlAttribute(\Guzzle\Service\Description\Parameter $property, array &$value) 
	{
		$sentAs = $property->getWireName();
		if( isset($value["@attributes"][$sentAs]) ) 
		{
			$value[$property->getName()] = $value["@attributes"][$sentAs];
			unset($value["@attributes"][$sentAs]);
			if( empty($value["@attributes"]) ) 
			{
				unset($value["@attributes"]);
			}
		}
	}
}
?>