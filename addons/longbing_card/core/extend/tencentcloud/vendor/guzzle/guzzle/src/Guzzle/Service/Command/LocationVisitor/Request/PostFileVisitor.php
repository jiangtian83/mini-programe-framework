<?php  namespace Guzzle\Service\Command\LocationVisitor\Request;
class PostFileVisitor extends AbstractRequestVisitor 
{
	public function visit(\Guzzle\Service\Command\CommandInterface $command, \Guzzle\Http\Message\RequestInterface $request, \Guzzle\Service\Description\Parameter $param, $value) 
	{
		$value = $param->filter($value);
		if( $value instanceof \Guzzle\Http\Message\PostFileInterface ) 
		{
			$request->addPostFile($value);
		}
		else 
		{
			$request->addPostFile($param->getWireName(), $value);
		}
	}
}
?>