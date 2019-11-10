<?php  namespace OSS\Tests;
require_once(__DIR__ . DIRECTORY_SEPARATOR . "TestOssClientBase.php");
class OssClientObjectTest extends TestOssClientBase 
{
	public function testGetObjectMeta() 
	{
		$object = "oss-php-sdk-test/upload-test-object-name.txt";
		try 
		{
			$res = $this->ossClient->getObjectMeta($this->bucket, $object);
			$this->assertEquals("200", $res["info"]["http_code"]);
			$this->assertEquals("text/plain", $res["content-type"]);
			$this->assertEquals("Accept-Encoding", $res["vary"]);
			$this->assertTrue(isset($res["content-length"]));
			$this->assertFalse(isset($res["content-encoding"]));
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertTrue(false);
		}
		$options = array( \OSS\OssClient::OSS_HEADERS => array( \OSS\OssClient::OSS_ACCEPT_ENCODING => "deflate, gzip" ) );
		try 
		{
			$res = $this->ossClient->getObjectMeta($this->bucket, $object, $options);
			$this->assertEquals("200", $res["info"]["http_code"]);
			$this->assertEquals("text/plain", $res["content-type"]);
			$this->assertEquals("Accept-Encoding", $res["vary"]);
			$this->assertFalse(isset($res["content-length"]));
			$this->assertEquals("gzip", $res["content-encoding"]);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertTrue(false);
		}
	}
	public function testGetObjectWithAcceptEncoding() 
	{
		$object = "oss-php-sdk-test/upload-test-object-name.txt";
		$options = array( \OSS\OssClient::OSS_HEADERS => array( \OSS\OssClient::OSS_ACCEPT_ENCODING => "deflate, gzip" ) );
		try 
		{
			$res = $this->ossClient->getObject($this->bucket, $object, $options);
			$this->assertEquals(file_get_contents(__FILE__), $res);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertTrue(false);
		}
	}
	public function testGetObjectWithHeader() 
	{
		$object = "oss-php-sdk-test/upload-test-object-name.txt";
		try 
		{
			$res = $this->ossClient->getObject($this->bucket, $object, array( \OSS\OssClient::OSS_LAST_MODIFIED => "xx" ));
			$this->assertEquals(file_get_contents(__FILE__), $res);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertEquals("\"/ilegal.txt\" object name is invalid", $e->getMessage());
		}
	}
	public function testGetObjectWithIleggalEtag() 
	{
		$object = "oss-php-sdk-test/upload-test-object-name.txt";
		try 
		{
			$res = $this->ossClient->getObject($this->bucket, $object, array( \OSS\OssClient::OSS_ETAG => "xx" ));
			$this->assertEquals(file_get_contents(__FILE__), $res);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertEquals("\"/ilegal.txt\" object name is invalid", $e->getMessage());
		}
	}
	public function testObject() 
	{
		$object = "oss-php-sdk-test/upload-test-object-name.txt";
		$content = file_get_contents(__FILE__);
		$options = array( \OSS\OssClient::OSS_LENGTH => strlen($content), \OSS\OssClient::OSS_HEADERS => array( "Expires" => "Fri, 28 Feb 2020 05:38:42 GMT", "Cache-Control" => "no-cache", "Content-Disposition" => "attachment;filename=oss_download.log", "Content-Encoding" => "utf-8", "Content-Language" => "zh-CN", "x-oss-server-side-encryption" => "AES256", "x-oss-meta-self-define-title" => "user define meta info" ) );
		try 
		{
			$this->ossClient->putObject($this->bucket, $object, $content, $options);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		try 
		{
			$this->ossClient->putObject($this->bucket, $object, $content, $options);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		try 
		{
			$result = $this->ossClient->deleteObjects($this->bucket, "stringtype", $options);
			$this->assertEquals("stringtype", $result[0]);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertEquals("objects must be array", $e->getMessage());
		}
		try 
		{
			$result = $this->ossClient->deleteObjects($this->bucket, "stringtype", $options);
			$this->assertFalse(true);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertEquals("objects must be array", $e->getMessage());
		}
		try 
		{
			$this->ossClient->uploadFile($this->bucket, $object, "notexist.txt", $options);
			$this->assertFalse(true);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertEquals("notexist.txt file does not exist", $e->getMessage());
		}
		try 
		{
			$content = $this->ossClient->getObject($this->bucket, $object);
			$this->assertEquals($content, file_get_contents(__FILE__));
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		try 
		{
			$options = array( \OSS\OssClient::OSS_RANGE => "0-4" );
			$content = $this->ossClient->getObject($this->bucket, $object, $options);
			$this->assertEquals($content, "<?php");
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		try 
		{
			$this->ossClient->uploadFile($this->bucket, $object, __FILE__);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		try 
		{
			$content = $this->ossClient->getObject($this->bucket, $object);
			$this->assertEquals($content, file_get_contents(__FILE__));
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		$localfile = "upload-test-object-name.txt";
		$options = array( \OSS\OssClient::OSS_FILE_DOWNLOAD => $localfile );
		try 
		{
			$this->ossClient->getObject($this->bucket, $object, $options);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		$this->assertTrue(file_get_contents($localfile) === file_get_contents(__FILE__));
		if( file_exists($localfile) ) 
		{
			unlink($localfile);
		}
		$localfile = "upload-test-object-name-no-such-key.txt";
		$options = array( \OSS\OssClient::OSS_FILE_DOWNLOAD => $localfile );
		try 
		{
			$this->ossClient->getObject($this->bucket, $object . "no-such-key", $options);
			$this->assertTrue(false);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertTrue(true);
			$this->assertFalse(file_exists($localfile));
			if( strpos($e, "The specified key does not exist") == false ) 
			{
				$this->assertTrue(true);
			}
		}
		try 
		{
			$result = $this->ossClient->getObject($this->bucket, $object . "no-such-key");
			$this->assertTrue(false);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertTrue(true);
			if( strpos($e, "The specified key does not exist") == false ) 
			{
				$this->assertTrue(true);
			}
		}
		$to_bucket = $this->bucket;
		$to_object = $object . ".copy";
		$options = array( );
		try 
		{
			$result = $this->ossClient->copyObject($this->bucket, $object, $to_bucket, $to_object, $options);
			$this->assertFalse(empty($result));
			$this->assertEquals(strlen("2016-11-21T03:46:58.000Z"), strlen($result[0]));
			$this->assertEquals(strlen("\"5B3C1A2E053D763E1B002CC607C5A0FE\""), strlen($result[1]));
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
			var_dump($e->getMessage());
		}
		try 
		{
			$content = $this->ossClient->getObject($this->bucket, $to_object);
			$this->assertEquals($content, file_get_contents(__FILE__));
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		$prefix = "";
		$delimiter = "/";
		$next_marker = "";
		$maxkeys = 1000;
		$options = array( "delimiter" => $delimiter, "prefix" => $prefix, "max-keys" => $maxkeys, "marker" => $next_marker );
		try 
		{
			$listObjectInfo = $this->ossClient->listObjects($this->bucket, $options);
			$objectList = $listObjectInfo->getObjectList();
			$prefixList = $listObjectInfo->getPrefixList();
			$this->assertNotNull($objectList);
			$this->assertNotNull($prefixList);
			$this->assertTrue(is_array($objectList));
			$this->assertTrue(is_array($prefixList));
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertTrue(false);
		}
		$from_bucket = $this->bucket;
		$from_object = "oss-php-sdk-test/upload-test-object-name.txt";
		$to_bucket = $from_bucket;
		$to_object = $from_object;
		$copy_options = array( \OSS\OssClient::OSS_HEADERS => array( "Expires" => "2012-10-01 08:00:00", "Content-Disposition" => "attachment; filename=\"xxxxxx\"" ) );
		try 
		{
			$this->ossClient->copyObject($from_bucket, $from_object, $to_bucket, $to_object, $copy_options);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		$object = "oss-php-sdk-test/upload-test-object-name.txt";
		try 
		{
			$objectMeta = $this->ossClient->getObjectMeta($this->bucket, $object);
			$this->assertEquals("attachment; filename=\"xxxxxx\"", $objectMeta[strtolower("Content-Disposition")]);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		$object = "oss-php-sdk-test/upload-test-object-name.txt";
		try 
		{
			$this->assertTrue($this->ossClient->doesObjectExist($this->bucket, $object));
			$this->ossClient->deleteObject($this->bucket, $object);
			$this->assertFalse($this->ossClient->doesObjectExist($this->bucket, $object));
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		$object1 = "oss-php-sdk-test/upload-test-object-name.txt";
		$object2 = "oss-php-sdk-test/upload-test-object-name.txt.copy";
		$list = array( $object1, $object2 );
		try 
		{
			$this->assertTrue($this->ossClient->doesObjectExist($this->bucket, $object2));
			$result = $this->ossClient->deleteObjects($this->bucket, $list);
			$this->assertEquals($list[1], $result[0]);
			$this->assertEquals($list[0], $result[1]);
			$result = $this->ossClient->deleteObjects($this->bucket, $list, array( "quiet" => "true" ));
			$this->assertEquals(array( ), $result);
			$this->assertFalse($this->ossClient->doesObjectExist($this->bucket, $object2));
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
	}
	public function testAppendObject() 
	{
		$object = "oss-php-sdk-test/append-test-object-name.txt";
		$content_array = array( "Hello OSS", "Hi OSS", "OSS OK" );
		try 
		{
			$position = $this->ossClient->appendObject($this->bucket, $object, $content_array[0], 0);
			$this->assertEquals($position, strlen($content_array[0]));
			$position = $this->ossClient->appendObject($this->bucket, $object, $content_array[1], $position);
			$this->assertEquals($position, strlen($content_array[0]) + strlen($content_array[1]));
			$position = $this->ossClient->appendObject($this->bucket, $object, $content_array[2], $position);
			$this->assertEquals($position, strlen($content_array[0]) + strlen($content_array[1]) + strlen($content_array[1]));
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		try 
		{
			$content = $this->ossClient->getObject($this->bucket, $object);
			$this->assertEquals($content, implode($content_array));
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		try 
		{
			$this->ossClient->deleteObject($this->bucket, $object);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		try 
		{
			$position = $this->ossClient->appendFile($this->bucket, $object, __FILE__, 0);
			$this->assertEquals($position, filesize(__FILE__));
			$position = $this->ossClient->appendFile($this->bucket, $object, __FILE__, $position);
			$this->assertEquals($position, filesize(__FILE__) * 2);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		try 
		{
			$content = $this->ossClient->getObject($this->bucket, $object);
			$this->assertEquals($content, file_get_contents(__FILE__) . file_get_contents(__FILE__));
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		try 
		{
			$this->ossClient->deleteObject($this->bucket, $object);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		$options = array( \OSS\OssClient::OSS_HEADERS => array( "Expires" => "2012-10-01 08:00:00", "Content-Disposition" => "attachment; filename=\"xxxxxx\"" ) );
		try 
		{
			$position = $this->ossClient->appendObject($this->bucket, $object, "Hello OSS, ", 0, $options);
			$position = $this->ossClient->appendObject($this->bucket, $object, "Hi OSS.", $position);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		try 
		{
			$objectMeta = $this->ossClient->getObjectMeta($this->bucket, $object);
			$this->assertEquals("attachment; filename=\"xxxxxx\"", $objectMeta[strtolower("Content-Disposition")]);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		try 
		{
			$this->ossClient->deleteObject($this->bucket, $object);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
	}
	public function testPutIllelObject() 
	{
		$object = "/ilegal.txt";
		try 
		{
			$this->ossClient->putObject($this->bucket, $object, "hi", null);
			$this->assertFalse(true);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertEquals("\"/ilegal.txt\" object name is invalid", $e->getMessage());
		}
	}
	public function testCheckMD5() 
	{
		$object = "oss-php-sdk-test/upload-test-object-name.txt";
		$content = file_get_contents(__FILE__);
		$options = array( \OSS\OssClient::OSS_CHECK_MD5 => true );
		try 
		{
			$this->ossClient->putObject($this->bucket, $object, $content, $options);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		try 
		{
			$content = $this->ossClient->getObject($this->bucket, $object);
			$this->assertEquals($content, file_get_contents(__FILE__));
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		try 
		{
			$this->ossClient->uploadFile($this->bucket, $object, __FILE__, $options);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		try 
		{
			$content = $this->ossClient->getObject($this->bucket, $object);
			$this->assertEquals($content, file_get_contents(__FILE__));
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		try 
		{
			$this->ossClient->deleteObject($this->bucket, $object);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		$object = "oss-php-sdk-test/append-test-object-name.txt";
		$content_array = array( "Hello OSS", "Hi OSS", "OSS OK" );
		$options = array( \OSS\OssClient::OSS_CHECK_MD5 => true );
		try 
		{
			$position = $this->ossClient->appendObject($this->bucket, $object, $content_array[0], 0, $options);
			$this->assertEquals($position, strlen($content_array[0]));
			$position = $this->ossClient->appendObject($this->bucket, $object, $content_array[1], $position, $options);
			$this->assertEquals($position, strlen($content_array[0]) + strlen($content_array[1]));
			$position = $this->ossClient->appendObject($this->bucket, $object, $content_array[2], $position, $options);
			$this->assertEquals($position, strlen($content_array[0]) + strlen($content_array[1]) + strlen($content_array[1]));
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		try 
		{
			$content = $this->ossClient->getObject($this->bucket, $object);
			$this->assertEquals($content, implode($content_array));
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		try 
		{
			$this->ossClient->deleteObject($this->bucket, $object);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		try 
		{
			$position = $this->ossClient->appendFile($this->bucket, $object, __FILE__, 0, $options);
			$this->assertEquals($position, filesize(__FILE__));
			$position = $this->ossClient->appendFile($this->bucket, $object, __FILE__, $position, $options);
			$this->assertEquals($position, filesize(__FILE__) * 2);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		try 
		{
			$content = $this->ossClient->getObject($this->bucket, $object);
			$this->assertEquals($content, file_get_contents(__FILE__) . file_get_contents(__FILE__));
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		try 
		{
			$this->ossClient->deleteObject($this->bucket, $object);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
	}
	public function setUp() 
	{
		parent::setUp();
		$this->ossClient->putObject($this->bucket, "oss-php-sdk-test/upload-test-object-name.txt", file_get_contents(__FILE__));
	}
}
?>