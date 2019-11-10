<?php  namespace think\console\command;
class Lists extends \think\console\Command 
{
	protected function configure() 
	{
		$this->setName("list")->setDefinition($this->createDefinition())->setDescription("Lists commands")->setHelp("The <info>%command.name%</info> command lists all commands:\r\n\r\n  <info>php %command.full_name%</info>\r\n\r\nYou can also display the commands for a specific namespace:\r\n\r\n  <info>php %command.full_name% test</info>\r\n\r\nIt's also possible to get raw list of commands (useful for embedding command runner):\r\n\r\n  <info>php %command.full_name% --raw</info>");
	}
	public function getNativeDefinition() 
	{
		return $this->createDefinition();
	}
	protected function execute(\think\console\Input $input, \think\console\Output $output) 
	{
		$output->describe($this->getConsole(), array( "raw_text" => $input->getOption("raw"), "namespace" => $input->getArgument("namespace") ));
	}
	private function createDefinition() 
	{
		return new \think\console\input\Definition(array( new \think\console\input\Argument("namespace", \think\console\input\Argument::OPTIONAL, "The namespace name"), new \think\console\input\Option("raw", null, \think\console\input\Option::VALUE_NONE, "To output raw command list") ));
	}
}
?>