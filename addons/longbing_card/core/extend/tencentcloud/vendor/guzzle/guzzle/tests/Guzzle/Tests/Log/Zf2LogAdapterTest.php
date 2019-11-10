<?php  namespace Guzzle\Tests\Log;
class Zf2LogAdapterTest extends \Guzzle\Tests\GuzzleTestCase 
{
	protected $adapter = NULL;
	protected $log = NULL;
	protected $stream = NULL;
	protected function setUp() 
	{
		$this->stream = fopen("php://temp", "r+");
		$this->log = new \Zend\Log\Logger();
		$this->log->addWriter(new \Zend\Log\Writer\Stream($this->stream));
		$this->adapter = new \Guzzle\Log\Zf2LogAdapter($this->log);
	}
	public function testLogsMessagesToAdaptedObject() 
	{
		$this->adapter->log("Zend_Test!", LOG_NOTICE);
		rewind($this->stream);
		$contents = stream_get_contents($this->stream);
		$this->assertEquals(1, substr_count($contents, "Zend_Test!"));
		$this->adapter->log("Zend_Test!", LOG_ALERT);
		rewind($this->stream);
		$contents = stream_get_contents($this->stream);
		$this->assertEquals(2, substr_count($contents, "Zend_Test!"));
	}
	public function testExposesAdaptedLogObject() 
	{
		$this->assertEquals($this->log, $this->adapter->getLogObject());
	}
}
?>