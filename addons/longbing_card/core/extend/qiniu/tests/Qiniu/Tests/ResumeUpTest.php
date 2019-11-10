<?php  namespace Qiniu\Tests;
class ResumeUpTest extends \PHPUnit_Framework_TestCase 
{
	protected $bucketName = NULL;
	protected $auth = NULL;
	protected function setUp() 
	{
		global $bucketName;
		$this->bucketName = $bucketName;
		global $testAuth;
		$this->auth = $testAuth;
	}
	public function test4ML() 
	{
		$key = "resumePutFile4ML";
		$upManager = new \Qiniu\Storage\UploadManager();
		$token = $this->auth->uploadToken($this->bucketName, $key);
		$tempFile = qiniuTempFile(4 * 1024 * 1024 + 10);
		list($ret, $error) = $upManager->putFile($token, $key, $tempFile);
		$this->assertNull($error);
		$this->assertNotNull($ret["hash"]);
		unlink($tempFile);
	}
	public function test4ML2() 
	{
		$key = "resumePutFile4ML";
		$zone = new \Qiniu\Zone(array( "up.qiniup.com" ));
		$cfg = new \Qiniu\Config($zone);
		$upManager = new \Qiniu\Storage\UploadManager($cfg);
		$token = $this->auth->uploadToken($this->bucketName, $key);
		$tempFile = qiniuTempFile(4 * 1024 * 1024 + 10);
		list($ret, $error) = $upManager->putFile($token, $key, $tempFile);
		$this->assertNull($error);
		$this->assertNotNull($ret["hash"]);
		unlink($tempFile);
	}
}
?>