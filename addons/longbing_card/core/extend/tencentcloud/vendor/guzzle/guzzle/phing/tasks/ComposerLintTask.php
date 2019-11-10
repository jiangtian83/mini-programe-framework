<?php  require_once("phing/Task.php");
class ComposerLintTask extends Task 
{
	protected $dir = NULL;
	protected $file = NULL;
	protected $passthru = false;
	protected $composer = NULL;
	public function setDir($str) 
	{
		$this->dir = $str;
	}
	public function setFile($str) 
	{
		$this->file = $str;
	}
	public function setPassthru($passthru) 
	{
		$this->passthru = (bool) $passthru;
	}
	public function setComposer($str) 
	{
		$this->file = $str;
	}
	public function init() 
	{
	}
	public function main() 
	{
		if( $this->composer === NULL ) 
		{
			$this->findComposer();
		}
		$files = array( );
		if( !empty($this->file) && file_exists($this->file) ) 
		{
			$files[] = $this->file;
		}
		if( !empty($this->dir) ) 
		{
			$found = $this->findFiles();
			foreach( $found as $file ) 
			{
				$files[] = $this->dir . DIRECTORY_SEPARATOR . $file;
			}
		}
		foreach( $files as $file ) 
		{
			$cmd = $this->composer . " validate " . $file;
			$cmd = escapeshellcmd($cmd);
			if( $this->passthru ) 
			{
				$retval = NULL;
				passthru($cmd, $retval);
				if( $retval == 1 ) 
				{
					throw new BuildException("invalid composer.json");
				}
			}
			else 
			{
				$out = array( );
				$retval = NULL;
				exec($cmd, $out, $retval);
				if( $retval == 1 ) 
				{
					$err = join("\n", $out);
					throw new BuildException($err);
				}
				$this->log($out[0]);
			}
		}
	}
	protected function findFiles() 
	{
		$ds = new DirectoryScanner();
		$ds->setBasedir($this->dir);
		$ds->setIncludes(array( "**/composer.json" ));
		$ds->scan();
		return $ds->getIncludedFiles();
	}
	protected function findComposer() 
	{
		$basedir = $this->project->getBasedir();
		$php = $this->project->getProperty("php.interpreter");
		if( file_exists($basedir . "/composer.phar") ) 
		{
			$this->composer = (string) $php . " " . $basedir . "/composer.phar";
		}
		else 
		{
			$out = array( );
			exec("which composer", $out);
			if( empty($out) ) 
			{
				throw new BuildException("Could not determine composer location.");
			}
			$this->composer = $out[0];
		}
	}
}
?>