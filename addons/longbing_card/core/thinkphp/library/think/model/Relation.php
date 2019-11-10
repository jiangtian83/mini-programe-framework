<?php  namespace think\model;
abstract class Relation 
{
	protected $parent = NULL;
	protected $model = NULL;
	protected $query = NULL;
	protected $foreignKey = NULL;
	protected $localKey = NULL;
	protected $baseQuery = NULL;
	protected $selfRelation = NULL;
	public function getParent() 
	{
		return $this->parent;
	}
	public function getModel() 
	{
		return $this->query->getModel();
	}
	public function getQuery() 
	{
		return $this->query;
	}
	public function selfRelation($self = true) 
	{
		$this->selfRelation = $self;
		return $this;
	}
	public function isSelfRelation() 
	{
		return $this->selfRelation;
	}
	protected function resultSetBuild($resultSet) 
	{
		return (new $this->model())->toCollection($resultSet);
	}
	protected function getQueryFields($model) 
	{
		$fields = $this->query->getOptions("field");
		return $this->getRelationQueryFields($fields, $model);
	}
	protected function getRelationQueryFields($fields, $model) 
	{
		if( $fields ) 
		{
			if( is_string($fields) ) 
			{
				$fields = explode(",", $fields);
			}
			foreach( $fields as &$field ) 
			{
				if( false === strpos($field, ".") ) 
				{
					$field = $model . "." . $field;
				}
			}
		}
		else 
		{
			$fields = $model . ".*";
		}
		return $fields;
	}
	protected function baseQuery() 
	{
	}
	public function __call($method, $args) 
	{
		if( $this->query ) 
		{
			$this->baseQuery();
			$result = call_user_func_array(array( $this->query, $method ), $args);
			if( $result instanceof \think\db\Query ) 
			{
				return $this;
			}
			$this->baseQuery = false;
			return $result;
		}
		throw new \think\Exception("method not exists:" . "think\\model\\Relation" . "->" . $method);
	}
}
?>