<?php  namespace think\console\output\driver;
class Nothing 
{
	public function __construct(\think\console\Output $output) 
	{
	}
	public function write($messages, $newline = false, $options = \think\console\Output::OUTPUT_NORMAL) 
	{
	}
	public function renderException(\Exception $e) 
	{
	}
}
?>