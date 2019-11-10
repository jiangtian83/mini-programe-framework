<?php  namespace think\composer;
class ThinkTesting extends \Composer\Installer\LibraryInstaller 
{
	public function getInstallPath(\Composer\Package\PackageInterface $package) 
	{
		if( "topthink/think-testing" !== $package->getPrettyName() ) 
		{
			throw new \InvalidArgumentException("Unable to install this library!");
		}
		return parent::getInstallPath($package);
	}
	public function install(\Composer\Repository\InstalledRepositoryInterface $repo, \Composer\Package\PackageInterface $package) 
	{
		parent::install($repo, $package);
		$this->copyTestDir($package);
	}
	public function update(\Composer\Repository\InstalledRepositoryInterface $repo, \Composer\Package\PackageInterface $initial, \Composer\Package\PackageInterface $target) 
	{
		parent::update($repo, $initial, $target);
		$this->copyTestDir($target);
	}
	private function copyTestDir(\Composer\Package\PackageInterface $package) 
	{
		$appDir = dirname($this->vendorDir);
		$source = $this->getInstallPath($package) . DIRECTORY_SEPARATOR . "example";
		if( !is_file($appDir . DIRECTORY_SEPARATOR . "phpunit.xml") ) 
		{
			$this->filesystem->copyThenRemove($source, $appDir);
		}
		else 
		{
			$this->filesystem->removeDirectoryPhp($source);
		}
	}
	public function supports($packageType) 
	{
		return "think-testing" === $packageType;
	}
}
?>