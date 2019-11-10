<?php  namespace Guzzle\Tests\Service\Command\LocationVisitor\Request;
class PostFileVisitorTest extends AbstractVisitorTestCase 
{
	public function testVisitsLocation() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Request\PostFileVisitor();
		$param = $this->getNestedCommand("postFile")->getParam("foo");
		$visitor->visit($this->command, $this->request, $param->setSentAs("test_3"), __FILE__);
		$this->assertInternalType("array", $this->request->getPostFile("test_3"));
		$visitor->visit($this->command, $this->request, $param->setSentAs(null), new \Guzzle\Http\Message\PostFile("baz", __FILE__));
		$this->assertInternalType("array", $this->request->getPostFile("baz"));
	}
	public function testVisitsLocationWithMultipleFiles() 
	{
		$description = \Guzzle\Service\Description\ServiceDescription::factory(array( "operations" => array( "DoPost" => array( "httpMethod" => "POST", "parameters" => array( "foo" => array( "location" => "postFile", "type" => array( "string", "array" ) ) ) ) ) ));
		$this->getServer()->flush();
		$this->getServer()->enqueue(array( "HTTP/1.1 200 OK\r\nContent-Length:0\r\n\r\n" ));
		$client = new \Guzzle\Service\Client($this->getServer()->getUrl());
		$client->setDescription($description);
		$command = $client->getCommand("DoPost", array( "foo" => array( __FILE__, __FILE__ ) ));
		$command->execute();
		$received = $this->getServer()->getReceivedRequests();
		$this->assertContains("name=\"foo[0]\";", $received[0]);
		$this->assertContains("name=\"foo[1]\";", $received[0]);
	}
}
?>