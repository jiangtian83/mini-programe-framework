<?php  namespace Guzzle\Tests\Plugin\Md5;
class Md5ValidatorPluginTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testValidatesMd5() 
	{
		$plugin = new \Guzzle\Plugin\Md5\Md5ValidatorPlugin();
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("GET", "http://www.test.com/");
		$request->getEventDispatcher()->addSubscriber($plugin);
		$body = "abc";
		$hash = md5($body);
		$response = new \Guzzle\Http\Message\Response(200, array( "Content-MD5" => $hash, "Content-Length" => 3 ), "abc");
		$request->dispatch("request.complete", array( "response" => $response ));
		$response->removeHeader("Content-MD5");
		$request->dispatch("request.complete", array( "response" => $response ));
	}
	public function testThrowsExceptionOnInvalidMd5() 
	{
		$plugin = new \Guzzle\Plugin\Md5\Md5ValidatorPlugin();
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("GET", "http://www.test.com/");
		$request->getEventDispatcher()->addSubscriber($plugin);
		$request->dispatch("request.complete", array( "response" => new \Guzzle\Http\Message\Response(200, array( "Content-MD5" => "foobar", "Content-Length" => 3 ), "abc") ));
	}
	public function testSkipsWhenContentLengthIsTooLarge() 
	{
		$plugin = new \Guzzle\Plugin\Md5\Md5ValidatorPlugin(false, 1);
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("GET", "http://www.test.com/");
		$request->getEventDispatcher()->addSubscriber($plugin);
		$request->dispatch("request.complete", array( "response" => new \Guzzle\Http\Message\Response(200, array( "Content-MD5" => "foobar", "Content-Length" => 3 ), "abc") ));
	}
	public function testProperlyValidatesWhenUsingContentEncoding() 
	{
		$plugin = new \Guzzle\Plugin\Md5\Md5ValidatorPlugin(true);
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("GET", "http://www.test.com/");
		$request->getEventDispatcher()->addSubscriber($plugin);
		$body = \Guzzle\Http\EntityBody::factory("abc");
		$body->compress();
		$hash = $body->getContentMd5();
		$body->uncompress();
		$response = new \Guzzle\Http\Message\Response(200, array( "Content-MD5" => $hash, "Content-Encoding" => "gzip" ), "abc");
		$request->dispatch("request.complete", array( "response" => $response ));
		$this->assertEquals("abc", $response->getBody(true));
		$response = new \Guzzle\Http\Message\Response(200, array( "Content-MD5" => $hash, "Content-Encoding" => "foobar" ), "abc");
		$request->dispatch("request.complete", array( "response" => $response ));
		$body->compress("bzip2.compress");
		$response = new \Guzzle\Http\Message\Response(200, array( "Content-MD5" => $body->getContentMd5(), "Content-Encoding" => "compress" ), "abc");
		$request->dispatch("request.complete", array( "response" => $response ));
		$request->getEventDispatcher()->removeSubscriber($plugin);
		$plugin = new \Guzzle\Plugin\Md5\Md5ValidatorPlugin(false);
		$request->getEventDispatcher()->addSubscriber($plugin);
		$request->dispatch("request.complete", array( "response" => $response ));
	}
}
?>