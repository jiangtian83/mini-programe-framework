<?php  namespace Qiniu\Tests;
class CdnManagerTest extends \PHPUnit_Framework_TestCase 
{
	protected $cdnManager = NULL;
	protected $encryptKey = NULL;
	protected $imgUrl = NULL;
	protected function setUp() 
	{
		global $timestampAntiLeechEncryptKey;
		global $customDomain;
		global $testAuth;
		$this->cdnManager = new \Qiniu\Cdn\CdnManager($testAuth);
		$this->encryptKey = $timestampAntiLeechEncryptKey;
		$this->imgUrl = $customDomain . "/24.jpg";
	}
	public function testCreateTimestampAntiLeechUrl() 
	{
		$signUrl = $this->cdnManager->createTimestampAntiLeechUrl($this->imgUrl, $this->encryptKey, 3600);
		$response = \Qiniu\Http\Client::get($signUrl);
		$this->assertEquals($response->statusCode, 200);
		$this->assertNull($response->error);
		$url2 = $this->imgUrl . "?imageInfo";
		$signUrl2 = $this->cdnManager->createTimestampAntiLeechUrl($url2, $this->encryptKey, 3600);
		$response = \Qiniu\Http\Client::get($signUrl2);
		$imgInfo = $response->json();
		$this->assertEquals($response->statusCode, 200);
		$this->assertEquals($imgInfo["size"], 2196145);
		$this->assertNull($response->error);
	}
}
?>