<?php  class ComposerAutoloaderInita3c18f6f45b4cf998e466d2367db0e41 
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
		spl_autoload_register(array( "ComposerAutoloaderInita3c18f6f45b4cf998e466d2367db0e41", "loadClassLoader" ), true, true);
		self::$loader = $loader = new Composer\Autoload\ClassLoader();
		spl_autoload_unregister(array( "ComposerAutoloaderInita3c18f6f45b4cf998e466d2367db0e41", "loadClassLoader" ));
		$useStaticLoader = 50600 <= PHP_VERSION_ID && !defined("HHVM_VERSION") && (!function_exists("zend_loader_file_encoded") || !zend_loader_file_encoded());
		if( $useStaticLoader ) 
		{
			require_once(__DIR__ . "/autoload_static.php");
			call_user_func(Composer\Autoload\ComposerStaticInita3c18f6f45b4cf998e466d2367db0e41::getInitializer($loader));
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