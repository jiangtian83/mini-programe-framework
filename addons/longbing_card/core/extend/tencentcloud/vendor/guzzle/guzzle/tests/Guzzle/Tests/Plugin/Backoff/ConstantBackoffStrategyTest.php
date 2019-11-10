<?php  namespace Guzzle\Tests\Plugin\Backoff;
class ConstantBackoffStrategyTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testRetriesWithConstantDelay() 
	{
		$strategy = new \Guzzle\Plugin\Backoff\ConstantBackoffStrategy(3.5);
		$this->assertFalse($strategy->makesDecision());
		$request = $this->getMock("Guzzle\\Http\\Message\\Request", array( ), array( ), "", false);
		$this->assertEquals(3.5, $strategy->getBackoffPeriod(0, $request));
		$this->assertEquals(3.5, $strategy->getBackoffPeriod(1, $request));
	}
}
?>