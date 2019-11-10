<?php  namespace OSS\Tests;
require_once(__DIR__ . DIRECTORY_SEPARATOR . "TestOssClientBase.php");
class CallbackTest extends TestOssClientBase 
{
	public function testMultipartUploadCallbackNormal() 
	{
		$object = "multipart-callback-test.txt";
		$copiedObject = "multipart-callback-test.txt.copied";
		$this->ossClient->putObject($this->bucket, $copiedObject, file_get_contents(__FILE__));
		try 
		{
			$upload_id = $this->ossClient->initiateMultipartUpload($this->bucket, $object);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		$copyId = 1;
		$eTag = $this->ossClient->uploadPartCopy($this->bucket, $copiedObject, $this->bucket, $object, $copyId, $upload_id);
		$upload_parts[] = array( "PartNumber" => $copyId, "ETag" => $eTag );
		try 
		{
			$listPartsInfo = $this->ossClient->listParts($this->bucket, $object, $upload_id);
			$this->assertNotNull($listPartsInfo);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertTrue(false);
		}
		$json = "{\r\n            \"callbackUrl\":\"callback.oss-demo.com:23450\",\r\n            \"callbackHost\":\"oss-cn-hangzhou.aliyuncs.com\",\r\n            \"callbackBody\":\"{\\\"mimeType\\\":\${mimeType},\\\"size\\\":\${size},\\\"x:var1\\\":\${x:var1},\\\"x:var2\\\":\${x:var2}}\",\r\n            \"callbackBodyType\":\"application/json\"\r\n        }";
		$var = "{\r\n           \"x:var1\":\"value1\",\r\n           \"x:var2\":\"值2\"\r\n       }";
		$options = array( \OSS\OssClient::OSS_CALLBACK => $json, \OSS\OssClient::OSS_CALLBACK_VAR => $var );
		try 
		{
			$result = $this->ossClient->completeMultipartUpload($this->bucket, $object, $upload_id, $upload_parts, $options);
			$this->assertEquals("200", $result["info"]["http_code"]);
			$this->assertEquals("{\"Status\":\"OK\"}", $result["body"]);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertTrue(false);
		}
	}
	public function testMultipartUploadCallbackFailed() 
	{
		$object = "multipart-callback-test.txt";
		$copiedObject = "multipart-callback-test.txt.copied";
		$this->ossClient->putObject($this->bucket, $copiedObject, file_get_contents(__FILE__));
		try 
		{
			$upload_id = $this->ossClient->initiateMultipartUpload($this->bucket, $object);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
		$copyId = 1;
		$eTag = $this->ossClient->uploadPartCopy($this->bucket, $copiedObject, $this->bucket, $object, $copyId, $upload_id);
		$upload_parts[] = array( "PartNumber" => $copyId, "ETag" => $eTag );
		try 
		{
			$listPartsInfo = $this->ossClient->listParts($this->bucket, $object, $upload_id);
			$this->assertNotNull($listPartsInfo);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertTrue(false);
		}
		$json = "{\r\n            \"callbackUrl\":\"www.baidu.com\",\r\n            \"callbackHost\":\"oss-cn-hangzhou.aliyuncs.com\",\r\n            \"callbackBody\":\"{\\\"mimeType\\\":\${mimeType},\\\"size\\\":\${size},\\\"x:var1\\\":\${x:var1},\\\"x:var2\\\":\${x:var2}}\",\r\n            \"callbackBodyType\":\"application/json\"\r\n        }";
		$var = "{\r\n       \"x:var1\":\"value1\",\r\n       \"x:var2\":\"值2\"\r\n       }";
		$options = array( \OSS\OssClient::OSS_CALLBACK => $json, \OSS\OssClient::OSS_CALLBACK_VAR => $var );
		try 
		{
			$result = $this->ossClient->completeMultipartUpload($this->bucket, $object, $upload_id, $upload_parts, $options);
			$this->assertTrue(false);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertTrue(true);
			$this->assertEquals("203", $e->getHTTPStatus());
		}
	}
	public function testPutObjectCallbackNormal() 
	{
		$json = "{\r\n                \"callbackUrl\":\"callback.oss-demo.com:23450\",\r\n                \"callbackHost\":\"oss-cn-hangzhou.aliyuncs.com\",\r\n                \"callbackBody\":\"{\\\"mimeType\\\":\${mimeType},\\\"size\\\":\${size}}\",\r\n                \"callbackBodyType\":\"application/json\"\r\n            }";
		$options = array( \OSS\OssClient::OSS_CALLBACK => $json );
		$this->putObjectCallbackOk($options, "200");
		$url = "{\r\n                \"callbackUrl\":\"callback.oss-demo.com:23450\",\r\n                \"callbackHost\":\"oss-cn-hangzhou.aliyuncs.com\",\r\n                \"callbackBody\":\"bucket=\${bucket}&object=\${object}&etag=\${etag}&size=\${size}&mimeType=\${mimeType}&imageInfo.height=\${imageInfo.height}&imageInfo.width=\${imageInfo.width}&imageInfo.format=\${imageInfo.format}\",\r\n                \"callbackBodyType\":\"application/x-www-form-urlencoded\"\r\n            }";
		$options = array( \OSS\OssClient::OSS_CALLBACK => $url );
		$this->putObjectCallbackOk($options, "200");
		$url = "{\r\n                \"callbackUrl\":\"callback.oss-demo.com:23450\",\r\n                \"callbackHost\":\"oss-cn-hangzhou.aliyuncs.com\",\r\n                \"callbackBody\":\"bucket=\${bucket}&object=\${object}&etag=\${etag}&size=\${size}&mimeType=\${mimeType}&imageInfo.height=\${imageInfo.height}&imageInfo.width=\${imageInfo.width}&imageInfo.format=\${imageInfo.format}\"\r\n            }";
		$options = array( \OSS\OssClient::OSS_CALLBACK => $url );
		$this->putObjectCallbackOk($options, "200");
		$json = "{\r\n                \"callbackUrl\":\"callback.oss-demo.com:23450\",\r\n                \"callbackHost\":\"oss-cn-hangzhou.aliyuncs.com\",\r\n                \"callbackBody\":\"{\\\" 春水碧于天，画船听雨眠。\\\":\\\"垆边人似月，皓腕凝霜雪。\\\"}\",\r\n                \"callbackBodyType\":\"application/json\"\r\n            }";
		$options = array( \OSS\OssClient::OSS_CALLBACK => $json );
		$this->putObjectCallbackOk($options, "200");
		$url = "{\r\n                \"callbackUrl\":\"callback.oss-demo.com:23450\",\r\n                \"callbackHost\":\"oss-cn-hangzhou.aliyuncs.com\",\r\n                \"callbackBody\":\"春水碧于天，画船听雨眠。垆边人似月，皓腕凝霜雪\",\r\n                \"callbackBodyType\":\"application/x-www-form-urlencoded\"\r\n            }";
		$options = array( \OSS\OssClient::OSS_CALLBACK => $url );
		$this->putObjectCallbackOk($options, "200");
		$json = "{\r\n                \"callbackUrl\":\"callback.oss-demo.com:23450\",\r\n                \"callbackHost\":\"oss-cn-hangzhou.aliyuncs.com\",\r\n                \"callbackBody\":\"{\\\"mimeType\\\":\${mimeType},\\\"size\\\":\${size},\\\"x:var1\\\":\${x:var1},\\\"x:var2\\\":\${x:var2}}\",\r\n                \"callbackBodyType\":\"application/json\"\r\n            }";
		$var = "{\r\n                \"x:var1\":\"value1\",\r\n                \"x:var2\":\"aliyun.com\"\r\n            }";
		$options = array( \OSS\OssClient::OSS_CALLBACK => $json, \OSS\OssClient::OSS_CALLBACK_VAR => $var );
		$this->putObjectCallbackOk($options, "200");
		$url = "{\r\n                \"callbackUrl\":\"callback.oss-demo.com:23450\",\r\n                \"callbackHost\":\"oss-cn-hangzhou.aliyuncs.com\",\r\n                \"callbackBody\":\"bucket=\${bucket}&object=\${object}&etag=\${etag}&size=\${size}&mimeType=\${mimeType}&imageInfo.height=\${imageInfo.height}&imageInfo.width=\${imageInfo.width}&imageInfo.format=\${imageInfo.format}&my_var1=\${x:var1}&my_var2=\${x:var2}\",\r\n                \"callbackBodyType\":\"application/x-www-form-urlencoded\"\r\n            }";
		$var = "{\r\n                \"x:var1\":\"value1凌波不过横塘路，但目送，芳\",\r\n                \"x:var2\":\"值2\"\r\n            }";
		$options = array( \OSS\OssClient::OSS_CALLBACK => $url, \OSS\OssClient::OSS_CALLBACK_VAR => $var );
		$this->putObjectCallbackOk($options, "200");
	}
	public function testPutCallbackWithCallbackFailed() 
	{
		$json = "{\r\n                \"callbackUrl\":\"http://www.baidu.com\",\r\n                \"callbackHost\":\"oss-cn-hangzhou.aliyuncs.com\",\r\n                \"callbackBody\":\"{\\\"mimeType\\\":\${mimeType},\\\"size\\\":\${size}}\",\r\n                \"callbackBodyType\":\"application/json\"\r\n            }";
		$options = array( \OSS\OssClient::OSS_CALLBACK => $json );
		$this->putObjectCallbackFailed($options, "203");
		$url = "{\r\n                \"callbackUrl\":\"http://www.baidu.com\",\r\n                \"callbackHost\":\"oss-cn-hangzhou.aliyuncs.com\",\r\n                \"callbackBody\":\"bucket=\${bucket}&object=\${object}&etag=\${etag}&size=\${size}&mimeType=\${mimeType}&imageInfo.height=\${imageInfo.height}&imageInfo.width=\${imageInfo.width}&imageInfo.format=\${imageInfo.format}&my_var1=\${x:var1}&my_var2=\${x:var2}\",\r\n                \"callbackBodyType\":\"application/x-www-form-urlencoded\"\r\n            }";
		$options = array( \OSS\OssClient::OSS_CALLBACK => $url );
		$this->putObjectCallbackFailed($options, "203");
	}
	private function putObjectCallbackOk($options, $status) 
	{
		$object = "oss-php-sdk-callback-test.txt";
		$content = file_get_contents(__FILE__);
		try 
		{
			$result = $this->ossClient->putObject($this->bucket, $object, $content, $options);
			$this->assertEquals($status, $result["info"]["http_code"]);
			$this->assertEquals("{\"Status\":\"OK\"}", $result["body"]);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
	}
	private function putObjectCallbackFailed($options, $status) 
	{
		$object = "oss-php-sdk-callback-test.txt";
		$content = file_get_contents(__FILE__);
		try 
		{
			$result = $this->ossClient->putObject($this->bucket, $object, $content, $options);
			$this->assertTrue(false);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertEquals($status, $e->getHTTPStatus());
			$this->assertTrue(true);
		}
	}
	public function setUp() 
	{
		parent::setUp();
	}
}
?>