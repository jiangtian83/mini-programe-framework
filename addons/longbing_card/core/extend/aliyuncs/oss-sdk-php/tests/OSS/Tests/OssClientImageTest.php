<?php  namespace OSS\Tests;
require_once(__DIR__ . "/Common.php");
class OssClinetImageTest extends \PHPUnit_Framework_TestCase 
{
	private $bucketName = NULL;
	private $client = NULL;
	private $local_file = NULL;
	private $object = NULL;
	private $download_file = NULL;
	public function setUp() 
	{
		$this->client = Common::getOssClient();
		$this->bucketName = "php-sdk-test-bucket-image-" . strval(rand(0, 10000));
		$this->client->createBucket($this->bucketName);
		Common::waitMetaSync();
		$this->local_file = "example.jpg";
		$this->object = "oss-example.jpg";
		$this->download_file = "image.jpg";
		$this->client->uploadFile($this->bucketName, $this->object, $this->local_file);
	}
	public function tearDown() 
	{
		$this->client->deleteObject($this->bucketName, $this->object);
		$this->client->deleteBucket($this->bucketName);
	}
	public function testImageResize() 
	{
		$options = array( \OSS\OssClient::OSS_FILE_DOWNLOAD => $this->download_file, \OSS\OssClient::OSS_PROCESS => "image/resize,m_fixed,h_100,w_100" );
		$this->check($options, 100, 100, 3587, "jpg");
	}
	public function testImageCrop() 
	{
		$options = array( \OSS\OssClient::OSS_FILE_DOWNLOAD => $this->download_file, \OSS\OssClient::OSS_PROCESS => "image/crop,w_100,h_100,x_100,y_100,r_1" );
		$this->check($options, 100, 100, 2281, "jpg");
	}
	public function testImageRotate() 
	{
		$options = array( \OSS\OssClient::OSS_FILE_DOWNLOAD => $this->download_file, \OSS\OssClient::OSS_PROCESS => "image/rotate,90" );
		$this->check($options, 267, 400, 21509, "jpg");
	}
	public function testImageSharpen() 
	{
		$options = array( \OSS\OssClient::OSS_FILE_DOWNLOAD => $this->download_file, \OSS\OssClient::OSS_PROCESS => "image/sharpen,100" );
		$this->check($options, 400, 267, 24183, "jpg");
	}
	public function testImageWatermark() 
	{
		$options = array( \OSS\OssClient::OSS_FILE_DOWNLOAD => $this->download_file, \OSS\OssClient::OSS_PROCESS => "image/watermark,text_SGVsbG8g5Zu-54mH5pyN5YqhIQ" );
		$this->check($options, 400, 267, 26953, "jpg");
	}
	public function testImageFormat() 
	{
		$options = array( \OSS\OssClient::OSS_FILE_DOWNLOAD => $this->download_file, \OSS\OssClient::OSS_PROCESS => "image/format,png" );
		$this->check($options, 400, 267, 160733, "png");
	}
	public function testImageTofile() 
	{
		$options = array( \OSS\OssClient::OSS_FILE_DOWNLOAD => $this->download_file, \OSS\OssClient::OSS_PROCESS => "image/resize,m_fixed,w_100,h_100" );
		$this->check($options, 100, 100, 3587, "jpg");
	}
	private function check($options, $width, $height, $size, $type) 
	{
		$this->client->getObject($this->bucketName, $this->object, $options);
		$array = getimagesize($this->download_file);
		$this->assertEquals($width, $array[0]);
		$this->assertEquals($height, $array[1]);
		$this->assertEquals(($type === "jpg" ? 2 : 3), $array[2]);
	}
}
?>