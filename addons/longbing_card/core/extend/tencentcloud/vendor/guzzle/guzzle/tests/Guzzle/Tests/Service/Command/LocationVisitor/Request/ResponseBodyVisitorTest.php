<?php  namespace Guzzle\Tests\Service\Command\LocationVisitor\Request;
class ResponseBodyVisitorTest extends AbstractVisitorTestCase 
{
	public function testVisitsLocation() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Request\ResponseBodyVisitor();
		$param = $this->getNestedCommand("response_body")->getParam("foo");
		$visitor->visit($this->command, $this->request, $param, sys_get_temp_dir() . "/foo.txt");
		$body = $this->readAttribute($this->request, "responseBody");
		$this->assertContains("/foo.txt", $body->getUri());
	}
}
?>