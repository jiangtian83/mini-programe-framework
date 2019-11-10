<?php  namespace Guzzle\Service\Command\LocationVisitor\Request;
class ResponseBodyVisitor extends AbstractRequestVisitor 
{
	public function visit(\Guzzle\Service\Command\CommandInterface $command, \Guzzle\Http\Message\RequestInterface $request, \Guzzle\Service\Description\Parameter $param, $value) 
	{
		$request->setResponseBody($value);
	}
}
?>