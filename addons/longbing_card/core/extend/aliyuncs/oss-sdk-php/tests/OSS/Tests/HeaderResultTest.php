<?php  namespace OSS\Tests;
class HeaderResultTest extends \PHPUnit_Framework_TestCase 
{
	public function testGetHeader() 
	{
		$response = new \OSS\Http\ResponseCore(array( "key" => "value" ), "", 200);
		$result = new \OSS\Result\HeaderResult($response);
		$this->assertTrue($result->isOK());
		$this->assertTrue(is_array($result->getData()));
		$data = $result->getData();
		$this->assertEquals($data["key"], "value");
	}
}
?>