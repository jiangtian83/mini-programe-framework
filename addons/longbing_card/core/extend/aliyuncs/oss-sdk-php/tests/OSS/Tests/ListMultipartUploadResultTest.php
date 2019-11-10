<?php  namespace OSS\Tests;
class ListMultipartUploadResultTest extends \PHPUnit_Framework_TestCase 
{
	private $validXml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<ListMultipartUploadsResult xmlns=\"http://doc.oss-cn-hangzhou.aliyuncs.com\">\r\n    <Bucket>oss-example</Bucket>\r\n    <KeyMarker>xx</KeyMarker>\r\n    <UploadIdMarker>3</UploadIdMarker>\r\n    <NextKeyMarker>oss.avi</NextKeyMarker>\r\n    <NextUploadIdMarker>0004B99B8E707874FC2D692FA5D77D3F</NextUploadIdMarker>\r\n    <Delimiter>x</Delimiter>\r\n    <Prefix>xx</Prefix>\r\n    <MaxUploads>1000</MaxUploads>\r\n    <IsTruncated>false</IsTruncated>\r\n    <Upload>\r\n        <Key>multipart.data</Key>\r\n        <UploadId>0004B999EF518A1FE585B0C9360DC4C8</UploadId>\r\n        <Initiated>2012-02-23T04:18:23.000Z</Initiated>\r\n    </Upload>\r\n    <Upload>\r\n        <Key>multipart.data</Key>\r\n        <UploadId>0004B999EF5A239BB9138C6227D69F95</UploadId>\r\n        <Initiated>2012-02-23T04:18:23.000Z</Initiated>\r\n    </Upload>\r\n    <Upload>\r\n        <Key>oss.avi</Key>\r\n        <UploadId>0004B99B8E707874FC2D692FA5D77D3F</UploadId>\r\n        <Initiated>2012-02-23T06:14:27.000Z</Initiated>\r\n    </Upload>\r\n</ListMultipartUploadsResult>";
	private $validXmlWithEncodedKey = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<ListMultipartUploadsResult xmlns=\"http://doc.oss-cn-hangzhou.aliyuncs.com\">\r\n    <Bucket>oss-example</Bucket>\r\n    <EncodingType>url</EncodingType>\r\n    <KeyMarker>php%2Bkey-marker</KeyMarker>\r\n    <UploadIdMarker>3</UploadIdMarker>\r\n    <NextKeyMarker>php%2Bnext-key-marker</NextKeyMarker>\r\n    <NextUploadIdMarker>0004B99B8E707874FC2D692FA5D77D3F</NextUploadIdMarker>\r\n    <Delimiter>%2F</Delimiter>\r\n    <Prefix>php%2Bprefix</Prefix>\r\n    <MaxUploads>1000</MaxUploads>\r\n    <IsTruncated>true</IsTruncated>\r\n    <Upload>\r\n        <Key>php%2Bkey-1</Key>\r\n        <UploadId>0004B999EF518A1FE585B0C9360DC4C8</UploadId>\r\n        <Initiated>2012-02-23T04:18:23.000Z</Initiated>\r\n    </Upload>\r\n    <Upload>\r\n        <Key>php%2Bkey-2</Key>\r\n        <UploadId>0004B999EF5A239BB9138C6227D69F95</UploadId>\r\n        <Initiated>2012-02-23T04:18:23.000Z</Initiated>\r\n    </Upload>\r\n    <Upload>\r\n        <Key>php%2Bkey-3</Key>\r\n        <UploadId>0004B99B8E707874FC2D692FA5D77D3F</UploadId>\r\n        <Initiated>2012-02-23T06:14:27.000Z</Initiated>\r\n    </Upload>\r\n</ListMultipartUploadsResult>";
	public function testParseValidXml() 
	{
		$response = new \OSS\Http\ResponseCore(array( ), $this->validXml, 200);
		$result = new \OSS\Result\ListMultipartUploadResult($response);
		$listMultipartUploadInfo = $result->getData();
		$this->assertEquals("oss-example", $listMultipartUploadInfo->getBucket());
		$this->assertEquals("xx", $listMultipartUploadInfo->getKeyMarker());
		$this->assertEquals(3, $listMultipartUploadInfo->getUploadIdMarker());
		$this->assertEquals("oss.avi", $listMultipartUploadInfo->getNextKeyMarker());
		$this->assertEquals("0004B99B8E707874FC2D692FA5D77D3F", $listMultipartUploadInfo->getNextUploadIdMarker());
		$this->assertEquals("x", $listMultipartUploadInfo->getDelimiter());
		$this->assertEquals("xx", $listMultipartUploadInfo->getPrefix());
		$this->assertEquals(1000, $listMultipartUploadInfo->getMaxUploads());
		$this->assertEquals("false", $listMultipartUploadInfo->getIsTruncated());
		$uploads = $listMultipartUploadInfo->getUploads();
		$this->assertEquals("multipart.data", $uploads[0]->getKey());
		$this->assertEquals("0004B999EF518A1FE585B0C9360DC4C8", $uploads[0]->getUploadId());
		$this->assertEquals("2012-02-23T04:18:23.000Z", $uploads[0]->getInitiated());
	}
	public function testParseValidXmlWithEncodedKey() 
	{
		$response = new \OSS\Http\ResponseCore(array( ), $this->validXmlWithEncodedKey, 200);
		$result = new \OSS\Result\ListMultipartUploadResult($response);
		$listMultipartUploadInfo = $result->getData();
		$this->assertEquals("oss-example", $listMultipartUploadInfo->getBucket());
		$this->assertEquals("php+key-marker", $listMultipartUploadInfo->getKeyMarker());
		$this->assertEquals("php+next-key-marker", $listMultipartUploadInfo->getNextKeyMarker());
		$this->assertEquals(3, $listMultipartUploadInfo->getUploadIdMarker());
		$this->assertEquals("0004B99B8E707874FC2D692FA5D77D3F", $listMultipartUploadInfo->getNextUploadIdMarker());
		$this->assertEquals("/", $listMultipartUploadInfo->getDelimiter());
		$this->assertEquals("php+prefix", $listMultipartUploadInfo->getPrefix());
		$this->assertEquals(1000, $listMultipartUploadInfo->getMaxUploads());
		$this->assertEquals("true", $listMultipartUploadInfo->getIsTruncated());
		$uploads = $listMultipartUploadInfo->getUploads();
		$this->assertEquals("php+key-1", $uploads[0]->getKey());
		$this->assertEquals("0004B999EF518A1FE585B0C9360DC4C8", $uploads[0]->getUploadId());
		$this->assertEquals("2012-02-23T04:18:23.000Z", $uploads[0]->getInitiated());
	}
}