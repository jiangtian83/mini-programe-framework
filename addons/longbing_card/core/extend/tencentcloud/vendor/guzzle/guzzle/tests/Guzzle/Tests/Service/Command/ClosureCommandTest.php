<?php  namespace Guzzle\Tests\Service\Command;
class ClosureCommandTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testConstructorValidatesClosure() 
	{
		$c = new \Guzzle\Service\Command\ClosureCommand();
	}
	public function testExecutesClosure() 
	{
		$c = new \Guzzle\Service\Command\ClosureCommand(array( "closure" => function($command, $api) 
		{
			$command->set("testing", "123");
			$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("GET", "http://www.test.com/");
			return $request;
		}
		));
		$client = $this->getServiceBuilder()->get("mock");
		$c->setClient($client)->prepare();
		$this->assertEquals("123", $c->get("testing"));
		$this->assertEquals("http://www.test.com/", $c->getRequest()->getUrl());
	}
	public function testMustReturnRequest() 
	{
		$c = new \Guzzle\Service\Command\ClosureCommand(array( "closure" => function($command, $api) 
		{
			return false;
		}
		));
		$client = $this->getServiceBuilder()->get("mock");
		$c->setClient($client)->prepare();
	}
}
?>