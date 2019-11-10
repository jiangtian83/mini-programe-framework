<?php  namespace Guzzle\Service\Command\LocationVisitor\Response;
class BodyVisitor extends AbstractResponseVisitor 
{
	public function visit(\Guzzle\Service\Command\CommandInterface $command, \Guzzle\Http\Message\Response $response, \Guzzle\Service\Description\Parameter $param, &$value, $context = NULL) 
	{
		$value[$param->getName()] = $param->filter($response->getBody());
	}
}
?>