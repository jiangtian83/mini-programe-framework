<?php  class ComposerAutoloaderInit2bc4f313dba415539e266f7ac2c87dcd 
{
	private static $loader = NULL;
	public static function loadClassLoader($class) 
	{
		if( "Composer\\Autoload\\ClassLoader" === $class ) 
		{
			require(__DIR__ . "/ClassLoader.php");
		}
	}
	public static function getLoader() 
	{
		if( NULL !== self::$loader ) 
		{
			return self::$loader;
		}
		spl_autoload_register(array( "ComposerAutoloaderInit2bc4f313dba415539e266f7ac2c87dcd", "loadClassLoader" ), true, true);
		self::$loader = $loader = new Composer\Autoload\ClassLoader();
		spl_autoload_unregister(array( "ComposerAutoloaderInit2bc4f313dba415539e266f7ac2c87dcd", "loadClassLoader" ));
		$useStaticLoader = 50600 <= PHP_VERSION_ID && !defined("HHVM_VERSION") && (!function_exists("zend_loader_file_encoded") || !zend_loader_file_encoded());
		if( $useStaticLoader ) 
		{
			require_once(__DIR__ . "/autoload_static.php");
			call_user_func(Composer\Autoload\ComposerStaticInit2bc4f313dba415539e266f7ac2c87dcd::getInitializer($loader));
		}
		else 
		{
			$map = require(__DIR__ . "/autoload_namespaces.php");
			foreach( $map as $namespace => $path ) 
			{
				$loader->set($namespace, $path);
			}
			$map = require(__DIR__ . "/autoload_psr4.php");
			foreach( $map as $namespace => $path ) 
			{
				$loader->setPsr4($namespace, $path);
			}
			$classMap = require(__DIR__ . "/autoload_classmap.php");
			if( $classMap ) 
			{
				$loader->addClassMap($classMap);
			}
		}
		$loader->register(true);
		return $loader;
	}
}
?>