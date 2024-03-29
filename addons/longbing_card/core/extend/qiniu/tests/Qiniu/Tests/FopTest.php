<?php  namespace Qiniu\Tests;
class FopTest extends \PHPUnit_Framework_TestCase 
{
	public function testExifPub() 
	{
		$fop = new \Qiniu\Processing\Operation("testres.qiniudn.com");
		list($exif, $error) = $fop->execute("gogopher.jpg", "exif");
		$this->assertNull($error);
		$this->assertNotNull($exif);
	}
	public function testExifPrivate() 
	{
		global $testAuth;
		$fop = new \Qiniu\Processing\Operation("private-res.qiniudn.com", $testAuth);
		list($exif, $error) = $fop->execute("noexif.jpg", "exif");
		$this->assertNotNull($error);
		$this->assertNull($exif);
	}
	public function testbuildUrl() 
	{
		$fops = "imageView2/2/h/200";
		$fop = new \Qiniu\Processing\Operation("testres.qiniudn.com");
		$url = $fop->buildUrl("gogopher.jpg", $fops);
		$this->assertEquals($url, "http://testres.qiniudn.com/gogopher.jpg?imageView2/2/h/200");
		$fops = array( "imageView2/2/h/200", "imageInfo" );
		$url = $fop->buildUrl("gogopher.jpg", $fops);
		$this->assertEquals($url, "http://testres.qiniudn.com/gogopher.jpg?imageView2/2/h/200|imageInfo");
	}
}
?>