<?php  namespace think\composer;
class ThinkExtend extends \Composer\Installer\LibraryInstaller 
{
	public function install(\Composer\Repository\InstalledRepositoryInterface $repo, \Composer\Package\PackageInterface $package) 
	{
		parent::install($repo, $package);
		$this->copyExtraFiles($package);
	}
	public function update(\Composer\Repository\InstalledRepositoryInterface $repo, \Composer\Package\PackageInterface $initial, \Composer\Package\PackageInterface $target) 
	{
		parent::update($repo, $initial, $target);
		$this->copyExtraFiles($target);
	}
	protected function copyExtraFiles(\Composer\Package\PackageInterface $package) 
	{
		if( $this->composer->getPackage()->getType() == "project" ) 
		{
			$extra = $package->getExtra();
			if( !empty($extra["think-config"]) ) 
			{
				$composerExtra = $this->composer->getPackage()->getExtra();
				$appDir = (!empty($composerExtra["app-path"]) ? $composerExtra["app-path"] : "application");
				if( is_dir($appDir) ) 
				{
					$extraDir = $appDir . DIRECTORY_SEPARATOR . "extra";
					$this->filesystem->ensureDirectoryExists($extraDir);
					foreach( (array) $extra["think-config"] as $name => $config ) 
					{
						$target = $extraDir . DIRECTORY_SEPARATOR . $name . ".php";
						$source = $this->getInstallPath($package) . DIRECTORY_SEPARATOR . $config;
						if( is_file($target) ) 
						{
							$this->io->write("<info>File " . $target . " exist!</info>");
							continue;
						}
						if( !is_file($source) ) 
						{
							$this->io->write("<info>File " . $target . " not exist!</info>");
							continue;
						}
						copy($source, $target);
					}
				}
			}
		}
	}
	public function supports($packageType) 
	{
		return "think-extend" === $packageType;
	}
}
?>