<?php  namespace OSS\Tests;
class OssExceptionTest extends \PHPUnit_Framework_TestCase 
{
	public function testOSS_exception() 
	{
		try 
		{
			throw new \OSS\Core\OssException("ERR");
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertNotNull($e);
			$this->assertEquals($e->getMessage(), "ERR");
		}
	}
}
?>