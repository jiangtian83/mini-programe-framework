<?php  namespace Guzzle\Service\Command\LocationVisitor\Response;
class JsonVisitor extends AbstractResponseVisitor 
{
	public function before(\Guzzle\Service\Command\CommandInterface $command, array &$result) 
	{
		$result = $command->getResponse()->json();
	}
	public function visit(\Guzzle\Service\Command\CommandInterface $command, \Guzzle\Http\Message\Response $response, \Guzzle\Service\Description\Parameter $param, &$value, $context = NULL) 
	{
		$name = $param->getName();
		$key = $param->getWireName();
		if( isset($value[$key]) ) 
		{
			$this->recursiveProcess($param, $value[$key]);
			if( $key != $name ) 
			{
				$value[$name] = $value[$key];
				unset($value[$key]);
			}
		}
	}
	protected function recursiveProcess(\Guzzle\Service\Description\Parameter $param, &$value) 
	{
		if( $value === null ) 
		{
			return NULL;
		}
		if( is_array($value) ) 
		{
			$type = $param->getType();
			if( $type == "array" ) 
			{
				foreach( $value as &$item ) 
				{
					$this->recursiveProcess($param->getItems(), $item);
				}
			}
			else 
			{
				if( $type == "object" && !isset($value[0]) ) 
				{
					$knownProperties = array( );
					if( $properties = $param->getProperties() ) 
					{
						foreach( $properties as $property ) 
						{
							$name = $property->getName();
							$key = $property->getWireName();
							$knownProperties[$name] = 1;
							if( isset($value[$key]) ) 
							{
								$this->recursiveProcess($property, $value[$key]);
								if( $key != $name ) 
								{
									$value[$name] = $value[$key];
									unset($value[$key]);
								}
							}
						}
					}
					if( $param->getAdditionalProperties() === false ) 
					{
						$value = array_intersect_key($value, $knownProperties);
					}
					else 
					{
						if( ($additional = $param->getAdditionalProperties()) !== true ) 
						{
							foreach( $value as &$v ) 
							{
								$this->recursiveProcess($additional, $v);
							}
						}
					}
				}
			}
		}
		$value = $param->filter($value);
	}
}
?>