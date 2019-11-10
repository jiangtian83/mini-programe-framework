<?php  namespace Guzzle\Service\Command\LocationVisitor\Request;
interface RequestVisitorInterface 
{
	public function after(\Guzzle\Service\Command\CommandInterface $command, \Guzzle\Http\Message\RequestInterface $request);
	public function visit(\Guzzle\Service\Command\CommandInterface $command, \Guzzle\Http\Message\RequestInterface $request, \Guzzle\Service\Description\Parameter $param, $value);
}
?>