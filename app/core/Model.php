<?php
	namespace app\core;
	use \PDO;
	use \PDOExption;
	use \App\core\Registry;
	use app\core\AppException;
	
class Model{
		public  static $table;
		public $store;
		public static $test="ddđ";
		public static $wheres;
		private $distinct=false;
		private $joins;
		private $group;
		private $having;
		private $order;
		private $limit;
		private $offset;
		public static $sql;
		public static $conn;
	
		public function __construct(){
			static::$conn=static::connect();
		}

		public function __call($method, $params)
	    {
	        
	    }
	    public static function __callStatic($method, $args)
		{
			$getClass=get_called_class();
			$getModel=str_replace('app\model\\', '', $getClass);
			$classNamespace = 'app\\model\\'.$getModel;
			$model= new $classNamespace;
			if ( function_exists(call_user_func_array([$model,$method], $args))) {
				call_user_func_array([$model,$method], $args);
			}else
			{
				echo "không có function";
			}
		}
		public function where($column=null,$operation=null,$values=null,$boolean='and')
		{
			if ($column!="") 
			{
				self::$wheres[]=[$column,$operation,$values,$boolean];
			}
			return new static();
		}

		public function distinct(){
			$this->distinct=true;
			return $this;
		}
		public function join($table,$first,$operator,$second,$type='inner'){
			$this->joins[]=[$table,$first,$operator,$second,$type];
			return $this;

		}
		public function leftjoin($table,$first,$operator,$second,$type='left'){
			$this->joins[]=[$table,$first,$operator,$second,$type];
			return $this;

		}
		public function rightjoin($table,$first,$operator,$second,$type='right'){
			$this->joins[]=[$table,$first,$operator,$second,$type];
			return $this;

		}
		public function groupBy($columns){
			$this->group=is_array($columns)?$columns:func_get_args();
			return $this;
		}
		public function having($column,$operation,$values,$boolean='and'){
			$this->having[]=[$column,$operation,$values,$boolean];
			return $this;
			
		}
		public function orhaving($column,$operation,$values,$boolean='or'){
			$this->having[]=[$column,$operation,$values,$boolean];
			return $this;
			
		}
		public function orderBy($column,$derection='ASC'){
			$this->orderBy[]=[$column,$derection];
			return $this;			
		}
		public function limit($limit){
			$this->limit=$limit;
			return $this;
		}
		public function offset($offset){
			$this->offset=$offset;
			return $this;
		}
		public  function all(){
			self::$sql="SELECT * FROM ".static::$table;
			$connn=static::connect();
			$result = mysqli_query($connn, self::$sql);
			$data = [];
	        while ($row = mysqli_fetch_array($result)){
	            array_push($data, $row);
	        }
			return $data;
		}
		public  function find($id){
			/*self::$sql="SELECT * FROM ".static::$table." WHERE id= ".$id;
			$connn=static::connect();
			$result = mysqli_query($connn, self::$sql);
			$data = [];
	        while ($row = mysqli_fetch_array($result)){
	            array_push($data, $row);
	        }
			return $data;*/
			self::$wheres[]=['id','=',$id,'and'];
			return new static();
		}
		public function __set($name,$value){
			if( !isset($this->store[$name]) )
				$this->store[$name] = $value;
			else
				throw new AppException("Can't not set \"$value\" to \"$name\", $name already exists");
		}

		public function __get($name){
			if( isset($this->store[$name]) )
				return $this->store[$name];
			return null;
		}

		public  function  getSQL(){
			if (!isset(static::$table)||empty(static::$table)) {
				return false;
			}
			self::$sql=$this->distinct ?'SELECT DISTINCT' : 'SELECT';
			if (isset($this->columns) && is_array($this->columns)) {
				self::$sql .=' '.implode(' ,', $this->columns);
			}else{
				self::$sql .= ' *';
			}
			self::$sql .=' FROM '.static::$table;
			if (isset($this->joins) && is_array($this->joins)) {
				foreach ($this->joins as $join) {
					switch (strtolower($join[4])) {
						case 'inner':
							self::$sql.= ' INNER JOIN';
							break;
						case 'left':
							self::$sql.= ' LEFT JOIN';
							break;
						case 'right':
							self::$sql.= ' RIGHT JOIN';
							break;
						
						default:
							self::$sql.= ' INNER JOIN';
							break;
					}
				}
				self::$sql .= " $join[0] ON $join[1] $join[2] $join[3]";
			}
			if (isset(static::$wheres) && is_array(static::$wheres)) {
				self::$sql.=" WHERE";
				foreach (static::$wheres as $key => $where) {
					self::$sql.= " $where[0] $where[1] $where[2] ";
					if ($key < (count(static::$wheres)-1)) {
						self::$sql.= (strtolower($where[3])==='and')? ' AND' : ' OR';
					}

				}
			}
			if (isset($this->group) && is_array($this->group)) {
				self::$sql .= " GROUP BY ".implode(' ,', $this->group);
			}
			if (isset($this->having) && is_array($this->having)) {
				self::$sql.=" HAVING";
				foreach ($this->having as $key => $hv) {
					self::$sql.= " $hv[0] $hv[1] $hv[2] ";
					if ($key < (count($this->having)-1)) {
						self::$sql.= (strtolower($hv[3])==='and')? ' AND' : ' OR';
					}

				}
			}
			if (isset($this->orderBy) && is_array($this->orderBy)) {
				self::$sql.=" ORDERBY";
				foreach ($this->orderBy as $key => $odb) {
					self::$sql.= " $odb[0] $odb[1]";
					if ($key < (count($this->orderBy)-1)) {
						self::$sql.= ' ,';
					}

				}
			}
			if (isset($this->limit)){
				self::$sql.= " LIMIT ".$this->limit;
			}
			if (isset($this->offset)){
				self::$sql.= " OFFSET ".$this->offset;
			}
			return self::$sql;
		}
		public function toSQL(){
			self::$sql=$this->getSQL();
			echo self::$sql;
		}
		public function get(){			
			self::$sql=$this->getSQL();
			$result = mysqli_query(static::$conn, self::$sql);
			$data = [];
	        while ($row = mysqli_fetch_array($result)){
	            array_push($data, $row);
	        }
			return $data;
		}
		public function save(){
			$count=0;
			array_splice($this->store, 0, 0);
			$dem=count($this->store);
			if (isset(static::$wheres) && is_array(static::$wheres)) {
				self::$sql="UPDATE ".static::$table." SET ";
				foreach ($this->store as $key => $value) {
					$count++;
					if ($dem==$count) {
						self::$sql.=" ".$key."= '".$value."'";
					}else{
						self::$sql.=" ".$key."= '".$value."' , ";
					}
					
				}
				foreach (static::$wheres as $key => $where) {
					self::$sql.= "WHERE $where[0] $where[1] ' $where[2] '";
					if ($key < (count(static::$wheres)-1)) {
						self::$sql.= (strtolower($where[3])==='and')? ' AND' : ' OR';
					}

				}
				return mysqli_query(static::$conn, self::$sql);

			}else{
				if (!isset(static::$table)||empty(static::$table)) {
					return false;
				}
				self::$sql="INSERT INTO ".static::$table." (";
				foreach ($this->store as $key => $value) {
					$count++;
					if ($dem==$count) {
						self::$sql.=" ".$key;
					}else{
						self::$sql.=" ".$key.",";
					}

				}
				$count=0;
				self::$sql.=") VALUES (";
				foreach ($this->store as $key => $value) {
					$count++;
					if ($dem==$count) {
						self::$sql.=" '".$value."'";
					}else{
						self::$sql.="'".$value."', ";
					}					
				}
				self::$sql.=")";
				static::$conn=static::connect();
				return mysqli_query(static::$conn, self::$sql);
			}
		}
		public function delete(){
			self::$sql="DELETE FROM ".static::$table." WHERE ";
			if (isset(static::$wheres) && is_array(static::$wheres)) {
				foreach (static::$wheres as $key => $where) {
					self::$sql.= " $where[0] $where[1] $where[2] ";
					if ($key < (count(static::$wheres)-1)) {
						self::$sql.= (strtolower($where[3])==='and')? ' AND' : ' OR';
					}
				}
			}
			return mysqli_query(static::$conn, self::$sql);
		}
		public static function connect(){
			echo "connect <br>";
			$config = Registry::getIntance()->config;
			$servername=$config['db']['host'];
			$dbname=$config['db']['name'];
			$username=$config['db']['user'];
			$password=$config['db']['password'];
			static::$conn = mysqli_connect($servername, $username, $password, $dbname);
			if (!static::$conn) {
				die("k the ket noi");
			}
			return static::$conn;
		}
		public function getModel($class){
			return str_replace('app\model\\', '', $class);
		}
	}
?>