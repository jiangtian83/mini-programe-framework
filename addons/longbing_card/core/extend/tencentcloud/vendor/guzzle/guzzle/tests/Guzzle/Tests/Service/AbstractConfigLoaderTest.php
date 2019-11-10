<?php  namespace Guzzle\Tests\Service;
class AbstractConfigLoaderTest extends \Guzzle\Tests\GuzzleTestCase 
{
	protected $loader = NULL;
	protected $cleanup = array( );
	public function setUp() 
	{
		$this->loader = $this->getMockBuilder("Guzzle\\Service\\AbstractConfigLoader")->setMethods(array( "build" ))->getMockForAbstractClass();
	}
	public function tearDown() 
	{
		foreach( $this->cleanup as $file ) 
		{
			unlink($file);
		}
	}
	public function testOnlyLoadsSupportedTypes() 
	{
		$this->loader->load(new \stdClass());
	}
	public function testFileMustBeReadable() 
	{
		$this->loader->load("fooooooo.json");
	}
	public function testMustBeSupportedExtension() 
	{
		$this->loader->load(dirname(__DIR__) . "/TestData/FileBody.txt");
	}
	public function testJsonMustBeValue() 
	{
		$filename = tempnam(sys_get_temp_dir(), "json") . ".json";
		file_put_contents($filename, "{/{./{}foo");
		$this->cleanup[] = $filename;
		$this->loader->load($filename);
	}
	public function testPhpFilesMustReturnAnArray() 
	{
		$filename = tempnam(sys_get_temp_dir(), "php") . ".php";
		file_put_contents($filename, "<?php \$fdr = false;");
		$this->cleanup[] = $filename;
		$this->loader->load($filename);
	}
	public function testLoadsPhpFileIncludes() 
	{
		$filename = tempnam(sys_get_temp_dir(), "php") . ".php";
		file_put_contents($filename, "<?php return array(\"foo\" => \"bar\");");
		$this->cleanup[] = $filename;
		$this->loader->expects($this->exactly(1))->method("build")->will($this->returnArgument(0));
		$config = $this->loader->load($filename);
		$this->assertEquals(array( "foo" => "bar" ), $config);
	}
	public function testCanCreateFromJson() 
	{
		$file = dirname(__DIR__) . "/TestData/services/json1.json";
		$this->loader->expects($this->exactly(1))->method("build")->will($this->returnArgument(0));
		$data = $this->loader->load($file);
		$this->assertArrayHasKey("includes", $data);
		$this->assertArrayHasKey("services", $data);
		$this->assertInternalType("array", $data["services"]["foo"]);
		$this->assertInternalType("array", $data["services"]["abstract"]);
		$this->assertInternalType("array", $data["services"]["mock"]);
		$this->assertEquals("bar", $data["services"]["foo"]["params"]["baz"]);
	}
	public function testUsesAliases() 
	{
		$file = dirname(__DIR__) . "/TestData/services/json1.json";
		$this->loader->addAlias("foo", $file);
		$this->loader->expects($this->exactly(1))->method("build")->will($this->returnArgument(0));
		$data = $this->loader->load("foo");
		$this->assertEquals("bar", $data["services"]["foo"]["params"]["baz"]);
	}
	public function testCanRemoveAliases() 
	{
		$file = dirname(__DIR__) . "/TestData/services/json1.json";
		$this->loader->addAlias("foo.json", $file);
		$this->loader->removeAlias("foo.json");
		$this->loader->load("foo.json");
	}
	public function testCanLoadArraysWithIncludes() 
	{
		$file = dirname(__DIR__) . "/TestData/services/json1.json";
		$config = array( "includes" => array( $file ) );
		$this->loader->expects($this->exactly(1))->method("build")->will($this->returnArgument(0));
		$data = $this->loader->load($config);
		$this->assertEquals("bar", $data["services"]["foo"]["params"]["baz"]);
	}
	public function testDoesNotEnterInfiniteLoop() 
	{
		$prefix = $file = dirname(__DIR__) . "/TestData/description";
		$this->loader->load((string) $prefix . "/baz.json");
		$this->assertCount(4, $this->readAttribute($this->loader, "loadedFiles"));
		$this->loader->load((string) $prefix . "/../test_service2.json");
		$this->assertCount(1, $this->readAttribute($this->loader, "loadedFiles"));
		$this->loader->load((string) $prefix . "/baz.json");
		$this->assertCount(4, $this->readAttribute($this->loader, "loadedFiles"));
	}
}
?>