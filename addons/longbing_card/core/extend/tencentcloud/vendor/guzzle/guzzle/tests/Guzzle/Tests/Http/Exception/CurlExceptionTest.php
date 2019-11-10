<?php  namespace Guzzle\Tests\Http\Exception;
class CurlExceptionTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testStoresCurlError() 
	{
		$e = new \Guzzle\Http\Exception\CurlException();
		$this->assertNull($e->getError());
		$this->assertNull($e->getErrorNo());
		$this->assertSame($e, $e->setError("test", 12));
		$this->assertEquals("test", $e->getError());
		$this->assertEquals(12, $e->getErrorNo());
		$handle = new \Guzzle\Http\Curl\CurlHandle(curl_init(), array( ));
		$e->setCurlHandle($handle);
		$this->assertSame($handle, $e->getCurlHandle());
		$handle->close();
	}
}
?>