<?php  namespace Guzzle\Service\Command\LocationVisitor\Response;
class StatusCodeVisitor extends AbstractResponseVisitor 
{
	public function visit(\Guzzle\Service\Command\CommandInterface $command, \Guzzle\Http\Message\Response $response, \Guzzle\Service\Description\Parameter $param, &$value, $context = NULL) 
	{
		$value[$param->getName()] = $response->getStatusCode();
	}
}
?>