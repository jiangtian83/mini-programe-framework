<?php  require_once("phing/tasks/ext/git/GitBaseTask.php");
class GuzzleSubSplitTask extends GitBaseTask 
{
	protected $remote = NULL;
	protected $heads = NULL;
	protected $tags = NULL;
	protected $base = NULL;
	protected $subIndicatorFile = "composer.json";
	protected $dryRun = NULL;
	protected $noHeads = false;
	protected $noTags = false;
	protected $splits = NULL;
	protected $client = NULL;
	public function setRemote($str) 
	{
		$this->remote = $str;
	}
	public function getRemote() 
	{
		return $this->remote;
	}
	public function setHeads($str) 
	{
		$this->heads = explode(",", $str);
	}
	public function getHeads() 
	{
		return $this->heads;
	}
	public function setTags($str) 
	{
		$this->tags = explode(",", $str);
	}
	public function getTags() 
	{
		return $this->tags;
	}
	public function setBase($str) 
	{
		$this->base = $str;
	}
	public function getBase() 
	{
		return $this->base;
	}
	public function setSubIndicatorFile($str) 
	{
		$this->subIndicatorFile = $str;
	}
	public function getSubIndicatorFile() 
	{
		return $this->subIndicatorFile;
	}
	public function setDryRun($bool) 
	{
		$this->dryRun = (bool) $bool;
	}
	public function getDryRun() 
	{
		return $this->dryRun;
	}
	public function setNoHeads($bool) 
	{
		$this->noHeads = (bool) $bool;
	}
	public function getNoHeads() 
	{
		return $this->noHeads;
	}
	public function setNoTags($bool) 
	{
		$this->noTags = (bool) $bool;
	}
	public function getNoTags() 
	{
		return $this->noTags;
	}
	public function main() 
	{
		$repo = $this->getRepository();
		if( empty($repo) ) 
		{
			throw new BuildException("\"repository\" is a required parameter");
		}
		$remote = $this->getRemote();
		if( empty($remote) ) 
		{
			throw new BuildException("\"remote\" is a required parameter");
		}
		chdir($repo);
		$this->client = $this->getGitClient(false, $repo);
		if( !is_dir(".subsplit") ) 
		{
			$this->subsplitInit();
		}
		else 
		{
			$this->subsplitUpdate();
		}
		$this->findSplits();
		$this->verifyRepos();
		$this->publish();
	}
	public function publish() 
	{
		$this->log("DRY RUN ONLY FOR NOW");
		$base = $this->getBase();
		$base = rtrim($base, "/") . "/";
		$org = $this->getOwningTarget()->getProject()->getProperty("github.org");
		$splits = array( );
		$heads = $this->getHeads();
		foreach( $heads as $head ) 
		{
			foreach( $this->splits[$head] as $component => $meta ) 
			{
				$splits[] = $base . $component . ":git@github.com:" . $org . "/" . $meta["repo"];
			}
			$cmd = "git subsplit publish ";
			$cmd .= escapeshellarg(implode(" ", $splits));
			if( $this->getNoHeads() ) 
			{
				$cmd .= " --no-heads";
			}
			else 
			{
				$cmd .= " --heads=" . $head;
			}
			if( $this->getNoTags() ) 
			{
				$cmd .= " --no-tags";
			}
			else 
			{
				if( $this->getTags() ) 
				{
					$cmd .= " --tags=" . escapeshellarg(implode(" ", $this->getTags()));
				}
			}
			passthru($cmd);
		}
	}
	public function subsplitUpdate() 
	{
		$repo = $this->getRepository();
		$this->log("git-subsplit update...");
		$cmd = $this->client->getCommand("subsplit");
		$cmd->addArgument("update");
		try 
		{
			$cmd->execute();
		}
		catch( Exception $e ) 
		{
			throw new BuildException("git subsplit update failed" . $e);
		}
		chdir($repo . "/.subsplit");
		passthru("php ../composer.phar update --dev");
		chdir($repo);
	}
	public function subsplitInit() 
	{
		$remote = $this->getRemote();
		$cmd = $this->client->getCommand("subsplit");
		$this->log("running git-subsplit init " . $remote);
		$cmd->setArguments(array( "init", $remote ));
		try 
		{
			$output = $cmd->execute();
		}
		catch( Exception $e ) 
		{
			throw new BuildException("git subsplit init failed" . $e);
		}
		$this->log(trim($output), Project::MSG_INFO);
		$repo = $this->getRepository();
		chdir($repo . "/.subsplit");
		passthru("php ../composer.phar install --dev");
		chdir($repo);
	}
	protected function findSplits() 
	{
		$this->log("checking heads for subsplits");
		$repo = $this->getRepository();
		$base = $this->getBase();
		$splits = array( );
		$heads = $this->getHeads();
		if( !empty($base) ) 
		{
			$base = "/" . ltrim($base, "/");
		}
		else 
		{
			$base = "/";
		}
		chdir($repo . "/.subsplit");
		foreach( $heads as $head ) 
		{
			$splits[$head] = array( );
			passthru("git checkout '" . $head . "'");
			$ds = new DirectoryScanner();
			$ds->setBasedir($repo . "/.subsplit" . $base);
			$ds->setIncludes(array( "**/" . $this->subIndicatorFile ));
			$ds->scan();
			$files = $ds->getIncludedFiles();
			foreach( $files as $file ) 
			{
				$pkg = file_get_contents($repo . "/.subsplit" . $base . "/" . $file);
				$pkg_json = json_decode($pkg, true);
				$name = $pkg_json["name"];
				$component = str_replace("/composer.json", "", $file);
				$tmpreponame = explode("/", $name);
				$reponame = $tmpreponame[1];
				$splits[$head][$component]["repo"] = $reponame;
				$nscomponent = str_replace("/", "\\", $component);
				$splits[$head][$component]["desc"] = "[READ ONLY] Subtree split of " . $nscomponent . ": " . $pkg_json["description"];
			}
		}
		passthru("git checkout master");
		chdir($repo);
		$this->splits = $splits;
	}
	protected function verifyRepos() 
	{
		$this->log("verifying GitHub target repos");
		$github_org = $this->getOwningTarget()->getProject()->getProperty("github.org");
		$github_creds = $this->getOwningTarget()->getProject()->getProperty("github.basicauth");
		if( $github_creds == "username:password" ) 
		{
			$this->log("Skipping GitHub repo checks. Update github.basicauth in build.properties to verify repos.", 1);
		}
		else 
		{
			$ch = curl_init("https://api.github.com/orgs/" . $github_org . "/repos?type=all");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERPWD, $github_creds);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$result = curl_exec($ch);
			curl_close($ch);
			$repos = json_decode($result, true);
			$existing_repos = array( );
			foreach( $repos as $repo ) 
			{
				$tmpreponame = explode("/", $repo["full_name"]);
				$reponame = $tmpreponame[1];
				$existing_repos[$reponame] = $repo["description"];
			}
			$heads = $this->getHeads();
			foreach( $heads as $head ) 
			{
				foreach( $this->splits[$head] as $component => $meta ) 
				{
					$reponame = $meta["repo"];
					if( !isset($existing_repos[$reponame]) ) 
					{
						$this->log("Creating missing repo " . $reponame);
						$payload = array( "name" => $reponame, "description" => $meta["desc"], "homepage" => "http://www.guzzlephp.org/", "private" => true, "has_issues" => false, "has_wiki" => false, "has_downloads" => true, "auto_init" => false );
						$ch = curl_init("https://api.github.com/orgs/" . $github_org . "/repos");
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_USERPWD, $github_creds);
						curl_setopt($ch, CURLOPT_HTTPHEADER, array( "Content-Type: application/json" ));
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
						$result = curl_exec($ch);
						echo "Response code: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
						curl_close($ch);
					}
					else 
					{
						$this->log("Repo " . $reponame . " exists", 2);
					}
				}
			}
		}
	}
}
?>