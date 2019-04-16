<?php
	namespace app\core;
	use \PDO;
	use \PDOExption;
	use \App\core\Registry;
	use app\core\AppException;
	
class Model{
		
		public static $intance;
		public $store;
		public $wheres;
		private $distinct=false;
		private $joins;
		private $group;
		private $having;
		private $order;
		private $limit;
		private $offset;
		protected $table;
		public $sql="SELECT * FROM";
		public $conn;
		public function __construct($column=null,$operation=null,$values=null,$boolean=null,$class){
			$this->table=$this->getModel($class);
			$this->conn=$this->connect();
			if ($column!="") {
				$this->wheres[]=[$column,$operation,$values,$boolean];
			}
			
		}
		public static  function where($column,$operation,$values,$boolean='and'){
			$class=get_called_class();
			return new self($column,$operation,$values,$boolean,$class);
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
		public static  function all(){
			$class=get_called_class();
			return new self('','','','',$class);

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

		public function get(){			
			if (!isset($this->table)||empty($this->table)) {
				return false;
			}
			/*$this->sql .=' '.$this->table;
			if (isset($this->wheres) && is_array($this->wheres)) {
				$this->sql.=" WHERE";
				foreach ($this->wheres as $key => $where) {
					$this->sql.= " $where[0] $where[1] $where[2] ";
					if ($key < (count($this->wheres)-1)) {
						$this->sql.= (strtolower($where[3])==='and')? ' AND' : ' OR';
					}

				}
			}*/
			$sql=$this->distinct ?'SELECT DISTINCT' : 'SELECT';
			if (isset($this->columns) && is_array($this->columns)) {
				$sql .=' '.implode(' ,', $this->columns);
			}else{
				$sql .= ' *';
			}
			$sql .=' FROM '.$this->table;
			if (isset($this->joins) && is_array($this->joins)) {
				foreach ($this->joins as $join) {
					switch (strtolower($join[4])) {
						case 'inner':
							$sql.= ' INNER JOIN';
							break;
						case 'left':
							$sql.= ' LEFT JOIN';
							break;
						case 'right':
							$sql.= ' RIGHT JOIN';
							break;
						
						default:
							$sql.= ' INNER JOIN';
							break;
					}
				}
				$sql .= " $join[0] ON $join[1] $join[2] $join[3]";
			}
			if (isset($this->wheres) && is_array($this->wheres)) {
				$sql.=" WHERE";
				foreach ($this->wheres as $key => $where) {
					$sql.= " $where[0] $where[1] $where[2] ";
					if ($key < (count($this->wheres)-1)) {
						$sql.= (strtolower($where[3])==='and')? ' AND' : ' OR';
					}

				}
			}
			if (isset($this->group) && is_array($this->group)) {
				$sql .= " GROUP BY ".implode(' ,', $this->group);
			}
			if (isset($this->having) && is_array($this->having)) {
				$sql.=" HAVING";
				foreach ($this->having as $key => $hv) {
					$sql.= " $hv[0] $hv[1] $hv[2] ";
					if ($key < (count($this->having)-1)) {
						$sql.= (strtolower($hv[3])==='and')? ' AND' : ' OR';
					}

				}
			}
			if (isset($this->orderBy) && is_array($this->orderBy)) {
				$sql.=" ORDERBY";
				foreach ($this->orderBy as $key => $odb) {
					$sql.= " $odb[0] $odb[1]";
					if ($key < (count($this->orderBy)-1)) {
						$sql.= ' ,';
					}

				}
			}
			if (isset($this->limit)){
				$sql.= " LIMIT ".$this->limit;
			}
			if (isset($this->offset)){
				$sql.= " OFFSET ".$this->offset;
			}
			$result = mysqli_query($this->conn, $sql);
			$data = [];
	        while ($row = mysqli_fetch_array($result)){
	            array_push($data, $row);
	        }
			return $data;
		}
		public function save(){
			$count=0;
			array_splice($this->store, 0, 1);
			$dem=count($this->store);
			if (isset($this->wheres) && is_array($this->wheres)) {
				$sql="UPDATE ".$this->table." SET ";
				foreach ($this->store as $key => $value) {
					$count++;
					if ($dem==$count) {
						$sql.=" ".$key."= '".$value."'";
					}else{
						$sql.=" ".$key."= '".$value."' , ";
					}
					
				}
				foreach ($this->wheres as $key => $where) {
					$sql.= "WHERE $where[0] $where[1] ' $where[2] '";
					if ($key < (count($this->wheres)-1)) {
						$sql.= (strtolower($where[3])==='and')? ' AND' : ' OR';
					}

				}
				return mysqli_query($this->conn, $sql);

			}else{
				if (!isset($this->table)||empty($this->table)) {
					return false;
				}
				$sql="INSERT INTO ".$this->table." (";
				foreach ($this->store as $key => $value) {
					$count++;
					if ($dem==$count) {
						$sql.=" ".$key;
					}else{
						$sql.=" ".$key.",";
					}

				}
				$count=0;
				$sql.=") VALUES (";
				foreach ($this->store as $key => $value) {
					$count++;
					if ($dem==$count) {
						$sql.=" '".$value."'";
					}else{
						$sql.="'".$value."', ";
					}
					
				}
				$sql.=")";
				$this->conn=$this->connect();
				return mysqli_query($this->conn, $sql);
			}
		}
		public function delete(){
			$sql="DELETE FROM ".$this->table." WHERE ";
			if (isset($this->wheres) && is_array($this->wheres)) {
				foreach ($this->wheres as $key => $where) {
					$sql.= " $where[0] $where[1] $where[2] ";
					if ($key < (count($this->wheres)-1)) {
						$sql.= (strtolower($where[3])==='and')? ' AND' : ' OR';
					}

				}
			}
			return mysqli_query($this->conn, $sql);
		}
		public function connect(){
			$this->config = Registry::getIntance()->config;
			$servername=$this->config['db']['host'];
			$dbname=$this->config['db']['name'];
			$username=$this->config['db']['user'];
			$password=$this->config['db']['password'];
			$this->conn = mysqli_connect($servername, $username, $password, $dbname);
			if (!$this->conn) {
				die("k the ket noi");
			}
			return $this->conn;
		}
		public function getModel($class){
			return str_replace('app\model\\', '', $class);
		}
	}
?>