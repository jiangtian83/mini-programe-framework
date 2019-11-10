<?php  namespace think\model;
class Pivot extends \think\Model 
{
	public $parent = NULL;
	protected $autoWriteTimestamp = false;
	public function __construct($data = array( ), \think\Model $parent = NULL, $table = "") 
	{
		$this->parent = $parent;
		if( is_null($this->name) ) 
		{
			$this->name = $table;
		}
		parent::__construct($data);
	}
}
?>