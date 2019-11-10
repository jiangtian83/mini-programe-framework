<?php  namespace think\model\relation;
abstract class OneToOne extends \think\model\Relation 
{
	protected $eagerlyType = 1;
	protected $joinType = NULL;
	protected $bindAttr = array( );
	protected $relation = NULL;
	public function joinType($type) 
	{
		$this->joinType = $type;
		return $this;
	}
	public function eagerly(\think\db\Query $query, $relation, $subRelation, $closure, $first) 
	{
		$name = \think\Loader::parseName(basename(str_replace("\\", "/", get_class($query->getModel()))));
		if( $first ) 
		{
			$table = $query->getTable();
			$query->table(array( $table => $name ));
			if( $query->getOptions("field") ) 
			{
				$field = $query->getOptions("field");
				$query->removeOption("field");
			}
			else 
			{
				$field = true;
			}
			$query->field($field, false, $table, $name);
			$field = null;
		}
		$joinTable = $this->query->getTable();
		$joinAlias = $relation;
		$query->via($joinAlias);
		if( $this instanceof BelongsTo ) 
		{
			$query->join(array( $joinTable => $joinAlias ), $name . "." . $this->foreignKey . "=" . $joinAlias . "." . $this->localKey, $this->joinType);
		}
		else 
		{
			$query->join(array( $joinTable => $joinAlias ), $name . "." . $this->localKey . "=" . $joinAlias . "." . $this->foreignKey, $this->joinType);
		}
		if( $closure ) 
		{
			call_user_func_array($closure, array( $query ));
			if( $query->getOptions("with_field") ) 
			{
				$field = $query->getOptions("with_field");
				$query->removeOption("with_field");
			}
		}
		else 
		{
			if( isset($this->option["field"]) ) 
			{
				$field = $this->option["field"];
			}
		}
		$query->field((isset($field) ? $field : true), false, $joinTable, $joinAlias, $relation . "__");
	}
	abstract protected function eagerlySet(&$resultSet, $relation, $subRelation, $closure);
	abstract protected function eagerlyOne(&$result, $relation, $subRelation, $closure);
	public function eagerlyResultSet(&$resultSet, $relation, $subRelation, $closure) 
	{
		if( 1 == $this->eagerlyType ) 
		{
			$this->eagerlySet($resultSet, $relation, $subRelation, $closure);
		}
		else 
		{
			foreach( $resultSet as $result ) 
			{
				$this->match($this->model, $relation, $result);
			}
		}
	}
	public function eagerlyResult(&$result, $relation, $subRelation, $closure) 
	{
		if( 1 == $this->eagerlyType ) 
		{
			$this->eagerlyOne($result, $relation, $subRelation, $closure);
		}
		else 
		{
			$this->match($this->model, $relation, $result);
		}
	}
	public function save($data) 
	{
		if( $data instanceof \think\Model ) 
		{
			$data = $data->getData();
		}
		$model = new $this->model();
		$data[$this->foreignKey] = $this->parent->
		{
			$this->localKey}
		;
		return ($model->save($data) ? $model : false);
	}
	public function setEagerlyType($type) 
	{
		$this->eagerlyType = $type;
		return $this;
	}
	public function getEagerlyType() 
	{
		return $this->eagerlyType;
	}
	public function bind($attr) 
	{
		if( is_string($attr) ) 
		{
			$attr = explode(",", $attr);
		}
		$this->bindAttr = $attr;
		return $this;
	}
	public function getBindAttr() 
	{
		return $this->bindAttr;
	}
	public function relationCount($result, $closure) 
	{
	}
	protected function match($model, $relation, &$result) 
	{
		foreach( $result->getData() as $key => $val ) 
		{
			if( strpos($key, "__") ) 
			{
				list($name, $attr) = explode("__", $key, 2);
				if( $name == $relation ) 
				{
					$list[$name][$attr] = $val;
					unset($result->$key);
				}
			}
		}
		if( isset($list[$relation]) ) 
		{
			$relationModel = new $model($list[$relation]);
			$relationModel->setParent(clone $result);
			$relationModel->isUpdate(true);
			if( !empty($this->bindAttr) ) 
			{
				$this->bindAttr($relationModel, $result, $this->bindAttr);
			}
		}
		else 
		{
			$relationModel = null;
		}
		$result->setRelation(\think\Loader::parseName($relation), $relationModel);
	}
	protected function bindAttr($model, &$result, $bindAttr) 
	{
		foreach( $bindAttr as $key => $attr ) 
		{
			$key = (is_numeric($key) ? $attr : $key);
			if( isset($result->$key) ) 
			{
				throw new \think\Exception("bind attr has exists:" . $key);
			}
			$result->setAttr($key, ($model ? $model->$attr : null));
		}
	}
	protected function eagerlyWhere($model, $where, $key, $relation, $subRelation = "", $closure = false) 
	{
		$this->baseQuery = true;
		if( $closure ) 
		{
			call_user_func_array($closure, array( $model ));
			if( $field = $model->getOptions("with_field") ) 
			{
				$model->field($field)->removeOption("with_field");
			}
		}
		$list = $model->where($where)->with($subRelation)->select();
		$data = array( );
		foreach( $list as $set ) 
		{
			$data[$set->$key] = $set;
		}
		return $data;
	}
}
?>