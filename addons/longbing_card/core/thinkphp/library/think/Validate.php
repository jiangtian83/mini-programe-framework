<?php  namespace think;
class Validate 
{
	protected static $instance = NULL;
	protected static $type = array( );
	protected $alias = array( ">" => "gt", ">=" => "egt", "<" => "lt", "<=" => "elt", "=" => "eq", "same" => "eq" );
	protected $rule = array( );
	protected $message = array( );
	protected $field = array( );
	protected static $typeMsg = array( "require" => ":attribute require", "number" => ":attribute must be numeric", "integer" => ":attribute must be integer", "float" => ":attribute must be float", "boolean" => ":attribute must be bool", "email" => ":attribute not a valid email address", "array" => ":attribute must be a array", "accepted" => ":attribute must be yes,on or 1", "date" => ":attribute not a valid datetime", "file" => ":attribute not a valid file", "image" => ":attribute not a valid image", "alpha" => ":attribute must be alpha", "alphaNum" => ":attribute must be alpha-numeric", "alphaDash" => ":attribute must be alpha-numeric, dash, underscore", "activeUrl" => ":attribute not a valid domain or ip", "chs" => ":attribute must be chinese", "chsAlpha" => ":attribute must be chinese or alpha", "chsAlphaNum" => ":attribute must be chinese,alpha-numeric", "chsDash" => ":attribute must be chinese,alpha-numeric,underscore, dash", "url" => ":attribute not a valid url", "ip" => ":attribute not a valid ip", "dateFormat" => ":attribute must be dateFormat of :rule", "in" => ":attribute must be in :rule", "notIn" => ":attribute be notin :rule", "between" => ":attribute must between :1 - :2", "notBetween" => ":attribute not between :1 - :2", "length" => "size of :attribute must be :rule", "max" => "max size of :attribute must be :rule", "min" => "min size of :attribute must be :rule", "after" => ":attribute cannot be less than :rule", "before" => ":attribute cannot exceed :rule", "expire" => ":attribute not within :rule", "allowIp" => "access IP is not allowed", "denyIp" => "access IP denied", "confirm" => ":attribute out of accord with :2", "different" => ":attribute cannot be same with :2", "egt" => ":attribute must greater than or equal :rule", "gt" => ":attribute must greater than :rule", "elt" => ":attribute must less than or equal :rule", "lt" => ":attribute must less than :rule", "eq" => ":attribute must equal :rule", "unique" => ":attribute has exists", "regex" => ":attribute not conform to the rules", "method" => "invalid Request method", "token" => "invalid token", "fileSize" => "filesize not match", "fileExt" => "extensions to upload is not allowed", "fileMime" => "mimetype to upload is not allowed" );
	protected $currentScene = NULL;
	protected $regex = array( );
	protected $scene = array( );
	protected $error = array( );
	protected $batch = false;
	public function __construct(array $rules = array( ), $message = array( ), $field = array( )) 
	{
		$this->rule = array_merge($this->rule, $rules);
		$this->message = array_merge($this->message, $message);
		$this->field = array_merge($this->field, $field);
	}
	public static function make($rules = array( ), $message = array( ), $field = array( )) 
	{
		if( is_null(self::$instance) ) 
		{
			self::$instance = new self($rules, $message, $field);
		}
		return self::$instance;
	}
	public function rule($name, $rule = "") 
	{
		if( is_array($name) ) 
		{
			$this->rule = array_merge($this->rule, $name);
		}
		else 
		{
			$this->rule[$name] = $rule;
		}
		return $this;
	}
	public static function extend($type, $callback = NULL) 
	{
		if( is_array($type) ) 
		{
			self::$type = array_merge(self::$type, $type);
		}
		else 
		{
			self::$type[$type] = $callback;
		}
	}
	public static function setTypeMsg($type, $msg = NULL) 
	{
		if( is_array($type) ) 
		{
			self::$typeMsg = array_merge(self::$typeMsg, $type);
		}
		else 
		{
			self::$typeMsg[$type] = $msg;
		}
	}
	public function message($name, $message = "") 
	{
		if( is_array($name) ) 
		{
			$this->message = array_merge($this->message, $name);
		}
		else 
		{
			$this->message[$name] = $message;
		}
		return $this;
	}
	public function scene($name, $fields = NULL) 
	{
		if( is_array($name) ) 
		{
			$this->scene = array_merge($this->scene, $name);
		}
		if( is_null($fields) ) 
		{
			$this->currentScene = $name;
		}
		else 
		{
			$this->scene[$name] = $fields;
		}
		return $this;
	}
	public function hasScene($name) 
	{
		return isset($this->scene[$name]);
	}
	public function batch($batch = true) 
	{
		$this->batch = $batch;
		return $this;
	}
	public function check($data, $rules = array( ), $scene = "") 
	{
		$this->error = array( );
		if( empty($rules) ) 
		{
			$rules = $this->rule;
		}
		$scene = $this->getScene($scene);
		if( is_array($scene) ) 
		{
			$change = array( );
			$array = array( );
			foreach( $scene as $k => $val ) 
			{
				if( is_numeric($k) ) 
				{
					$array[] = $val;
				}
				else 
				{
					$array[] = $k;
					$change[$k] = $val;
				}
			}
		}
		foreach( $rules as $key => $item ) 
		{
			if( is_numeric($key) ) 
			{
				list($key, $rule) = $item;
				if( isset($item[2]) ) 
				{
					$msg = (is_string($item[2]) ? explode("|", $item[2]) : $item[2]);
				}
				else 
				{
					$msg = array( );
				}
			}
			else 
			{
				$rule = $item;
				$msg = array( );
			}
			if( strpos($key, "|") ) 
			{
				list($key, $title) = explode("|", $key);
			}
			else 
			{
				$title = (isset($this->field[$key]) ? $this->field[$key] : $key);
			}
			if( !empty($scene) ) 
			{
				if( $scene instanceof \Closure && !call_user_func_array($scene, array( $key, $data )) ) 
				{
					continue;
				}
				if( is_array($scene) ) 
				{
					if( !in_array($key, $array) ) 
					{
						continue;
					}
					if( isset($change[$key]) ) 
					{
						$rule = $change[$key];
					}
				}
			}
			$value = $this->getDataValue($data, $key);
			if( $rule instanceof \Closure ) 
			{
				$result = call_user_func_array($rule, array( $value, $data ));
			}
			else 
			{
				$result = $this->checkItem($key, $value, $rule, $data, $title, $msg);
			}
			if( true !== $result ) 
			{
				if( !empty($this->batch) ) 
				{
					if( is_array($result) ) 
					{
						$this->error = array_merge($this->error, $result);
					}
					else 
					{
						$this->error[$key] = $result;
					}
				}
				else 
				{
					$this->error = $result;
					return false;
				}
			}
		}
		return (!empty($this->error) ? false : true);
	}
	protected function checkRule($value, $rules) 
	{
		if( $rules instanceof \Closure ) 
		{
			return call_user_func_array($rules, array( $value ));
		}
		if( is_string($rules) ) 
		{
			$rules = explode("|", $rules);
		}
		foreach( $rules as $key => $rule ) 
		{
			if( $rule instanceof \Closure ) 
			{
				$result = call_user_func_array($rule, array( $value ));
			}
			else 
			{
				list($type, $rule) = $this->getValidateType($key, $rule);
				$callback = (isset(self::$type[$type]) ? self::$type[$type] : array( $this, $type ));
				$result = call_user_func_array($callback, array( $value, $rule ));
			}
			if( true !== $result ) 
			{
				return $result;
			}
		}
		return true;
	}
	protected function checkItem($field, $value, $rules, $data, $title = "", $msg = array( )) 
	{
		if( is_string($rules) ) 
		{
			$rules = explode("|", $rules);
		}
		$i = 0;
		foreach( $rules as $key => $rule ) 
		{
			if( $rule instanceof \Closure ) 
			{
				$result = call_user_func_array($rule, array( $value, $data ));
				$info = (is_numeric($key) ? "" : $key);
			}
			else 
			{
				list($type, $rule, $info) = $this->getValidateType($key, $rule);
				if( 0 === strpos($info, "require") || !is_null($value) && "" !== $value ) 
				{
					$callback = (isset(self::$type[$type]) ? self::$type[$type] : array( $this, $type ));
					$result = call_user_func_array($callback, array( $value, $rule, $data, $field, $title ));
				}
				else 
				{
					$result = true;
				}
			}
			if( false === $result ) 
			{
				if( isset($msg[$i]) ) 
				{
					$message = $msg[$i];
					if( is_string($message) && strpos($message, "{%") === 0 ) 
					{
						$message = Lang::get(substr($message, 2, -1));
					}
				}
				else 
				{
					$message = $this->getRuleMsg($field, $title, $info, $rule);
				}
				return $message;
			}
			if( true !== $result ) 
			{
				if( is_string($result) && false !== strpos($result, ":") ) 
				{
					$result = str_replace(array( ":attribute", ":rule" ), array( $title, (string) $rule ), $result);
				}
				return $result;
			}
			$i++;
		}
		return $result;
	}
	protected function getValidateType($key, $rule) 
	{
		if( !is_numeric($key) ) 
		{
			return array( $key, $rule, $key );
		}
		if( strpos($rule, ":") ) 
		{
			list($type, $rule) = explode(":", $rule, 2);
			if( isset($this->alias[$type]) ) 
			{
				$type = $this->alias[$type];
			}
			$info = $type;
		}
		else 
		{
			if( method_exists($this, $rule) ) 
			{
				$type = $rule;
				$info = $rule;
				$rule = "";
			}
			else 
			{
				$type = "is";
				$info = $rule;
			}
		}
		return array( $type, $rule, $info );
	}
	protected function confirm($value, $rule, $data, $field = "") 
	{
		if( "" == $rule ) 
		{
			if( strpos($field, "_confirm") ) 
			{
				$rule = strstr($field, "_confirm", true);
			}
			else 
			{
				$rule = $field . "_confirm";
			}
		}
		return $this->getDataValue($data, $rule) === $value;
	}
	protected function different($value, $rule, $data) 
	{
		return $this->getDataValue($data, $rule) != $value;
	}
	protected function egt($value, $rule, $data) 
	{
		$val = $this->getDataValue($data, $rule);
		return !is_null($val) && $val <= $value;
	}
	protected function gt($value, $rule, $data) 
	{
		$val = $this->getDataValue($data, $rule);
		return !is_null($val) && $val < $value;
	}
	protected function elt($value, $rule, $data) 
	{
		$val = $this->getDataValue($data, $rule);
		return !is_null($val) && $value <= $val;
	}
	protected function lt($value, $rule, $data) 
	{
		$val = $this->getDataValue($data, $rule);
		return !is_null($val) && $value < $val;
	}
	protected function eq($value, $rule) 
	{
		return $value == $rule;
	}
	protected function is($value, $rule, $data = array( )) 
	{
		switch( $rule ) 
		{
			case "require": $result = !empty($value) || "0" == $value;
			break;
			case "accepted": $result = in_array($value, array( "1", "on", "yes" ));
			break;
			case "date": $result = false !== strtotime($value);
			break;
			case "alpha": $result = $this->regex($value, "/^[A-Za-z]+\$/");
			break;
			case "alphaNum": $result = $this->regex($value, "/^[A-Za-z0-9]+\$/");
			break;
			case "alphaDash": $result = $this->regex($value, "/^[A-Za-z0-9\\-\\_]+\$/");
			break;
			case "chs": $result = $this->regex($value, "/^[\\x{4e00}-\\x{9fa5}]+\$/u");
			break;
			case "chsAlpha": $result = $this->regex($value, "/^[\\x{4e00}-\\x{9fa5}a-zA-Z]+\$/u");
			break;
			case "chsAlphaNum": $result = $this->regex($value, "/^[\\x{4e00}-\\x{9fa5}a-zA-Z0-9]+\$/u");
			break;
			case "chsDash": $result = $this->regex($value, "/^[\\x{4e00}-\\x{9fa5}a-zA-Z0-9\\_\\-]+\$/u");
			break;
			case "activeUrl": $result = checkdnsrr($value);
			break;
			case "ip": $result = $this->filter($value, array( FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 ));
			break;
			case "url": $result = $this->filter($value, FILTER_VALIDATE_URL);
			break;
			case "float": $result = $this->filter($value, FILTER_VALIDATE_FLOAT);
			break;
			case "number": $result = is_numeric($value);
			break;
			case "integer": $result = $this->filter($value, FILTER_VALIDATE_INT);
			break;
			case "email": $result = $this->filter($value, FILTER_VALIDATE_EMAIL);
			break;
			case "boolean": $result = in_array($value, array( true, false, 0, 1, "0", "1" ), true);
			break;
			case "array": $result = is_array($value);
			break;
			case "file": $result = $value instanceof File;
			break;
			case "image": $result = $value instanceof File && in_array($this->getImageType($value->getRealPath()), array( 1, 2, 3, 6 ));
			break;
			case "token": $result = $this->token($value, "__token__", $data);
			break;
			default: if( isset(self::$type[$rule]) ) 
			{
				$result = call_user_func_array(self::$type[$rule], array( $value ));
			}
			else 
			{
				$result = $this->regex($value, $rule);
			}
		}
		return $result;
	}
	protected function getImageType($image) 
	{
		if( function_exists("exif_imagetype") ) 
		{
			return exif_imagetype($image);
		}
		try 
		{
			$info = getimagesize($image);
			return ($info ? $info[2] : false);
		}
		catch( \Exception $e ) 
		{
			return false;
		}
	}
	protected function activeUrl($value, $rule) 
	{
		if( !in_array($rule, array( "A", "MX", "NS", "SOA", "PTR", "CNAME", "AAAA", "A6", "SRV", "NAPTR", "TXT", "ANY" )) ) 
		{
			$rule = "MX";
		}
		return checkdnsrr($value, $rule);
	}
	protected function ip($value, $rule) 
	{
		if( !in_array($rule, array( "ipv4", "ipv6" )) ) 
		{
			$rule = "ipv4";
		}
		return $this->filter($value, array( FILTER_VALIDATE_IP, ("ipv6" == $rule ? FILTER_FLAG_IPV6 : FILTER_FLAG_IPV4) ));
	}
	protected function fileExt($file, $rule) 
	{
		if( is_array($file) ) 
		{
			foreach( $file as $item ) 
			{
				if( !$item instanceof File || !$item->checkExt($rule) ) 
				{
					return false;
				}
			}
			return true;
		}
		else 
		{
			if( $file instanceof File ) 
			{
				return $file->checkExt($rule);
			}
			return false;
		}
	}
	protected function fileMime($file, $rule) 
	{
		if( is_array($file) ) 
		{
			foreach( $file as $item ) 
			{
				if( !$item instanceof File || !$item->checkMime($rule) ) 
				{
					return false;
				}
			}
			return true;
		}
		else 
		{
			if( $file instanceof File ) 
			{
				return $file->checkMime($rule);
			}
			return false;
		}
	}
	protected function fileSize($file, $rule) 
	{
		if( is_array($file) ) 
		{
			foreach( $file as $item ) 
			{
				if( !$item instanceof File || !$item->checkSize($rule) ) 
				{
					return false;
				}
			}
			return true;
		}
		else 
		{
			if( $file instanceof File ) 
			{
				return $file->checkSize($rule);
			}
			return false;
		}
	}
	protected function image($file, $rule) 
	{
		if( !$file instanceof File ) 
		{
			return false;
		}
		if( $rule ) 
		{
			$rule = explode(",", $rule);
			list($width, $height, $type) = getimagesize($file->getRealPath());
			if( isset($rule[2]) ) 
			{
				$imageType = strtolower($rule[2]);
				if( "jpeg" == $imageType ) 
				{
					$imageType = "jpg";
				}
				if( image_type_to_extension($type, false) != $imageType ) 
				{
					return false;
				}
			}
			list($w, $h) = $rule;
			return $w == $width && $h == $height;
		}
		return in_array($this->getImageType($file->getRealPath()), array( 1, 2, 3, 6 ));
	}
	protected function method($value, $rule) 
	{
		$method = Request::instance()->method();
		return strtoupper($rule) == $method;
	}
	protected function dateFormat($value, $rule) 
	{
		$info = date_parse_from_format($rule, $value);
		return 0 == $info["warning_count"] && 0 == $info["error_count"];
	}
	protected function unique($value, $rule, $data, $field) 
	{
		if( is_string($rule) ) 
		{
			$rule = explode(",", $rule);
		}
		if( false !== strpos($rule[0], "\\") ) 
		{
			$db = new $rule[0]();
		}
		else 
		{
			try 
			{
				$db = Loader::model($rule[0]);
			}
			catch( exception\ClassNotFoundException $e ) 
			{
				$db = Db::name($rule[0]);
			}
		}
		$key = (isset($rule[1]) ? $rule[1] : $field);
		if( strpos($key, "^") ) 
		{
			$fields = explode("^", $key);
			foreach( $fields as $key ) 
			{
				$map[$key] = $data[$key];
			}
		}
		else 
		{
			if( strpos($key, "=") ) 
			{
				parse_str($key, $map);
			}
			else 
			{
				$map[$key] = $data[$field];
			}
		}
		$pk = (isset($rule[3]) ? $rule[3] : $db->getPk());
		if( is_string($pk) ) 
		{
			if( isset($rule[2]) ) 
			{
				$map[$pk] = array( "neq", $rule[2] );
			}
			else 
			{
				if( isset($data[$pk]) ) 
				{
					$map[$pk] = array( "neq", $data[$pk] );
				}
			}
		}
		if( $db->where($map)->field($pk)->find() ) 
		{
			return false;
		}
		return true;
	}
	protected function behavior($value, $rule, $data) 
	{
		return Hook::exec($rule, "", $data);
	}
	protected function filter($value, $rule) 
	{
		if( is_string($rule) && strpos($rule, ",") ) 
		{
			list($rule, $param) = explode(",", $rule);
		}
		else 
		{
			if( is_array($rule) ) 
			{
				$param = (isset($rule[1]) ? $rule[1] : null);
				$rule = $rule[0];
			}
			else 
			{
				$param = null;
			}
		}
		return false !== filter_var($value, (is_int($rule) ? $rule : filter_id($rule)), $param);
	}
	protected function requireIf($value, $rule, $data) 
	{
		list($field, $val) = explode(",", $rule);
		if( $this->getDataValue($data, $field) == $val ) 
		{
			return !empty($value) || "0" == $value;
		}
		return true;
	}
	protected function requireCallback($value, $rule, $data) 
	{
		$result = call_user_func_array($rule, array( $value, $data ));
		if( $result ) 
		{
			return !empty($value) || "0" == $value;
		}
		return true;
	}
	protected function requireWith($value, $rule, $data) 
	{
		$val = $this->getDataValue($data, $rule);
		if( !empty($val) ) 
		{
			return !empty($value) || "0" == $value;
		}
		return true;
	}
	protected function in($value, $rule) 
	{
		return in_array($value, (is_array($rule) ? $rule : explode(",", $rule)));
	}
	protected function notIn($value, $rule) 
	{
		return !in_array($value, (is_array($rule) ? $rule : explode(",", $rule)));
	}
	protected function between($value, $rule) 
	{
		if( is_string($rule) ) 
		{
			$rule = explode(",", $rule);
		}
		list($min, $max) = $rule;
		return $min <= $value && $value <= $max;
	}
	protected function notBetween($value, $rule) 
	{
		if( is_string($rule) ) 
		{
			$rule = explode(",", $rule);
		}
		list($min, $max) = $rule;
		return $value < $min || $max < $value;
	}
	protected function length($value, $rule) 
	{
		if( is_array($value) ) 
		{
			$length = count($value);
		}
		else 
		{
			if( $value instanceof File ) 
			{
				$length = $value->getSize();
			}
			else 
			{
				$length = mb_strlen((string) $value);
			}
		}
		if( strpos($rule, ",") ) 
		{
			list($min, $max) = explode(",", $rule);
			return $min <= $length && $length <= $max;
		}
		return $length == $rule;
	}
	protected function max($value, $rule) 
	{
		if( is_array($value) ) 
		{
			$length = count($value);
		}
		else 
		{
			if( $value instanceof File ) 
			{
				$length = $value->getSize();
			}
			else 
			{
				$length = mb_strlen((string) $value);
			}
		}
		return $length <= $rule;
	}
	protected function min($value, $rule) 
	{
		if( is_array($value) ) 
		{
			$length = count($value);
		}
		else 
		{
			if( $value instanceof File ) 
			{
				$length = $value->getSize();
			}
			else 
			{
				$length = mb_strlen((string) $value);
			}
		}
		return $rule <= $length;
	}
	protected function after($value, $rule) 
	{
		return strtotime($rule) <= strtotime($value);
	}
	protected function before($value, $rule) 
	{
		return strtotime($value) <= strtotime($rule);
	}
	protected function expire($value, $rule) 
	{
		if( is_string($rule) ) 
		{
			$rule = explode(",", $rule);
		}
		list($start, $end) = $rule;
		if( !is_numeric($start) ) 
		{
			$start = strtotime($start);
		}
		if( !is_numeric($end) ) 
		{
			$end = strtotime($end);
		}
		return $start <= $_SERVER["REQUEST_TIME"] && $_SERVER["REQUEST_TIME"] <= $end;
	}
	protected function allowIp($value, $rule) 
	{
		return in_array($_SERVER["REMOTE_ADDR"], (is_array($rule) ? $rule : explode(",", $rule)));
	}
	protected function denyIp($value, $rule) 
	{
		return !in_array($_SERVER["REMOTE_ADDR"], (is_array($rule) ? $rule : explode(",", $rule)));
	}
	protected function regex($value, $rule) 
	{
		if( isset($this->regex[$rule]) ) 
		{
			$rule = $this->regex[$rule];
		}
		if( 0 !== strpos($rule, "/") && !preg_match("/\\/[imsU]{0,4}\$/", $rule) ) 
		{
			$rule = "/^" . $rule . "\$/";
		}
		return is_scalar($value) && 1 === preg_match($rule, (string) $value);
	}
	protected function token($value, $rule, $data) 
	{
		$rule = (!empty($rule) ? $rule : "__token__");
		if( !isset($data[$rule]) || !Session::has($rule) ) 
		{
			return false;
		}
		if( isset($data[$rule]) && Session::get($rule) === $data[$rule] ) 
		{
			Session::delete($rule);
			return true;
		}
		Session::delete($rule);
		return false;
	}
	public function getError() 
	{
		return $this->error;
	}
	protected function getDataValue($data, $key) 
	{
		if( is_numeric($key) ) 
		{
			$value = $key;
		}
		else 
		{
			if( strpos($key, ".") ) 
			{
				list($name1, $name2) = explode(".", $key);
				$value = (isset($data[$name1][$name2]) ? $data[$name1][$name2] : null);
			}
			else 
			{
				$value = (isset($data[$key]) ? $data[$key] : null);
			}
		}
		return $value;
	}
	protected function getRuleMsg($attribute, $title, $type, $rule) 
	{
		if( isset($this->message[$attribute . "." . $type]) ) 
		{
			$msg = $this->message[$attribute . "." . $type];
		}
		else 
		{
			if( isset($this->message[$attribute][$type]) ) 
			{
				$msg = $this->message[$attribute][$type];
			}
			else 
			{
				if( isset($this->message[$attribute]) ) 
				{
					$msg = $this->message[$attribute];
				}
				else 
				{
					if( isset(self::$typeMsg[$type]) ) 
					{
						$msg = self::$typeMsg[$type];
					}
					else 
					{
						if( 0 === strpos($type, "require") ) 
						{
							$msg = self::$typeMsg["require"];
						}
						else 
						{
							$msg = $title . Lang::get("not conform to the rules");
						}
					}
				}
			}
		}
		if( is_string($msg) && 0 === strpos($msg, "{%") ) 
		{
			$msg = Lang::get(substr($msg, 2, -1));
		}
		else 
		{
			if( Lang::has($msg) ) 
			{
				$msg = Lang::get($msg);
			}
		}
		if( is_string($msg) && is_scalar($rule) && false !== strpos($msg, ":") ) 
		{
			if( is_string($rule) && strpos($rule, ",") ) 
			{
				$array = array_pad(explode(",", $rule), 3, "");
			}
			else 
			{
				$array = array_pad(array( ), 3, "");
			}
			$msg = str_replace(array( ":attribute", ":rule", ":1", ":2", ":3" ), array( $title, (string) $rule, $array[0], $array[1], $array[2] ), $msg);
		}
		return $msg;
	}
	protected function getScene($scene = "") 
	{
		if( empty($scene) ) 
		{
			$scene = $this->currentScene;
		}
		if( !empty($scene) && isset($this->scene[$scene]) ) 
		{
			$scene = $this->scene[$scene];
			if( is_string($scene) ) 
			{
				$scene = explode(",", $scene);
			}
		}
		else 
		{
			$scene = array( );
		}
		return $scene;
	}
	public static function __callStatic($method, $params) 
	{
		$class = self::make();
		if( method_exists($class, $method) ) 
		{
			return call_user_func_array(array( $class, $method ), $params);
		}
		throw new \BadMethodCallException("method not exists:" . "think\\Validate" . "->" . $method);
	}
}
?>