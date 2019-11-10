<?php  namespace Composer\Autoload;
class ComposerStaticInita3c18f6f45b4cf998e466d2367db0e41 
{
	public static $prefixLengthsPsr4 = array( "S" => array( "Symfony\\Component\\EventDispatcher\\" => 34 ) );
	public static $prefixDirsPsr4 = NULL;
	public static $prefixesPsr0 = NULL;
	public static function getInitializer(ClassLoader $loader) 
	{
		return \Closure::bind(function() use ($loader) 
		{
			$loader->prefixLengthsPsr4 = ComposerStaticInita3c18f6f45b4cf998e466d2367db0e41::$prefixLengthsPsr4;
			$loader->prefixDirsPsr4 = ComposerStaticInita3c18f6f45b4cf998e466d2367db0e41::$prefixDirsPsr4;
			$loader->prefixesPsr0 = ComposerStaticInita3c18f6f45b4cf998e466d2367db0e41::$prefixesPsr0;
		}
		, null, "Composer\\Autoload\\ClassLoader");
	}
}
?>