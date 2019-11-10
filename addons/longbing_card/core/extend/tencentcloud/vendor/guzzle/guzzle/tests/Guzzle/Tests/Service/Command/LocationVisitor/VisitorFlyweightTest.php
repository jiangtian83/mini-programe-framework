<?php  namespace Guzzle\Tests\Service\Command;
class VisitorFlyweightTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testUsesDefaultMappingsWithGetInstance() 
	{
		$f = \Guzzle\Service\Command\LocationVisitor\VisitorFlyweight::getInstance();
		$this->assertInstanceOf("Guzzle\\Service\\Command\\LocationVisitor\\Request\\JsonVisitor", $f->getRequestVisitor("json"));
		$this->assertInstanceOf("Guzzle\\Service\\Command\\LocationVisitor\\Response\\JsonVisitor", $f->getResponseVisitor("json"));
	}
	public function testCanUseCustomMappings() 
	{
		$f = new \Guzzle\Service\Command\LocationVisitor\VisitorFlyweight(array( ));
		$this->assertEquals(array( ), $this->readAttribute($f, "mappings"));
	}
	public function testThrowsExceptionWhenRetrievingUnknownVisitor() 
	{
		\Guzzle\Service\Command\LocationVisitor\VisitorFlyweight::getInstance()->getRequestVisitor("foo");
	}
	public function testCachesVisitors() 
	{
		$f = new \Guzzle\Service\Command\LocationVisitor\VisitorFlyweight();
		$v1 = $f->getRequestVisitor("json");
		$this->assertSame($v1, $f->getRequestVisitor("json"));
	}
	public function testAllowsAddingVisitors() 
	{
		$f = new \Guzzle\Service\Command\LocationVisitor\VisitorFlyweight();
		$j1 = new \Guzzle\Service\Command\LocationVisitor\Request\JsonVisitor();
		$j2 = new \Guzzle\Service\Command\LocationVisitor\Response\JsonVisitor();
		$f->addRequestVisitor("json", $j1);
		$f->addResponseVisitor("json", $j2);
		$this->assertSame($j1, $f->getRequestVisitor("json"));
		$this->assertSame($j2, $f->getResponseVisitor("json"));
	}
}
?>