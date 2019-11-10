<?php  namespace Guzzle\Tests\Service\Command\LocationVisitor\Response;
class StatusCodeVisitorTest extends AbstractResponseVisitorTest 
{
	public function testVisitsLocation() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Response\StatusCodeVisitor();
		$param = new \Guzzle\Service\Description\Parameter(array( "location" => "statusCode", "name" => "code" ));
		$visitor->visit($this->command, $this->response, $param, $this->value);
		$this->assertEquals(200, $this->value["code"]);
	}
}
?>