<?php  namespace Guzzle\Tests\Log;
class PsrLogAdapterTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testLogsMessagesToAdaptedObject() 
	{
		$log = new \Monolog\Logger("test");
		$handler = new \Monolog\Handler\TestHandler();
		$log->pushHandler($handler);
		$adapter = new \Guzzle\Log\PsrLogAdapter($log);
		$adapter->log("test!", LOG_INFO);
		$this->assertTrue($handler->hasInfoRecords());
		$this->assertSame($log, $adapter->getLogObject());
	}
}
?>