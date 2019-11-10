<?php  namespace Qiniu\Tests;
class FormUpTest extends \PHPUnit_Framework_TestCase 
{
	protected $bucketName = NULL;
	protected $auth = NULL;
	protected $cfg = NULL;
	protected function setUp() 
	{
		global $bucketName;
		$this->bucketName = $bucketName;
		global $testAuth;
		$this->auth = $testAuth;
		$this->cfg = new \Qiniu\Config();
	}
	public function testData() 
	{
		$token = $this->auth->uploadToken($this->bucketName);
		list($ret, $error) = \Qiniu\Storage\FormUploader::put($token, "formput", "hello world", $this->cfg, null, "text/plain", null);
		$this->assertNull($error);
		$this->assertNotNull($ret["hash"]);
	}
	public function testData2() 
	{
		$upManager = new \Qiniu\Storage\UploadManager();
		$token = $this->auth->uploadToken($this->bucketName);
		list($ret, $error) = $upManager->put($token, "formput", "hello world", null, "text/plain", null);
		$this->assertNull($error);
		$this->assertNotNull($ret["hash"]);
	}
	public function testFile() 
	{
		$key = "formPutFile";
		$token = $this->auth->uploadToken($this->bucketName, $key);
		list($ret, $error) = \Qiniu\Storage\FormUploader::putFile($token, $key, __FILE__, $this->cfg, null, "text/plain", null);
		$this->assertNull($error);
		$this->assertNotNull($ret["hash"]);
	}
	public function testFile2() 
	{
		$key = "formPutFile";
		$token = $this->auth->uploadToken($this->bucketName, $key);
		$upManager = new \Qiniu\Storage\UploadManager();
		list($ret, $error) = $upManager->putFile($token, $key, __FILE__, null, "text/plain", null);
		$this->assertNull($error);
		$this->assertNotNull($ret["hash"]);
	}
}
?>