<?php  namespace Qiniu\Tests;
class ZoneTest extends \PHPUnit_Framework_TestCase 
{
	protected $zone = NULL;
	protected $zoneHttps = NULL;
	protected $ak = NULL;
	protected $bucketName = NULL;
	protected $bucketNameBC = NULL;
	protected $bucketNameNA = NULL;
	protected function setUp() 
	{
		global $bucketName;
		$this->bucketName = $bucketName;
		global $bucketNameBC;
		$this->bucketNameBC = $bucketNameBC;
		global $bucketNameNA;
		$this->bucketNameNA = $bucketNameNA;
		global $accessKey;
		$this->ak = $accessKey;
		$this->zone = new \Qiniu\Zone();
		$this->zoneHttps = new \Qiniu\Zone("https");
	}
	public function testUpHosts() 
	{
		$zone = \Qiniu\Zone::queryZone($this->ak, $this->bucketName);
		$this->assertContains("upload.qiniup.com", $zone->cdnUpHosts);
		$zone = \Qiniu\Zone::queryZone($this->ak, $this->bucketNameBC);
		$this->assertContains("upload-z1.qiniup.com", $zone->cdnUpHosts);
		$zone = \Qiniu\Zone::queryZone($this->ak, $this->bucketNameNA);
		$this->assertContains("upload-na0.qiniup.com", $zone->cdnUpHosts);
	}
	public function testIoHosts() 
	{
		$zone = \Qiniu\Zone::queryZone($this->ak, $this->bucketName);
		$this->assertEquals($zone->iovipHost, "iovip.qbox.me");
		$zone = \Qiniu\Zone::queryZone($this->ak, $this->bucketNameBC);
		$this->assertEquals($zone->iovipHost, "iovip-z1.qbox.me");
		$zone = \Qiniu\Zone::queryZone($this->ak, $this->bucketNameNA);
		$this->assertEquals($zone->iovipHost, "iovip-na0.qbox.me");
	}
}
?>