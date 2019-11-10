<?php  namespace Guzzle\Tests\Service\Command\LocationVisitor\Response;
class HeaderVisitorTest extends AbstractResponseVisitorTest 
{
	public function testVisitsLocation() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Response\HeaderVisitor();
		$param = new \Guzzle\Service\Description\Parameter(array( "location" => "header", "name" => "ContentType", "sentAs" => "Content-Type" ));
		$visitor->visit($this->command, $this->response, $param, $this->value);
		$this->assertEquals("text/plain", $this->value["ContentType"]);
	}
	public function testVisitsLocationWithFilters() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Response\HeaderVisitor();
		$param = new \Guzzle\Service\Description\Parameter(array( "location" => "header", "name" => "Content-Type", "filters" => array( "strtoupper" ) ));
		$visitor->visit($this->command, $this->response, $param, $this->value);
		$this->assertEquals("TEXT/PLAIN", $this->value["Content-Type"]);
	}
	public function testVisitsMappedPrefixHeaders() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Response\HeaderVisitor();
		$param = new \Guzzle\Service\Description\Parameter(array( "location" => "header", "name" => "Metadata", "sentAs" => "X-Baz-", "type" => "object", "additionalProperties" => array( "type" => "string" ) ));
		$response = new \Guzzle\Http\Message\Response(200, array( "X-Baz-Test" => "ABC", "X-Baz-Bar" => array( "123", "456" ), "Content-Length" => 3 ), "Foo");
		$visitor->visit($this->command, $response, $param, $this->value);
		$this->assertEquals(array( "Metadata" => array( "Test" => "ABC", "Bar" => array( "123", "456" ) ) ), $this->value);
	}
	public function testDiscardingUnknownHeaders() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Response\HeaderVisitor();
		$param = new \Guzzle\Service\Description\Parameter(array( "location" => "header", "name" => "Content-Type", "additionalParameters" => false ));
		$visitor->visit($this->command, $this->response, $param, $this->value);
		$this->assertEquals("text/plain", $this->value["Content-Type"]);
		$this->assertArrayNotHasKey("X-Foo", $this->value);
	}
	public function testDiscardingUnknownPropertiesWithAliasing() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Response\HeaderVisitor();
		$param = new \Guzzle\Service\Description\Parameter(array( "location" => "header", "name" => "ContentType", "sentAs" => "Content-Type", "additionalParameters" => false ));
		$visitor->visit($this->command, $this->response, $param, $this->value);
		$this->assertEquals("text/plain", $this->value["ContentType"]);
		$this->assertArrayNotHasKey("X-Foo", $this->value);
	}
}
?>