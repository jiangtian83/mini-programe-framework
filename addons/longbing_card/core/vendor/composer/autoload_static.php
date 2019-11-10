<?php  namespace Composer\Autoload;
class ComposerStaticInit2bc4f313dba415539e266f7ac2c87dcd 
{
	public static $prefixLengthsPsr4 = array( "t" => array( "think\\composer\\" => 15, "think\\" => 6 ), "a" => array( "app\\" => 4 ) );
	public static $prefixDirsPsr4 = NULL;
	public static function getInitializer(ClassLoader $loader) 
	{
		return \Closure::bind(function() use ($loader) 
		{
			$loader->prefixLengthsPsr4 = ComposerStaticInit2bc4f313dba415539e266f7ac2c87dcd::$prefixLengthsPsr4;
			$loader->prefixDirsPsr4 = ComposerStaticInit2bc4f313dba415539e266f7ac2c87dcd::$prefixDirsPsr4;
		}
		, null, "Composer\\Autoload\\ClassLoader");
	}
}
?>