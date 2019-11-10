<?php  namespace OSS\Tests;
class ListObjectsResultTest extends \PHPUnit_Framework_TestCase 
{
	private $validXml1 = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<ListBucketResult>\r\n  <Name>testbucket-hf</Name>\r\n  <Prefix></Prefix>\r\n  <Marker></Marker>\r\n  <MaxKeys>1000</MaxKeys>\r\n  <Delimiter>/</Delimiter>\r\n  <IsTruncated>false</IsTruncated>\r\n  <CommonPrefixes>\r\n    <Prefix>oss-php-sdk-test/</Prefix>\r\n  </CommonPrefixes>\r\n  <CommonPrefixes>\r\n    <Prefix>test/</Prefix>\r\n  </CommonPrefixes>\r\n</ListBucketResult>";
	private $validXml2 = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<ListBucketResult>\r\n  <Name>testbucket-hf</Name>\r\n  <Prefix>oss-php-sdk-test/</Prefix>\r\n  <Marker>xx</Marker>\r\n  <MaxKeys>1000</MaxKeys>\r\n  <Delimiter>/</Delimiter>\r\n  <IsTruncated>false</IsTruncated>\r\n  <Contents>\r\n    <Key>oss-php-sdk-test/upload-test-object-name.txt</Key>\r\n    <LastModified>2015-11-18T03:36:00.000Z</LastModified>\r\n    <ETag>\"89B9E567E7EB8815F2F7D41851F9A2CD\"</ETag>\r\n    <Type>Normal</Type>\r\n    <Size>13115</Size>\r\n    <StorageClass>Standard</StorageClass>\r\n    <Owner>\r\n      <ID>cname_user</ID>\r\n      <DisplayName>cname_user</DisplayName>\r\n    </Owner>\r\n  </Contents>\r\n</ListBucketResult>";
	private $validXmlWithEncodedKey = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<ListBucketResult>\r\n  <Name>testbucket-hf</Name>\r\n  <EncodingType>url</EncodingType>\r\n  <Prefix>php%2Fprefix</Prefix>\r\n  <Marker>php%2Fmarker</Marker>\r\n  <NextMarker>php%2Fnext-marker</NextMarker>\r\n  <MaxKeys>1000</MaxKeys>\r\n  <Delimiter>%2F</Delimiter>\r\n  <IsTruncated>true</IsTruncated>\r\n  <Contents>\r\n    <Key>php/a%2Bb</Key>\r\n    <LastModified>2015-11-18T03:36:00.000Z</LastModified>\r\n    <ETag>\"89B9E567E7EB8815F2F7D41851F9A2CD\"</ETag>\r\n    <Type>Normal</Type>\r\n    <Size>13115</Size>\r\n    <StorageClass>Standard</StorageClass>\r\n    <Owner>\r\n      <ID>cname_user</ID>\r\n      <DisplayName>cname_user</DisplayName>\r\n    </Owner>\r\n  </Contents>\r\n</ListBucketResult>";
	public function testParseValidXml1() 
	{
		$response = new \OSS\Http\ResponseCore(array( ), $this->validXml1, 200);
		$result = new \OSS\Result\ListObjectsResult($response);
		$this->assertTrue($result->isOK());
		$this->assertNotNull($result->getData());
		$this->assertNotNull($result->getRawResponse());
		$objectListInfo = $result->getData();
		$this->assertEquals(2, count($objectListInfo->getPrefixList()));
		$this->assertEquals(0, count($objectListInfo->getObjectList()));
		$this->assertEquals("testbucket-hf", $objectListInfo->getBucketName());
		$this->assertEquals("", $objectListInfo->getPrefix());
		$this->assertEquals("", $objectListInfo->getMarker());
		$this->assertEquals(1000, $objectListInfo->getMaxKeys());
		$this->assertEquals("/", $objectListInfo->getDelimiter());
		$this->assertEquals("false", $objectListInfo->getIsTruncated());
		$prefixes = $objectListInfo->getPrefixList();
		$this->assertEquals("oss-php-sdk-test/", $prefixes[0]->getPrefix());
		$this->assertEquals("test/", $prefixes[1]->getPrefix());
	}
	public function testParseValidXml2() 
	{
		$response = new \OSS\Http\ResponseCore(array( ), $this->validXml2, 200);
		$result = new \OSS\Result\ListObjectsResult($response);
		$this->assertTrue($result->isOK());
		$this->assertNotNull($result->getData());
		$this->assertNotNull($result->getRawResponse());
		$objectListInfo = $result->getData();
		$this->assertEquals(0, count($objectListInfo->getPrefixList()));
		$this->assertEquals(1, count($objectListInfo->getObjectList()));
		$this->assertEquals("testbucket-hf", $objectListInfo->getBucketName());
		$this->assertEquals("oss-php-sdk-test/", $objectListInfo->getPrefix());
		$this->assertEquals("xx", $objectListInfo->getMarker());
		$this->assertEquals(1000, $objectListInfo->getMaxKeys());
		$this->assertEquals("/", $objectListInfo->getDelimiter());
		$this->assertEquals("false", $objectListInfo->getIsTruncated());
		$objects = $objectListInfo->getObjectList();
		$this->assertEquals("oss-php-sdk-test/upload-test-object-name.txt", $objects[0]->getKey());
		$this->assertEquals("2015-11-18T03:36:00.000Z", $objects[0]->getLastModified());
		$this->assertEquals("\"89B9E567E7EB8815F2F7D41851F9A2CD\"", $objects[0]->getETag());
		$this->assertEquals("Normal", $objects[0]->getType());
		$this->assertEquals(13115, $objects[0]->getSize());
		$this->assertEquals("Standard", $objects[0]->getStorageClass());
	}
	public function testParseValidXmlWithEncodedKey() 
	{
		$response = new \OSS\Http\ResponseCore(array( ), $this->validXmlWithEncodedKey, 200);
		$result = new \OSS\Result\ListObjectsResult($response);
		$this->assertTrue($result->isOK());
		$this->assertNotNull($result->getData());
		$this->assertNotNull($result->getRawResponse());
		$objectListInfo = $result->getData();
		$this->assertEquals(0, count($objectListInfo->getPrefixList()));
		$this->assertEquals(1, count($objectListInfo->getObjectList()));
		$this->assertEquals("testbucket-hf", $objectListInfo->getBucketName());
		$this->assertEquals("php/prefix", $objectListInfo->getPrefix());
		$this->assertEquals("php/marker", $objectListInfo->getMarker());
		$this->assertEquals("php/next-marker", $objectListInfo->getNextMarker());
		$this->assertEquals(1000, $objectListInfo->getMaxKeys());
		$this->assertEquals("/", $objectListInfo->getDelimiter());
		$this->assertEquals("true", $objectListInfo->getIsTruncated());
		$objects = $objectListInfo->getObjectList();
		$this->assertEquals("php/a+b", $objects[0]->getKey());
		$this->assertEquals("2015-11-18T03:36:00.000Z", $objects[0]->getLastModified());
		$this->assertEquals("\"89B9E567E7EB8815F2F7D41851F9A2CD\"", $objects[0]->getETag());
		$this->assertEquals("Normal", $objects[0]->getType());
		$this->assertEquals(13115, $objects[0]->getSize());
		$this->assertEquals("Standard", $objects[0]->getStorageClass());
	}
}