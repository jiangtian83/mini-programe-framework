<?php  namespace think\console\command;
class Help extends \think\console\Command 
{
	private $command = NULL;
	protected function configure() 
	{
		$this->ignoreValidationErrors();
		$this->setName("help")->setDefinition(array( new \think\console\input\Argument("command_name", \think\console\input\Argument::OPTIONAL, "The command name", "help"), new \think\console\input\Option("raw", null, \think\console\input\Option::VALUE_NONE, "To output raw command help") ))->setDescription("Displays help for a command")->setHelp("The <info>%command.name%</info> command displays help for a given command:\r\n\r\n  <info>php %command.full_name% list</info>\r\n\r\nTo display the list of available commands, please use the <info>list</info> command.");
	}
	public function setCommand(\think\console\Command $command) 
	{
		$this->command = $command;
	}
	protected function execute(\think\console\Input $input, \think\console\Output $output) 
	{
		if( null === $this->command ) 
		{
			$this->command = $this->getConsole()->find($input->getArgument("command_name"));
		}
		$output->describe($this->command, array( "raw_text" => $input->getOption("raw") ));
		$this->command = null;
	}
}
?>