<?php  namespace Guzzle\Tests\Http\Message\Header;
class LinkTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testParsesLinks() 
	{
		$link = new \Guzzle\Http\Message\Header\Link("Link", "<http:/.../front.jpeg>; rel=front; type=\"image/jpeg\", <http://.../back.jpeg>; rel=back; type=\"image/jpeg\", <http://.../side.jpeg?test=1>; rel=side; type=\"image/jpeg\"");
		$links = $link->getLinks();
		$this->assertEquals(array( array( "rel" => "front", "type" => "image/jpeg", "url" => "http:/.../front.jpeg" ), array( "rel" => "back", "type" => "image/jpeg", "url" => "http://.../back.jpeg" ), array( "rel" => "side", "type" => "image/jpeg", "url" => "http://.../side.jpeg?test=1" ) ), $links);
		$this->assertEquals(array( "rel" => "back", "type" => "image/jpeg", "url" => "http://.../back.jpeg" ), $link->getLink("back"));
		$this->assertTrue($link->hasLink("front"));
		$this->assertFalse($link->hasLink("foo"));
	}
	public function testCanAddLink() 
	{
		$link = new \Guzzle\Http\Message\Header\Link("Link", "<http://foo>; rel=a; type=\"image/jpeg\"");
		$link->addLink("http://test.com", "test", array( "foo" => "bar" ));
		$this->assertEquals("<http://foo>; rel=a; type=\"image/jpeg\", <http://test.com>; rel=\"test\"; foo=\"bar\"", (string) $link);
	}
	public function testCanParseLinksWithCommas() 
	{
		$link = new \Guzzle\Http\Message\Header\Link("Link", "<http://example.com/TheBook/chapter1>; rel=\"previous\"; title=\"start, index\"");
		$this->assertEquals(array( array( "rel" => "previous", "title" => "start, index", "url" => "http://example.com/TheBook/chapter1" ) ), $link->getLinks());
	}
}
?>