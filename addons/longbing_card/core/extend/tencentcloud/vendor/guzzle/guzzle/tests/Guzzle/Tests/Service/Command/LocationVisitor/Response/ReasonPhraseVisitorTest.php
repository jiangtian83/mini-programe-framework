<?php  namespace Guzzle\Tests\Service\Command\LocationVisitor\Response;
class ReasonPhraseVisitorTest extends AbstractResponseVisitorTest 
{
	public function testVisitsLocation() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Response\ReasonPhraseVisitor();
		$param = new \Guzzle\Service\Description\Parameter(array( "location" => "reasonPhrase", "name" => "phrase" ));
		$visitor->visit($this->command, $this->response, $param, $this->value);
		$this->assertEquals("OK", $this->value["phrase"]);
	}
}
?>