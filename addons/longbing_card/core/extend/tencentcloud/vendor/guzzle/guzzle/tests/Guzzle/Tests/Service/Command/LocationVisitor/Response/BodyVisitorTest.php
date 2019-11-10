<?php  namespace Guzzle\Tests\Service\Command\LocationVisitor\Response;
class BodyVisitorTest extends AbstractResponseVisitorTest 
{
	public function testVisitsLocation() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Response\BodyVisitor();
		$param = new \Guzzle\Service\Description\Parameter(array( "location" => "body", "name" => "foo" ));
		$visitor->visit($this->command, $this->response, $param, $this->value);
		$this->assertEquals("Foo", (string) $this->value["foo"]);
	}
}
?>