<?php  namespace OSS\Tests;
class ListPartsResultTest extends \PHPUnit_Framework_TestCase 
{
	private $validXml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<ListPartsResult xmlns=\"http://doc.oss-cn-hangzhou.aliyuncs.com\">\r\n    <Bucket>multipart_upload</Bucket>\r\n    <Key>multipart.data</Key>\r\n    <UploadId>0004B999EF5A239BB9138C6227D69F95</UploadId>\r\n    <NextPartNumberMarker>5</NextPartNumberMarker>\r\n    <MaxParts>1000</MaxParts>\r\n    <IsTruncated>false</IsTruncated>\r\n    <Part>\r\n        <PartNumber>1</PartNumber>\r\n        <LastModified>2012-02-23T07:01:34.000Z</LastModified>\r\n        <ETag>&quot;3349DC700140D7F86A078484278075A9&quot;</ETag>\r\n        <Size>6291456</Size>\r\n    </Part>\r\n    <Part>\r\n        <PartNumber>2</PartNumber>\r\n        <LastModified>2012-02-23T07:01:12.000Z</LastModified>\r\n        <ETag>&quot;3349DC700140D7F86A078484278075A9&quot;</ETag>\r\n        <Size>6291456</Size>\r\n    </Part>\r\n    <Part>\r\n        <PartNumber>5</PartNumber>\r\n        <LastModified>2012-02-23T07:02:03.000Z</LastModified>\r\n        <ETag>&quot;7265F4D211B56873A381D321F586E4A9&quot;</ETag>\r\n        <Size>1024</Size>\r\n    </Part>\r\n</ListPartsResult>";
	public function testParseValidXml() 
	{
		$response = new \OSS\Http\ResponseCore(array( ), $this->validXml, 200);
		$result = new \OSS\Result\ListPartsResult($response);
		$listPartsInfo = $result->getData();
		$this->assertEquals("multipart_upload", $listPartsInfo->getBucket());
		$this->assertEquals("multipart.data", $listPartsInfo->getKey());
		$this->assertEquals("0004B999EF5A239BB9138C6227D69F95", $listPartsInfo->getUploadId());
		$this->assertEquals(5, $listPartsInfo->getNextPartNumberMarker());
		$this->assertEquals(1000, $listPartsInfo->getMaxParts());
		$this->assertEquals("false", $listPartsInfo->getIsTruncated());
		$this->assertEquals(3, count($listPartsInfo->getListPart()));
		$parts = $listPartsInfo->getListPart();
		$this->assertEquals(1, $parts[0]->getPartNumber());
		$this->assertEquals("2012-02-23T07:01:34.000Z", $parts[0]->getLastModified());
		$this->assertEquals("\"3349DC700140D7F86A078484278075A9\"", $parts[0]->getETag());
		$this->assertEquals(6291456, $parts[0]->getSize());
	}
}