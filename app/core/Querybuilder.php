<?php 
	namespace App\core;
	use \PDO;
	use \PDOExption;
	use \App\core\Registry;
	use App\core\AppException;

	/**
	 * Querybuilder
	 */
	class Querybuilder
	{
		private $columns;
		private $from;
		private $distinct=false ;
		private $joins;
		private $wheres;
		private $group;
		private $having;
		private $order;
		private $limit;
		private $offset;
		private $conn;

		function __construct($tableName)
		{
			$this->from=$tableName;
			$this->conn=$this->connect();

		}
		public static function table($tableName){
			return new self($tableName);
		}
		public function select($columns){
			$this->columns=is_array($columns)?$columns:func_get_args();
			return $this;

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
		public function where($column,$operation,$values,$boolean='and'){
			$this->wheres[]=[$column,$operation,$values,$boolean];
			return $this;
			
		}
		public function orwhere($column,$operation,$values,$boolean='or'){
			$this->wheres[]=[$column,$operation,$values,$boolean];
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
		public function get(){
			if (!isset($this->from)||empty($this->from)) {
				return false;
			}
			$sql=$this->distinct ?'SELECT DISTINCT' : 'SELECT';
			if (isset($this->columns) && is_array($this->columns)) {
				$sql .=' '.implode(' ,', $this->columns)." ";
			}else{
				$sql .= ' * ';
			}
			$sql .=' FROM '.$this->from;
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
		public function insert($array){
			if (!isset($this->from)||empty($this->from)) {
				return false;
			}
			$count=0;
			$dem=count($array);
			$sql="INSERT INTO ".$this->from." (";
			foreach ($array as $key => $value) {
				$count++;
				if ($dem==$count) {
					$sql.=" ".$key;
				}else{
					$sql.=" ".$key.",";
				}
			}
			$count=0;
			$sql.=") VALUES (";
			foreach ($array as $key => $value) {
				$count++;
				if ($dem==$count) {
					$sql.=" '".$value."'";
				}else{
					$sql.="'".$value."', ";
				}
				
			}
			$sql.=")";
			/*$this->conn=$this->connect();*/
			
			return mysqli_query($this->conn, $sql);
		}
		public function update($array){
			if (!isset($this->from)||empty($this->from)) {
				return false;
			}
			$count=0;
			$dem=count($array);
			$sql="UPDATE ".$this->from." SET ";
				foreach ($array as $key => $value) {
					$count++;
					if ($dem==$count) {
						$sql.=" ".$key."= '".$value."' ";
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
			$sql.=")";
			/*$this->conn=$this->connect();*/
			
			return mysqli_query($this->conn, $sql);
		}
		public function delete(){
			$sql="DELETE FROM ".$this->from." WHERE ";
			if (isset($this->wheres) && is_array($this->wheres)) {
				foreach (static::$wheres as $key => $where) {
					$sql.= " $where[0] $where[1] $where[2] ";
					if ($key < (count($this->$wheres)-1)) {
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
		
	}

?>