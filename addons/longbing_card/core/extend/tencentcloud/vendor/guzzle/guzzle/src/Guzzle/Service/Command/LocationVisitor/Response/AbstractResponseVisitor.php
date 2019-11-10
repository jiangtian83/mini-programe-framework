<?php  namespace Guzzle\Service\Command\LocationVisitor\Response;
abstract class AbstractResponseVisitor implements ResponseVisitorInterface 
{
	public function before(\Guzzle\Service\Command\CommandInterface $command, array &$result) 
	{
	}
	public function after(\Guzzle\Service\Command\CommandInterface $command) 
	{
	}
	public function visit(\Guzzle\Service\Command\CommandInterface $command, \Guzzle\Http\Message\Response $response, \Guzzle\Service\Description\Parameter $param, &$value, $context = NULL) 
	{
	}
}
?>