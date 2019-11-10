<?php  namespace Guzzle\Tests\Plugin\Backoff;
class HttpBackoffStrategyTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testRetriesWhenCodeMatches() 
	{
		$this->assertNotEmpty(\Guzzle\Plugin\Backoff\HttpBackoffStrategy::getDefaultFailureCodes());
		$strategy = new \Guzzle\Plugin\Backoff\HttpBackoffStrategy();
		$this->assertTrue($strategy->makesDecision());
		$request = $this->getMock("Guzzle\\Http\\Message\\Request", array( ), array( ), "", false);
		$response = new \Guzzle\Http\Message\Response(200);
		$this->assertEquals(false, $strategy->getBackoffPeriod(0, $request, $response));
		$response->setStatus(400);
		$this->assertEquals(false, $strategy->getBackoffPeriod(0, $request, $response));
		foreach( \Guzzle\Plugin\Backoff\HttpBackoffStrategy::getDefaultFailureCodes() as $code ) 
		{
			$this->assertEquals(0, $strategy->getBackoffPeriod(0, $request, $response->setStatus($code)));
		}
	}
	public function testAllowsCustomCodes() 
	{
		$strategy = new \Guzzle\Plugin\Backoff\HttpBackoffStrategy(array( 204 ));
		$request = $this->getMock("Guzzle\\Http\\Message\\Request", array( ), array( ), "", false);
		$response = new \Guzzle\Http\Message\Response(204);
		$this->assertEquals(0, $strategy->getBackoffPeriod(0, $request, $response));
		$response->setStatus(500);
		$this->assertEquals(false, $strategy->getBackoffPeriod(0, $request, $response));
	}
	public function testIgnoresNonErrors() 
	{
		$strategy = new \Guzzle\Plugin\Backoff\HttpBackoffStrategy();
		$request = $this->getMock("Guzzle\\Http\\Message\\Request", array( ), array( ), "", false);
		$this->assertEquals(false, $strategy->getBackoffPeriod(0, $request));
	}
}
?>