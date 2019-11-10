<?php  namespace Guzzle\Tests\Service\Command\LocationVisitor\Request;
class QueryVisitorTest extends AbstractVisitorTestCase 
{
	public function testVisitsLocation() 
	{
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Request\QueryVisitor();
		$param = $this->getNestedCommand("query")->getParam("foo")->setSentAs("test");
		$visitor->visit($this->command, $this->request, $param, "123");
		$this->assertEquals("123", $this->request->getQuery()->get("test"));
	}
	public function testRecursivelyBuildsQueryStrings() 
	{
		$command = $this->getCommand("query");
		$command->getOperation()->getParam("foo")->setSentAs("Foo");
		$request = $command->prepare();
		$this->assertEquals("Foo[test][baz]=1&Foo[test][Jenga_Yall!]=HELLO&Foo[bar]=123", rawurldecode($request->getQuery()));
	}
	public function testFiltersAreAppliedToArrayParamType() 
	{
		$command = $this->getCommandWithArrayParamAndFilters();
		$request = $command->prepare();
		$query = $request->getQuery();
		$this->assertEquals("BAR", $query->get("Foo"));
		$this->assertEquals("123,456,789", $query->get("Arr"));
	}
}
?>