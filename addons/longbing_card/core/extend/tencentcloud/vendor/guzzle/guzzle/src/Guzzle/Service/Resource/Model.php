<?php  namespace Guzzle\Service\Resource;
class Model extends \Guzzle\Common\Collection 
{
	protected $structure = NULL;
	public function __construct(array $data = array( ), \Guzzle\Service\Description\Parameter $structure = NULL) 
	{
		$this->data = $data;
		$this->structure = $structure;
	}
	public function getStructure() 
	{
		return ($this->structure ?: new \Guzzle\Service\Description\Parameter());
	}
	public function __toString() 
	{
		$output = "Debug output of ";
		if( $this->structure ) 
		{
			$output .= $this->structure->getName() . " ";
		}
		$output .= "model";
		$output = str_repeat("=", strlen($output)) . "\n" . $output . "\n" . str_repeat("=", strlen($output)) . "\n\n";
		$output .= "Model data\n-----------\n\n";
		$output .= "This data can be retrieved from the model object using the get() method of the model " . "(e.g. \$model->get(\$key)) or accessing the model like an associative array (e.g. \$model['key']).\n\n";
		$lines = array_slice(explode("\n", trim(print_r($this->toArray(), true))), 2, -1);
		$output .= implode("\n", $lines);
		if( $this->structure ) 
		{
			$output .= "\n\nModel structure\n---------------\n\n";
			$output .= "The following JSON document defines how the model was parsed from an HTTP response into the " . "associative array structure you see above.\n\n";
			$output .= "  " . json_encode($this->structure->toArray()) . "\n\n";
		}
		return $output . "\n";
	}
}
?>