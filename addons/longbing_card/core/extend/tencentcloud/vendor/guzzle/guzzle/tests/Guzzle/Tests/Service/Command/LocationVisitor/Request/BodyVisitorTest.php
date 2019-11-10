<?php  namespace Guzzle\Tests\Service\Command\LocationVisitor\Request;
class BodyVisitorTest extends AbstractVisitorTestCase 
{
	public function testVisitsLocation() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Request\BodyVisitor();
		$param = $this->getNestedCommand("body")->getParam("foo")->setSentAs("Foo");
		$visitor->visit($this->command, $this->request, $param, "123");
		$this->assertEquals("123", (string) $this->request->getBody());
		$this->assertNull($this->request->getHeader("Expect"));
	}
	public function testAddsExpectHeaderWhenSetToTrue() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Request\BodyVisitor();
		$param = $this->getNestedCommand("body")->getParam("foo")->setSentAs("Foo");
		$param->setData("expect_header", true);
		$visitor->visit($this->command, $this->request, $param, "123");
		$this->assertEquals("123", (string) $this->request->getBody());
	}
	public function testCanDisableExpectHeader() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Request\BodyVisitor();
		$param = $this->getNestedCommand("body")->getParam("foo")->setSentAs("Foo");
		$param->setData("expect_header", false);
		$visitor->visit($this->command, $this->request, $param, "123");
		$this->assertNull($this->request->getHeader("Expect"));
	}
	public function testCanSetExpectHeaderBasedOnSize() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Request\BodyVisitor();
		$param = $this->getNestedCommand("body")->getParam("foo")->setSentAs("Foo");
		$param->setData("expect_header", 5);
		$visitor->visit($this->command, $this->request, $param, "123");
		$this->assertNull($this->request->getHeader("Expect"));
		$param->setData("expect_header", 2);
		$visitor->visit($this->command, $this->request, $param, "123");
		$this->assertEquals("100-Continue", (string) $this->request->getHeader("Expect"));
	}
	public function testAddsContentEncodingWhenSetOnBody() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Request\BodyVisitor();
		$param = $this->getNestedCommand("body")->getParam("foo")->setSentAs("Foo");
		$body = \Guzzle\Http\EntityBody::factory("foo");
		$body->compress();
		$visitor->visit($this->command, $this->request, $param, $body);
		$this->assertEquals("gzip", (string) $this->request->getHeader("Content-Encoding"));
	}
}
?>