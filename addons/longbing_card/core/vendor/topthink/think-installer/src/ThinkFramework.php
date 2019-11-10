<?php  namespace think\composer;
class ThinkFramework extends \Composer\Installer\LibraryInstaller 
{
	public function install(\Composer\Repository\InstalledRepositoryInterface $repo, \Composer\Package\PackageInterface $package) 
	{
		parent::install($repo, $package);
		if( $this->composer->getPackage()->getType() == "project" && $package->getInstallationSource() != "source" ) 
		{
			$this->filesystem->removeDirectory($this->getInstallPath($package) . DIRECTORY_SEPARATOR . "tests");
		}
	}
	public function getInstallPath(\Composer\Package\PackageInterface $package) 
	{
		if( "topthink/framework" !== $package->getPrettyName() ) 
		{
			throw new \InvalidArgumentException("Unable to install this library!");
		}
		if( $this->composer->getPackage()->getType() !== "project" ) 
		{
			return parent::getInstallPath($package);
		}
		if( $this->composer->getPackage() ) 
		{
			$extra = $this->composer->getPackage()->getExtra();
			if( !empty($extra["think-path"]) ) 
			{
				return $extra["think-path"];
			}
		}
		return "thinkphp";
	}
	public function update(\Composer\Repository\InstalledRepositoryInterface $repo, \Composer\Package\PackageInterface $initial, \Composer\Package\PackageInterface $target) 
	{
		parent::update($repo, $initial, $target);
		if( $this->composer->getPackage()->getType() == "project" && $target->getInstallationSource() != "source" ) 
		{
			$this->filesystem->removeDirectory($this->getInstallPath($target) . DIRECTORY_SEPARATOR . "tests");
		}
	}
	public function supports($packageType) 
	{
		return "think-framework" === $packageType;
	}
}
?>