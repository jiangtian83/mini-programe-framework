<?php  namespace Guzzle\Tests\Service\Command\LocationVisitor\Request;
class HeaderVisitorTest extends AbstractVisitorTestCase 
{
	public function testValidatesHeaderMapsAreArrays() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Request\HeaderVisitor();
		$param = $this->getNestedCommand("header")->getParam("foo")->setSentAs("test");
		$param->setAdditionalProperties(new \Guzzle\Service\Description\Parameter(array( )));
		$visitor->visit($this->command, $this->request, $param, "test");
	}
	public function testVisitsLocation() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Request\HeaderVisitor();
		$param = $this->getNestedCommand("header")->getParam("foo")->setSentAs("test");
		$param->setAdditionalProperties(false);
		$visitor->visit($this->command, $this->request, $param, "123");
		$this->assertEquals("123", (string) $this->request->getHeader("test"));
	}
	public function testVisitsMappedPrefixHeaders() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Request\HeaderVisitor();
		$param = $this->getNestedCommand("header")->getParam("foo")->setSentAs("test");
		$param->setSentAs("x-foo-");
		$param->setAdditionalProperties(new \Guzzle\Service\Description\Parameter(array( "type" => "string" )));
		$visitor->visit($this->command, $this->request, $param, array( "bar" => "test", "baz" => "123" ));
		$this->assertEquals("test", (string) $this->request->getHeader("x-foo-bar"));
		$this->assertEquals("123", (string) $this->request->getHeader("x-foo-baz"));
	}
}
?>