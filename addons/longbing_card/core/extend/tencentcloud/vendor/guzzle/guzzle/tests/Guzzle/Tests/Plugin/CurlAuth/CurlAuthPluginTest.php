<?php  namespace Guzzle\Tests\Plugin\CurlAuth;
class CurlAuthPluginTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testAddsBasicAuthentication() 
	{
		\Guzzle\Common\Version::$emitWarnings = false;
		$plugin = new \Guzzle\Plugin\CurlAuth\CurlAuthPlugin("michael", "test");
		$client = new \Guzzle\Http\Client("http://www.test.com/");
		$client->getEventDispatcher()->addSubscriber($plugin);
		$request = $client->get("/");
		$this->assertEquals("michael", $request->getUsername());
		$this->assertEquals("test", $request->getPassword());
		\Guzzle\Common\Version::$emitWarnings = true;
	}
	public function testAddsDigestAuthentication() 
	{
		\Guzzle\Common\Version::$emitWarnings = false;
		$plugin = new \Guzzle\Plugin\CurlAuth\CurlAuthPlugin("julian", "test", CURLAUTH_DIGEST);
		$client = new \Guzzle\Http\Client("http://www.test.com/");
		$client->getEventDispatcher()->addSubscriber($plugin);
		$request = $client->get("/");
		$this->assertEquals("julian", $request->getUsername());
		$this->assertEquals("test", $request->getPassword());
		$this->assertEquals("julian:test", $request->getCurlOptions()->get(CURLOPT_USERPWD));
		$this->assertEquals(CURLAUTH_DIGEST, $request->getCurlOptions()->get(CURLOPT_HTTPAUTH));
		\Guzzle\Common\Version::$emitWarnings = true;
	}
}
?>