<?php  namespace Guzzle\Service\Command\LocationVisitor\Request;
class HeaderVisitor extends AbstractRequestVisitor 
{
	public function visit(\Guzzle\Service\Command\CommandInterface $command, \Guzzle\Http\Message\RequestInterface $request, \Guzzle\Service\Description\Parameter $param, $value) 
	{
		$value = $param->filter($value);
		if( $param->getType() == "object" && $param->getAdditionalProperties() instanceof \Guzzle\Service\Description\Parameter ) 
		{
			$this->addPrefixedHeaders($request, $param, $value);
		}
		else 
		{
			$request->setHeader($param->getWireName(), $value);
		}
	}
	protected function addPrefixedHeaders(\Guzzle\Http\Message\RequestInterface $request, \Guzzle\Service\Description\Parameter $param, $value) 
	{
		if( !is_array($value) ) 
		{
			throw new \Guzzle\Common\Exception\InvalidArgumentException("An array of mapped headers expected, but received a single value");
		}
		$prefix = $param->getSentAs();
		foreach( $value as $headerName => $headerValue ) 
		{
			$request->setHeader($prefix . $headerName, $headerValue);
		}
	}
}
?>