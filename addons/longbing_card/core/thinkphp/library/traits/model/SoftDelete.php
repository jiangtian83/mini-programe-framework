<?php  namespace traits\model;
trait SoftDelete 
{
	public function trashed() 
	{
		$field = $this->getDeleteTimeField();
		if( $field && !empty($this->data[$field]) ) 
		{
			return true;
		}
		return false;
	}
	public static function withTrashed() 
	{
		return (new static())->getQuery();
	}
	public static function onlyTrashed() 
	{
		$model = new static();
		$field = $model->getDeleteTimeField(true);
		if( $field ) 
		{
			return $model->getQuery()->useSoftDelete($field, array( "not null", "" ));
		}
		return $model->getQuery();
	}
	public function delete($force = false) 
	{
		if( false === $this->trigger("before_delete", $this) ) 
		{
			return false;
		}
		$name = $this->getDeleteTimeField();
		if( $name && !$force ) 
		{
			$this->data[$name] = $this->autoWriteTimestamp($name);
			$result = $this->isUpdate()->save();
		}
		else 
		{
			$result = $this->getQuery()->where($this->getWhere())->delete();
		}
		if( !empty($this->relationWrite) ) 
		{
			foreach( $this->relationWrite as $key => $name ) 
			{
				$name = (is_numeric($key) ? $name : $key);
				$result = $this->getRelation($name);
				if( $result instanceof \think\Model ) 
				{
					$result->delete();
				}
				else 
				{
					if( $result instanceof Collection || is_array($result) ) 
					{
						foreach( $result as $model ) 
						{
							$model->delete();
						}
					}
				}
			}
		}
		$this->trigger("after_delete", $this);
		$this->origin = array( );
		return $result;
	}
	public static function destroy($data, $force = false) 
	{
		if( is_null($data) ) 
		{
			return 0;
		}
		$query = self::withTrashed();
		if( is_array($data) && key($data) !== 0 ) 
		{
			$query->where($data);
			$data = null;
		}
		else 
		{
			if( $data instanceof \Closure ) 
			{
				call_user_func_array($data, array( $query ));
				$data = null;
			}
		}
		$count = 0;
		if( $resultSet = $query->select($data) ) 
		{
			foreach( $resultSet as $data ) 
			{
				$result = $data->delete($force);
				$count += $result;
			}
		}
		return $count;
	}
	public function restore($where = array( )) 
	{
		if( empty($where) ) 
		{
			$pk = $this->getPk();
			$where[$pk] = $this->getData($pk);
		}
		$name = $this->getDeleteTimeField();
		if( $name ) 
		{
			return $this->getQuery()->useSoftDelete($name, array( "not null", "" ))->where($where)->update(array( $name => null ));
		}
		return 0;
	}
	protected function base($query) 
	{
		$field = $this->getDeleteTimeField(true);
		return ($field ? $query->useSoftDelete($field) : $query);
	}
	protected function getDeleteTimeField($read = false) 
	{
		$field = (property_exists($this, "deleteTime") && isset($this->deleteTime) ? $this->deleteTime : "delete_time");
		if( false === $field ) 
		{
			return false;
		}
		if( !strpos($field, ".") ) 
		{
			$field = "__TABLE__." . $field;
		}
		if( !$read && strpos($field, ".") ) 
		{
			$array = explode(".", $field);
			$field = array_pop($array);
		}
		return $field;
	}
}
?>