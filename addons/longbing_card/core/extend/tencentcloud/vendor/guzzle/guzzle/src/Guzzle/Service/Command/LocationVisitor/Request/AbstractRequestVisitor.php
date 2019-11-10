<?php  namespace Guzzle\Service\Command\LocationVisitor\Request;
abstract class AbstractRequestVisitor implements RequestVisitorInterface 
{
	public function after(\Guzzle\Service\Command\CommandInterface $command, \Guzzle\Http\Message\RequestInterface $request) 
	{
	}
	public function visit(\Guzzle\Service\Command\CommandInterface $command, \Guzzle\Http\Message\RequestInterface $request, \Guzzle\Service\Description\Parameter $param, $value) 
	{
	}
	protected function prepareValue($value, \Guzzle\Service\Description\Parameter $param) 
	{
		return (is_array($value) ? $this->resolveRecursively($value, $param) : $param->filter($value));
	}
	protected function resolveRecursively(array $value, \Guzzle\Service\Description\Parameter $param) 
	{
		foreach( $value as $name => &$v ) 
		{
			switch( $param->getType() ) 
			{
				case "object": if( $subParam = $param->getProperty($name) ) 
				{
					$key = $subParam->getWireName();
					$value[$key] = $this->prepareValue($v, $subParam);
					if( $name != $key ) 
					{
						unset($value[$name]);
					}
				}
				else 
				{
					if( $param->getAdditionalProperties() instanceof \Guzzle\Service\Description\Parameter ) 
					{
						$v = $this->prepareValue($v, $param->getAdditionalProperties());
					}
				}
				break;
				case "array": if( $items = $param->getItems() ) 
				{
					$v = $this->prepareValue($v, $items);
				}
				break;
			}
		}
		return $param->filter($value);
	}
}
?>