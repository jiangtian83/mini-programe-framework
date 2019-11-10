<?php  namespace think\process\pipes;
abstract class Pipes 
{
	public $pipes = array( );
	protected $inputBuffer = "";
	protected $input = NULL;
	private $blocked = true;
	const CHUNK_SIZE = 16384;
	abstract public function getDescriptors();
	abstract public function getFiles();
	abstract public function readAndWrite($blocking, $close);
	abstract public function areOpen();
	public function close() 
	{
		foreach( $this->pipes as $pipe ) 
		{
			fclose($pipe);
		}
		$this->pipes = array( );
	}
	protected function hasSystemCallBeenInterrupted() 
	{
		$lastError = error_get_last();
		return isset($lastError["message"]) && false !== stripos($lastError["message"], "interrupted system call");
	}
	protected function unblock() 
	{
		if( !$this->blocked ) 
		{
			return NULL;
		}
		foreach( $this->pipes as $pipe ) 
		{
			stream_set_blocking($pipe, 0);
		}
		if( null !== $this->input ) 
		{
			stream_set_blocking($this->input, 0);
		}
		$this->blocked = false;
	}
}
?>