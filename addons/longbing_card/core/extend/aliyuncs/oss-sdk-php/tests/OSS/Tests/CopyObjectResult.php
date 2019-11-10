<?php  namespace OSS\Tests;
class CopyObjectResultTest extends \PHPUnit_Framework_TestCase 
{
	private $body = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n<CopyObjectResult>\r\n  <LastModified>Fri, 24 Feb 2012 07:18:48 GMT</LastModified>\r\n  <ETag>\"5B3C1A2E053D763E1B002CC607C5A0FE\"</ETag>\r\n</CopyObjectResult>";
	public function testNullResponse() 
	{
		$response = null;
		try 
		{
			new \OSS\Result\CopyObjectResult($response);
			$this->assertFalse(true);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertEquals("raw response is null", $e->getMessage());
		}
	}
	public function testOkResponse() 
	{
		$header = array( );
		$response = new \OSS\Http\ResponseCore($header, $this->body, 200);
		$result = new \OSS\Result\CopyObjectResult($response);
		$data = $result->getData();
		$this->assertTrue($result->isOK());
		$this->assertEquals("Fri, 24 Feb 2012 07:18:48 GMT", $data[0]);
		$this->assertEquals("\"5B3C1A2E053D763E1B002CC607C5A0FE\"", $data[1]);
	}
	public function testFailResponse() 
	{
		$response = new \OSS\Http\ResponseCore(array( ), "", 404);
		try 
		{
			new \OSS\Result\CopyObjectResult($response);
			$this->assertFalse(true);
		}
		catch( \OSS\Core\OssException $e ) 
		{
		}
	}
}