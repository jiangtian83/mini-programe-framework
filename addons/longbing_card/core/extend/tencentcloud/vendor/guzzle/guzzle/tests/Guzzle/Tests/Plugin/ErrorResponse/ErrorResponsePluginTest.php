<?php  namespace Guzzle\Tests\Plugin\ErrorResponse;
class ErrorResponsePluginTest extends \Guzzle\Tests\GuzzleTestCase 
{
	protected $client = NULL;
	public static function tearDownAfterClass() 
	{
		self::getServer()->flush();
	}
	public function setUp() 
	{
		$mockError = "Guzzle\\Tests\\Mock\\ErrorResponseMock";
		$description = \Guzzle\Service\Description\ServiceDescription::factory(array( "operations" => array( "works" => array( "httpMethod" => "GET", "errorResponses" => array( array( "code" => 500, "class" => $mockError ), array( "code" => 503, "reason" => "foo", "class" => $mockError ), array( "code" => 200, "reason" => "Error!", "class" => $mockError ) ) ), "bad_class" => array( "httpMethod" => "GET", "errorResponses" => array( array( "code" => 500, "class" => "Does\\Not\\Exist" ) ) ), "does_not_implement" => array( "httpMethod" => "GET", "errorResponses" => array( array( "code" => 500, "class" => "Guzzle\\Tests\\Plugin\\ErrorResponse\\ErrorResponsePluginTest" ) ) ), "no_errors" => array( "httpMethod" => "GET" ), "no_class" => array( "httpMethod" => "GET", "errorResponses" => array( array( "code" => 500 ) ) ) ) ));
		$this->client = new \Guzzle\Service\Client($this->getServer()->getUrl());
		$this->client->setDescription($description);
	}
	public function testSkipsWhenErrorResponsesIsNotSet() 
	{
		$this->getServer()->enqueue("HTTP/1.1 500 Foo\r\nContent-Length: 0\r\n\r\n");
		$this->client->addSubscriber(new \Guzzle\Plugin\ErrorResponse\ErrorResponsePlugin());
		$this->client->getCommand("no_errors")->execute();
	}
	public function testSkipsWhenErrorResponsesIsNotSetAndAllowsSuccess() 
	{
		$this->getServer()->enqueue("HTTP/1.1 200 OK\r\nContent-Length: 0\r\n\r\n");
		$this->client->addSubscriber(new \Guzzle\Plugin\ErrorResponse\ErrorResponsePlugin());
		$this->client->getCommand("no_errors")->execute();
	}
	public function testEnsuresErrorResponseExists() 
	{
		$this->getServer()->enqueue("HTTP/1.1 500 Foo\r\nContent-Length: 0\r\n\r\n");
		$this->client->addSubscriber(new \Guzzle\Plugin\ErrorResponse\ErrorResponsePlugin());
		$this->client->getCommand("bad_class")->execute();
	}
	public function testEnsuresErrorResponseImplementsInterface() 
	{
		$this->getServer()->enqueue("HTTP/1.1 500 Foo\r\nContent-Length: 0\r\n\r\n");
		$this->client->addSubscriber(new \Guzzle\Plugin\ErrorResponse\ErrorResponsePlugin());
		$this->client->getCommand("does_not_implement")->execute();
	}
	public function testThrowsSpecificErrorResponseOnMatch() 
	{
		try 
		{
			$this->getServer()->enqueue("HTTP/1.1 500 Foo\r\nContent-Length: 0\r\n\r\n");
			$this->client->addSubscriber(new \Guzzle\Plugin\ErrorResponse\ErrorResponsePlugin());
			$command = $this->client->getCommand("works");
			$command->execute();
			$this->fail("Exception not thrown");
		}
		catch( \Guzzle\Tests\Mock\ErrorResponseMock $e ) 
		{
			$this->assertSame($command, $e->command);
			$this->assertEquals(500, $e->response->getStatusCode());
		}
	}
	public function testThrowsWhenCodeAndPhraseMatch() 
	{
		$this->getServer()->enqueue("HTTP/1.1 200 Error!\r\nContent-Length: 0\r\n\r\n");
		$this->client->addSubscriber(new \Guzzle\Plugin\ErrorResponse\ErrorResponsePlugin());
		$this->client->getCommand("works")->execute();
	}
	public function testSkipsWhenReasonDoesNotMatch() 
	{
		$this->getServer()->enqueue("HTTP/1.1 200 OK\r\nContent-Length: 0\r\n\r\n");
		$this->client->addSubscriber(new \Guzzle\Plugin\ErrorResponse\ErrorResponsePlugin());
		$this->client->getCommand("works")->execute();
	}
	public function testSkipsWhenNoClassIsSet() 
	{
		$this->getServer()->enqueue("HTTP/1.1 200 OK\r\nContent-Length: 0\r\n\r\n");
		$this->client->addSubscriber(new \Guzzle\Plugin\ErrorResponse\ErrorResponsePlugin());
		$this->client->getCommand("no_class")->execute();
	}
}
?>