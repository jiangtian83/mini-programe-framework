<?php  namespace Guzzle\Tests\Service\Command;
class DefaultRequestSerializerTest extends \Guzzle\Tests\GuzzleTestCase 
{
	protected $request = NULL;
	protected $command = NULL;
	protected $client = NULL;
	protected $serializer = NULL;
	protected $operation = NULL;
	public function setUp() 
	{
		$this->serializer = \Guzzle\Service\Command\DefaultRequestSerializer::getInstance();
		$this->client = new \Guzzle\Service\Client("http://foo.com/baz");
		$this->operation = new \Guzzle\Service\Description\Operation(array( "httpMethod" => "POST" ));
		$this->command = $this->getMockBuilder("Guzzle\\Service\\Command\\AbstractCommand")->setConstructorArgs(array( array( ), $this->operation ))->getMockForAbstractClass();
		$this->command->setClient($this->client);
	}
	public function testAllowsCustomVisitor() 
	{
		$this->serializer->addVisitor("custom", new \Guzzle\Service\Command\LocationVisitor\Request\HeaderVisitor());
		$this->command["test"] = "123";
		$this->operation->addParam(new \Guzzle\Service\Description\Parameter(array( "name" => "test", "location" => "custom" )));
		$request = $this->serializer->prepare($this->command);
		$this->assertEquals("123", (string) $request->getHeader("test"));
	}
	public function testUsesRelativePath() 
	{
		$this->operation->setUri("bar");
		$request = $this->serializer->prepare($this->command);
		$this->assertEquals("http://foo.com/baz/bar", (string) $request->getUrl());
	}
	public function testUsesRelativePathWithUriLocations() 
	{
		$this->command["test"] = "123";
		$this->operation->setUri("bar/{test}");
		$this->operation->addParam(new \Guzzle\Service\Description\Parameter(array( "name" => "test", "location" => "uri" )));
		$request = $this->serializer->prepare($this->command);
		$this->assertEquals("http://foo.com/baz/bar/123", (string) $request->getUrl());
	}
	public function testAllowsCustomFactory() 
	{
		$f = new \Guzzle\Service\Command\LocationVisitor\VisitorFlyweight();
		$serializer = new \Guzzle\Service\Command\DefaultRequestSerializer($f);
		$this->assertSame($f, $this->readAttribute($serializer, "factory"));
	}
	public function testMixedParams() 
	{
		$this->operation->setUri("bar{?limit,fields}");
		$this->operation->addParam(new \Guzzle\Service\Description\Parameter(array( "name" => "limit", "location" => "uri", "required" => false )));
		$this->operation->addParam(new \Guzzle\Service\Description\Parameter(array( "name" => "fields", "location" => "uri", "required" => true )));
		$this->command["fields"] = array( "id", "name" );
		$request = $this->serializer->prepare($this->command);
		$this->assertEquals("http://foo.com/baz/bar?fields=" . urlencode("id,name"), (string) $request->getUrl());
	}
	public function testValidatesAdditionalParameters() 
	{
		$description = \Guzzle\Service\Description\ServiceDescription::factory(array( "operations" => array( "foo" => array( "httpMethod" => "PUT", "parameters" => array( "bar" => array( "location" => "header" ) ), "additionalParameters" => array( "location" => "json" ) ) ) ));
		$client = new \Guzzle\Service\Client();
		$client->setDescription($description);
		$command = $client->getCommand("foo");
		$command["bar"] = "test";
		$command["hello"] = "abc";
		$request = $command->prepare();
		$this->assertEquals("test", (string) $request->getHeader("bar"));
		$this->assertEquals("{\"hello\":\"abc\"}", (string) $request->getBody());
	}
}
?>