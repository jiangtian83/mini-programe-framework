<?php  namespace app\base\controller;
class Card extends \app\common\controller\Base 
{
	protected $token = NULL;
	protected $error_array = array( "error-1" => -1, "error402" => 402, "error-2" => -2, "error401" => 401, "error10000" => 10000, "error0" => 0, "error400" => 400, "error-3" => -3 );
	protected $request = NULL;
	protected $limit_10 = 10;
	protected $limit_15 = 15;
	public function _initialize() 
	{
	}
	protected function getParams(array $rule) 
	{
		$tmp = array( );
		foreach( $rule as $index => $v ) 
		{
			if( $v ) 
			{
				$value = $this->request->param($index);
				$res = \think\Validate::is($value, $v);
				if( !$res ) 
				{
					return false;
				}
			}
			else 
			{
				$value = $this->request->param($index);
				if( !$value ) 
				{
					$value = $v;
				}
			}
			$tmp[$index] = $value;
		}
		return $tmp;
	}
}
?>