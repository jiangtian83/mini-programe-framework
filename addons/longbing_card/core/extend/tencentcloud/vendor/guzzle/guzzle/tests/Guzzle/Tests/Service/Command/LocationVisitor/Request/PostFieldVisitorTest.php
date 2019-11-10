<?php  namespace Guzzle\Tests\Service\Command\LocationVisitor\Request;
class PostFieldVisitorTest extends AbstractVisitorTestCase 
{
	public function testVisitsLocation() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Request\PostFieldVisitor();
		$param = $this->getNestedCommand("postField")->getParam("foo");
		$visitor->visit($this->command, $this->request, $param->setSentAs("test"), "123");
		$this->assertEquals("123", (string) $this->request->getPostField("test"));
	}
	public function testRecursivelyBuildsPostFields() 
	{
		$command = $this->getCommand("postField");
		$request = $command->prepare();
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Request\PostFieldVisitor();
		$param = $command->getOperation()->getParam("foo");
		$visitor->visit($command, $request, $param, $command["foo"]);
		$visitor->after($command, $request);
		$this->assertEquals("Foo[test][baz]=1&Foo[test][Jenga_Yall!]=HELLO&Foo[bar]=123", rawurldecode((string) $request->getPostFields()));
	}
}
?>