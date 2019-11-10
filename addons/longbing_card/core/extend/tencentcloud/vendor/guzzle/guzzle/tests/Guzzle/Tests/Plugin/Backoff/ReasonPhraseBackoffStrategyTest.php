<?php  namespace Guzzle\Tests\Plugin\Backoff;
class ReasonPhraseBackoffStrategyTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testRetriesWhenCodeMatches() 
	{
		$this->assertEmpty(\Guzzle\Plugin\Backoff\ReasonPhraseBackoffStrategy::getDefaultFailureCodes());
		$strategy = new \Guzzle\Plugin\Backoff\ReasonPhraseBackoffStrategy(array( "Foo", "Internal Server Error" ));
		$this->assertTrue($strategy->makesDecision());
		$request = $this->getMock("Guzzle\\Http\\Message\\Request", array( ), array( ), "", false);
		$response = new \Guzzle\Http\Message\Response(200);
		$this->assertEquals(false, $strategy->getBackoffPeriod(0, $request, $response));
		$response->setStatus(200, "Foo");
		$this->assertEquals(0, $strategy->getBackoffPeriod(0, $request, $response));
	}
	public function testIgnoresNonErrors() 
	{
		$strategy = new \Guzzle\Plugin\Backoff\ReasonPhraseBackoffStrategy();
		$request = $this->getMock("Guzzle\\Http\\Message\\Request", array( ), array( ), "", false);
		$this->assertEquals(false, $strategy->getBackoffPeriod(0, $request));
	}
}
?>