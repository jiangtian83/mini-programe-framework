<?php  namespace Guzzle\Tests\Parsers\UriTemplate;
class PeclUriTemplateTest extends AbstractUriTemplateTest 
{
	protected function setUp() 
	{
		if( !extension_loaded("uri_template") ) 
		{
			$this->markTestSkipped("uri_template PECL extension must be installed to test PeclUriTemplate");
		}
	}
	public function testExpandsUriTemplates($template, $expansion, $params) 
	{
		$uri = new \Guzzle\Parser\UriTemplate\PeclUriTemplate($template);
		$this->assertEquals($expansion, $uri->expand($template, $params));
	}
}
?>