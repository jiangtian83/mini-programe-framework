<?php  namespace Guzzle\Service\Command\LocationVisitor\Request;
class BodyVisitor extends AbstractRequestVisitor 
{
	public function visit(\Guzzle\Service\Command\CommandInterface $command, \Guzzle\Http\Message\RequestInterface $request, \Guzzle\Service\Description\Parameter $param, $value) 
	{
		$value = $param->filter($value);
		$entityBody = \Guzzle\Http\EntityBody::factory($value);
		$request->setBody($entityBody);
		$this->addExpectHeader($request, $entityBody, $param->getData("expect_header"));
		if( $encoding = $entityBody->getContentEncoding() ) 
		{
			$request->setHeader("Content-Encoding", $encoding);
		}
	}
	protected function addExpectHeader(\Guzzle\Http\Message\EntityEnclosingRequestInterface $request, \Guzzle\Http\EntityBodyInterface $body, $expect) 
	{
		if( $expect === false ) 
		{
			$request->removeHeader("Expect");
		}
		else 
		{
			if( $expect !== true ) 
			{
				$expect = ($expect ?: 1048576);
				if( is_numeric($expect) && $body->getSize() ) 
				{
					if( $body->getSize() < $expect ) 
					{
						$request->removeHeader("Expect");
					}
					else 
					{
						$request->setHeader("Expect", "100-Continue");
					}
				}
			}
		}
	}
}
?>