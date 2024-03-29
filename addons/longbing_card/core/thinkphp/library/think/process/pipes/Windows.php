<?php  namespace think\process\pipes;
class Windows extends Pipes 
{
	private $files = array( );
	private $fileHandles = array( );
	private $readBytes = NULL;
	private $disableOutput = NULL;
	public function __construct($disableOutput, $input) 
	{
		$this->disableOutput = (bool) $disableOutput;
		if( !$this->disableOutput ) 
		{
			$this->files = array( \think\Process::STDOUT => tempnam(sys_get_temp_dir(), "sf_proc_stdout"), \think\Process::STDERR => tempnam(sys_get_temp_dir(), "sf_proc_stderr") );
			foreach( $this->files as $offset => $file ) 
			{
				$this->fileHandles[$offset] = fopen($this->files[$offset], "rb");
				if( false === $this->fileHandles[$offset] ) 
				{
					throw new \RuntimeException("A temporary file could not be opened to write the process output to, verify that your TEMP environment variable is writable");
				}
			}
		}
		if( is_resource($input) ) 
		{
			$this->input = $input;
		}
		else 
		{
			$this->inputBuffer = $input;
		}
	}
	public function __destruct() 
	{
		$this->close();
		$this->removeFiles();
	}
	public function getDescriptors() 
	{
		if( $this->disableOutput ) 
		{
			$nullstream = fopen("NUL", "c");
			return array( array( "pipe", "r" ), $nullstream, $nullstream );
		}
		return array( array( "pipe", "r" ), array( "file", "NUL", "w" ), array( "file", "NUL", "w" ) );
	}
	public function getFiles() 
	{
		return $this->files;
	}
	public function readAndWrite($blocking, $close = false) 
	{
		$this->write($blocking, $close);
		$read = array( );
		$fh = $this->fileHandles;
		foreach( $fh as $type => $fileHandle ) 
		{
			if( 0 !== fseek($fileHandle, $this->readBytes[$type]) ) 
			{
				continue;
			}
			$data = "";
			$dataread = null;
			while( !feof($fileHandle) ) 
			{
				if( false !== ($dataread = fread($fileHandle, self::CHUNK_SIZE)) ) 
				{
					$data .= $dataread;
				}
			}
			if( 0 < ($length = strlen($data)) ) 
			{
				$this->readBytes[$type] += $length;
				$read[$type] = $data;
			}
			if( false === $dataread || true === $close && feof($fileHandle) && "" === $data ) 
			{
				fclose($this->fileHandles[$type]);
				unset($this->fileHandles[$type]);
			}
		}
		return $read;
	}
	public function areOpen() 
	{
		return (bool) $this->pipes && (bool) $this->fileHandles;
	}
	public function close() 
	{
		parent::close();
		foreach( $this->fileHandles as $handle ) 
		{
			fclose($handle);
		}
		$this->fileHandles = array( );
	}
	public static function create(\think\Process $process, $input) 
	{
		return new static($process->isOutputDisabled(), $input);
	}
	private function removeFiles() 
	{
		foreach( $this->files as $filename ) 
		{
			if( file_exists($filename) ) 
			{
				@unlink($filename);
			}
		}
		$this->files = array( );
	}
	private function write($blocking, $close) 
	{
		if( empty($this->pipes) ) 
		{
			return NULL;
		}
		$this->unblock();
		$r = (null !== $this->input ? array( "input" => $this->input ) : null);
		$w = (isset($this->pipes[0]) ? array( $this->pipes[0] ) : null);
		$e = null;
		if( false === ($n = @stream_select($r, $w, $e, 0, ($blocking ? \think\Process::TIMEOUT_PRECISION * 1000000 : 0))) ) 
		{
			if( !$this->hasSystemCallBeenInterrupted() ) 
			{
				$this->pipes = array( );
			}
		}
		else 
		{
			if( 0 === $n ) 
			{
				return NULL;
			}
			if( null !== $w && 0 < count($r) ) 
			{
				$data = "";
				while( $dataread = fread($r["input"], self::CHUNK_SIZE) ) 
				{
					$data .= $dataread;
				}
				$this->inputBuffer .= $data;
				if( false === $data || true === $close && feof($r["input"]) && "" === $data ) 
				{
					$this->input = null;
				}
			}
			if( null !== $w && 0 < count($w) ) 
			{
				while( strlen($this->inputBuffer) ) 
				{
					$written = fwrite($w[0], $this->inputBuffer, 2 << 18);
					if( 0 < $written ) 
					{
						$this->inputBuffer = (string) substr($this->inputBuffer, $written);
					}
					else 
					{
						break;
					}
				}
			}
			if( "" === $this->inputBuffer && null === $this->input && isset($this->pipes[0]) ) 
			{
				fclose($this->pipes[0]);
				unset($this->pipes[0]);
			}
		}
	}
}
?>