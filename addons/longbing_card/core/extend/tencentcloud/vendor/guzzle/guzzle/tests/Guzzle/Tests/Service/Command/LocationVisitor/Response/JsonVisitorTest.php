<?php  namespace Guzzle\Tests\Service\Command\LocationVisitor\Response;
class JsonVisitorTest extends AbstractResponseVisitorTest 
{
	public function testBeforeMethodParsesXml() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Response\JsonVisitor();
		$command = $this->getMockBuilder("Guzzle\\Service\\Command\\AbstractCommand")->setMethods(array( "getResponse" ))->getMockForAbstractClass();
		$command->expects($this->once())->method("getResponse")->will($this->returnValue(new \Guzzle\Http\Message\Response(200, null, "{\"foo\":\"bar\"}")));
		$result = array( );
		$visitor->before($command, $result);
		$this->assertEquals(array( "foo" => "bar" ), $result);
	}
	public function testVisitsLocation() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Response\JsonVisitor();
		$param = new \Guzzle\Service\Description\Parameter(array( "name" => "foo", "type" => "array", "items" => array( "filters" => "strtoupper", "type" => "string" ) ));
		$this->value = array( "foo" => array( "a", "b", "c" ) );
		$visitor->visit($this->command, $this->response, $param, $this->value);
		$this->assertEquals(array( "A", "B", "C" ), $this->value["foo"]);
	}
	public function testRenamesTopLevelValues() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Response\JsonVisitor();
		$param = new \Guzzle\Service\Description\Parameter(array( "name" => "foo", "sentAs" => "Baz", "type" => "string" ));
		$this->value = array( "Baz" => "test" );
		$visitor->visit($this->command, $this->response, $param, $this->value);
		$this->assertEquals(array( "foo" => "test" ), $this->value);
	}
	public function testRenamesDoesNotFailForNonExistentKey() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Response\JsonVisitor();
		$param = new \Guzzle\Service\Description\Parameter(array( "name" => "foo", "type" => "object", "properties" => array( "bar" => array( "name" => "bar", "sentAs" => "baz" ) ) ));
		$this->value = array( "foo" => array( "unknown" => "Unknown" ) );
		$visitor->visit($this->command, $this->response, $param, $this->value);
		$this->assertEquals(array( "foo" => array( "unknown" => "Unknown" ) ), $this->value);
	}
	public function testTraversesObjectsAndAppliesFilters() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Response\JsonVisitor();
		$param = new \Guzzle\Service\Description\Parameter(array( "name" => "foo", "type" => "object", "properties" => array( "foo" => array( "filters" => "strtoupper" ), "bar" => array( "filters" => "strtolower" ) ) ));
		$this->value = array( "foo" => array( "foo" => "hello", "bar" => "THERE" ) );
		$visitor->visit($this->command, $this->response, $param, $this->value);
		$this->assertEquals(array( "foo" => "HELLO", "bar" => "there" ), $this->value["foo"]);
	}
	public function testDiscardingUnknownProperties() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Response\JsonVisitor();
		$param = new \Guzzle\Service\Description\Parameter(array( "name" => "foo", "type" => "object", "additionalProperties" => false, "properties" => array( "bar" => array( "type" => "string", "name" => "bar" ) ) ));
		$this->value = array( "foo" => array( "bar" => 15, "unknown" => "Unknown" ) );
		$visitor->visit($this->command, $this->response, $param, $this->value);
		$this->assertEquals(array( "foo" => array( "bar" => 15 ) ), $this->value);
	}
	public function testDiscardingUnknownPropertiesWithAliasing() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Response\JsonVisitor();
		$param = new \Guzzle\Service\Description\Parameter(array( "name" => "foo", "type" => "object", "additionalProperties" => false, "properties" => array( "bar" => array( "name" => "bar", "sentAs" => "baz" ) ) ));
		$this->value = array( "foo" => array( "baz" => 15, "unknown" => "Unknown" ) );
		$visitor->visit($this->command, $this->response, $param, $this->value);
		$this->assertEquals(array( "foo" => array( "bar" => 15 ) ), $this->value);
	}
	public function testWalksAdditionalProperties() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Response\JsonVisitor();
		$param = new \Guzzle\Service\Description\Parameter(array( "name" => "foo", "type" => "object", "additionalProperties" => array( "type" => "object", "properties" => array( "bar" => array( "type" => "string", "filters" => array( "base64_decode" ) ) ) ) ));
		$this->value = array( "foo" => array( "baz" => array( "bar" => "Zm9v" ) ) );
		$visitor->visit($this->command, $this->response, $param, $this->value);
		$this->assertEquals("foo", $this->value["foo"]["baz"]["bar"]);
	}
}
?>