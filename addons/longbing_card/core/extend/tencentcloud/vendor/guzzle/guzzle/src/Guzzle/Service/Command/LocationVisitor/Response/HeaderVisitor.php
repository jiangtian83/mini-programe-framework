<?php  namespace Guzzle\Service\Command\LocationVisitor\Response;
class HeaderVisitor extends AbstractResponseVisitor 
{
	public function visit(\Guzzle\Service\Command\CommandInterface $command, \Guzzle\Http\Message\Response $response, \Guzzle\Service\Description\Parameter $param, &$value, $context = NULL) 
	{
		if( $param->getType() == "object" && $param->getAdditionalProperties() instanceof \Guzzle\Service\Description\Parameter ) 
		{
			$this->processPrefixedHeaders($response, $param, $value);
		}
		else 
		{
			$value[$param->getName()] = $param->filter((string) $response->getHeader($param->getWireName()));
		}
	}
	protected function processPrefixedHeaders(\Guzzle\Http\Message\Response $response, \Guzzle\Service\Description\Parameter $param, &$value) 
	{
		if( $prefix = $param->getSentAs() ) 
		{
			$container = $param->getName();
			$len = strlen($prefix);
			foreach( $response->getHeaders()->toArray() as $key => $header ) 
			{
				if( stripos($key, $prefix) === 0 ) 
				{
					$value[$container][substr($key, $len)] = (count($header) == 1 ? end($header) : $header);
				}
			}
		}
	}
}
?>