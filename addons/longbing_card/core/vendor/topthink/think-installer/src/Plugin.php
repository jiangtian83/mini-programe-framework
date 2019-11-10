<?php  namespace think\composer;
class Plugin implements \Composer\Plugin\PluginInterface 
{
	public function activate(\Composer\Composer $composer, \Composer\IO\IOInterface $io) 
	{
		$manager = $composer->getInstallationManager();
		$manager->addInstaller(new ThinkFramework($io, $composer));
		$manager->addInstaller(new ThinkTesting($io, $composer));
		$manager->addInstaller(new ThinkExtend($io, $composer));
	}
}
?>