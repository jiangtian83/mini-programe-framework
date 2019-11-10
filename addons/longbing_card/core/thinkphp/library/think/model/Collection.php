<?php  namespace think\model;
class Collection extends \think\Collection 
{
	public function load($relation) 
	{
		$item = current($this->items);
		$item->eagerlyResultSet($this->items, $relation);
		return $this;
	}
	public function hidden($hidden = array( ), $override = false) 
	{
		$this->each(function($model) use ($hidden, $override) 
		{
			$model->hidden($hidden, $override);
		}
		);
		return $this;
	}
	public function visible($visible = array( ), $override = false) 
	{
		$this->each(function($model) use ($visible, $override) 
		{
			$model->visible($visible, $override);
		}
		);
		return $this;
	}
	public function append($append = array( ), $override = false) 
	{
		$this->each(function($model) use ($append, $override) 
		{
			$model and $model->append($append, $override);
		}
		);
		return $this;
	}
}
?>