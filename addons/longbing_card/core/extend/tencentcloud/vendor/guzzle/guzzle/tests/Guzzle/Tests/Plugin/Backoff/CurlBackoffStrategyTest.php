<?php  namespace Guzzle\Tests\Plugin\Backoff;
class CurlBackoffStrategyTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testRetriesWithExponentialDelay() 
	{
		$this->assertNotEmpty(\Guzzle\Plugin\Backoff\CurlBackoffStrategy::getDefaultFailureCodes());
		$strategy = new \Guzzle\Plugin\Backoff\CurlBackoffStrategy();
		$this->assertTrue($strategy->makesDecision());
		$request = $this->getMock("Guzzle\\Http\\Message\\Request", array( ), array( ), "", false);
		$e = new \Guzzle\Http\Exception\CurlException();
		$e->setError("foo", CURLE_BAD_CALLING_ORDER);
		$this->assertEquals(false, $strategy->getBackoffPeriod(0, $request, null, $e));
		foreach( \Guzzle\Plugin\Backoff\CurlBackoffStrategy::getDefaultFailureCodes() as $code ) 
		{
			$this->assertEquals(0, $strategy->getBackoffPeriod(0, $request, null, $e->setError("foo", $code)));
		}
	}
	public function testIgnoresNonErrors() 
	{
		$strategy = new \Guzzle\Plugin\Backoff\CurlBackoffStrategy();
		$request = $this->getMock("Guzzle\\Http\\Message\\Request", array( ), array( ), "", false);
		$this->assertEquals(false, $strategy->getBackoffPeriod(0, $request, new \Guzzle\Http\Message\Response(200)));
	}
}
?>