<?php  namespace think;
class File extends \SplFileObject 
{
	private $error = "";
	protected $filename = NULL;
	protected $saveName = NULL;
	protected $rule = "date";
	protected $validate = array( );
	protected $isTest = NULL;
	protected $info = NULL;
	protected $hash = array( );
	public function __construct($filename, $mode = "r") 
	{
		parent::__construct($filename, $mode);
		$this->filename = ($this->getRealPath() ?: $this->getPathname());
	}
	public function isTest($test = false) 
	{
		$this->isTest = $test;
		return $this;
	}
	public function setUploadInfo($info) 
	{
		$this->info = $info;
		return $this;
	}
	public function getInfo($name = "") 
	{
		return (isset($this->info[$name]) ? $this->info[$name] : $this->info);
	}
	public function getSaveName() 
	{
		return $this->saveName;
	}
	public function setSaveName($saveName) 
	{
		$this->saveName = $saveName;
		return $this;
	}
	public function hash($type = "sha1") 
	{
		if( !isset($this->hash[$type]) ) 
		{
			$this->hash[$type] = hash_file($type, $this->filename);
		}
		return $this->hash[$type];
	}
	protected function checkPath($path) 
	{
		if( is_dir($path) || mkdir($path, 493, true) ) 
		{
			return true;
		}
		$this->error = array( "directory {:path} creation failed", array( "path" => $path ) );
		return false;
	}
	public function getMime() 
	{
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		return finfo_file($finfo, $this->filename);
	}
	public function rule($rule) 
	{
		$this->rule = $rule;
		return $this;
	}
	public function validate(array $rule = array( )) 
	{
		$this->validate = $rule;
		return $this;
	}
	public function isValid() 
	{
		return ($this->isTest ? is_file($this->filename) : is_uploaded_file($this->filename));
	}
	public function check($rule = array( )) 
	{
		$rule = ($rule ?: $this->validate);
		if( isset($rule["size"]) && !$this->checkSize($rule["size"]) ) 
		{
			$this->error = "filesize not match";
			return false;
		}
		if( isset($rule["type"]) && !$this->checkMime($rule["type"]) ) 
		{
			$this->error = "mimetype to upload is not allowed";
			return false;
		}
		if( isset($rule["ext"]) && !$this->checkExt($rule["ext"]) ) 
		{
			$this->error = "extensions to upload is not allowed";
			return false;
		}
		if( !$this->checkImg() ) 
		{
			$this->error = "illegal image files";
			return false;
		}
		return true;
	}
	public function checkExt($ext) 
	{
		if( is_string($ext) ) 
		{
			$ext = explode(",", $ext);
		}
		$extension = strtolower(pathinfo($this->getInfo("name"), PATHINFO_EXTENSION));
		return in_array($extension, $ext);
	}
	public function checkImg() 
	{
		$extension = strtolower(pathinfo($this->getInfo("name"), PATHINFO_EXTENSION));
		return !in_array($extension, array( "gif", "jpg", "jpeg", "bmp", "png", "swf" )) || in_array($this->getImageType($this->filename), array( 1, 2, 3, 4, 6, 13 ));
	}
	protected function getImageType($image) 
	{
		if( function_exists("exif_imagetype") ) 
		{
			return exif_imagetype($image);
		}
		try 
		{
			$info = getimagesize($image);
			return ($info ? $info[2] : false);
		}
		catch( \Exception $e ) 
		{
			return false;
		}
	}
	public function checkSize($size) 
	{
		return $this->getSize() <= $size;
	}
	public function checkMime($mime) 
	{
		$mime = (is_string($mime) ? explode(",", $mime) : $mime);
		return in_array(strtolower($this->getMime()), $mime);
	}
	public function move($path, $savename = true, $replace = true) 
	{
		if( !empty($this->info["error"]) ) 
		{
			$this->error($this->info["error"]);
			return false;
		}
		if( !$this->isValid() ) 
		{
			$this->error = "upload illegal files";
			return false;
		}
		if( !$this->check() ) 
		{
			return false;
		}
		$path = rtrim($path, DS) . DS;
		$saveName = $this->buildSaveName($savename);
		$filename = $path . $saveName;
		if( false === $this->checkPath(dirname($filename)) ) 
		{
			return false;
		}
		if( !$replace && is_file($filename) ) 
		{
			$this->error = array( "has the same filename: {:filename}", array( "filename" => $filename ) );
			return false;
		}
		if( $this->isTest ) 
		{
			rename($this->filename, $filename);
		}
		else 
		{
			if( !move_uploaded_file($this->filename, $filename) ) 
			{
				$this->error = "upload write error";
				return false;
			}
		}
		$file = new self($filename);
		$file->setSaveName($saveName)->setUploadInfo($this->info);
		return $file;
	}
	protected function buildSaveName($savename) 
	{
		if( true === $savename ) 
		{
			if( $this->rule instanceof \Closure ) 
			{
				$savename = call_user_func_array($this->rule, array( $this ));
			}
			else 
			{
				switch( $this->rule ) 
				{
					case "date": $savename = date("Ymd") . DS . md5(microtime(true));
					break;
					default: if( in_array($this->rule, hash_algos()) ) 
					{
						$hash = $this->hash($this->rule);
						$savename = substr($hash, 0, 2) . DS . substr($hash, 2);
					}
					else 
					{
						if( is_callable($this->rule) ) 
						{
							$savename = call_user_func($this->rule);
						}
						else 
						{
							$savename = date("Ymd") . DS . md5(microtime(true));
						}
					}
				}
			}
		}
		else 
		{
			if( "" === $savename || false === $savename ) 
			{
				$savename = $this->getInfo("name");
			}
		}
		if( !strpos($savename, ".") ) 
		{
			$savename .= "." . pathinfo($this->getInfo("name"), PATHINFO_EXTENSION);
		}
		return $savename;
	}
	private function error($errorNo) 
	{
		switch( $errorNo ) 
		{
			case 1: case 2: $this->error = "upload File size exceeds the maximum value";
			break;
			case 3: $this->error = "only the portion of file is uploaded";
			break;
			case 4: $this->error = "no file to uploaded";
			break;
			case 6: $this->error = "upload temp dir not found";
			break;
			case 7: $this->error = "file write error";
			break;
			default: $this->error = "unknown upload error";
		}
		return $this;
	}
	public function getError() 
	{
		if( is_array($this->error) ) 
		{
			list($msg, $vars) = $this->error;
		}
		else 
		{
			$msg = $this->error;
			$vars = array( );
		}
		return (Lang::has($msg) ? Lang::get($msg, $vars) : $msg);
	}
	public function __call($method, $args) 
	{
		return $this->hash($method);
	}
}
?>