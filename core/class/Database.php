<?php
class Database{

	private $host;
	private $username;
	private $password;
	private $db;
	public $link;

	public function __construct(){
		$this->host = "localhost";
		$this->username = "root";
		$this->password = "root";
		$this->database = "Mini_Bizi";
		$this->link = new mysqli($this->host, $this->username,$this->password,$this->database);
		if (mysqli_connect_errno()) {
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit();
		}
	}

	public function log_db_errors($error, $query){

		$message = '<p>Error at '. date('Y-m-d H:i:s').':</p>';
		$message .= '<p>Query: '. htmlentities($query).'<br />';
		$message .= 'Error: ' . $error;
		$message .= '</p>';

		echo $message ;
	}

	public function filter($data){

		if(!is_array($data)){
			$data = trim(htmlentities($data));
			$data = $this->link->real_escape_string($data);
		}else{
			$data = array_map(array('Database','filter'),$data);
		}
		return $data;
	}

	public function query($query){

		$totalQuery = $this->link->query($query);
		if($this->link->error){
			$totalQuery->free();
			return false;
		}
		else{
			$totalQuery->free();
			return true;
		}
	}

	public function num_rows($query){

		$numRows = $this->link->query($query);
		if($this->link->error){
			return $this->link->error;
		}else{
			return $numRows->num_rows;
		}
	}

	public function getRow($query){

		$row = $this->link->query($query);
		if($this->link->error){
			return false;
		}
		else{
			return $row->fetch_assoc();
		}
	}

	public function getRowNotAssoc($query){

		$row = $this->link->query($query);
		if($this->link->error){
			return false;
		}
		else{
			return $row->fetch_row();
		}
	}

	public function getResults($query){

		$row = null;
		$results = $this->link->query($query);
		if($this->link->error){
			return false;
		}else{
			$row = array();
			while($r = $results->fetch_assoc()){
				$row[] = $r;
			}
			return $row;
		}
	}

	public function insert($table, $variables = array()){

		if(empty($variables)){
			return false;
			exit;
		}

		$sql = "INSERT INTO ". $table;
		$fields = array();
		$values = array();
		foreach($variables as $field => $value){
			$fields[] = $field;
			$values[] = "'".$value."'";
		}
		$fields = ' (' . implode(', ', $fields) . ')';
		$values = '('. implode(', ', $values) .')';
		$sql .= $fields .' VALUES '. $values;
		$query = $this->link->query( $sql );

		if($this->link->error){
			return false;
		}else{
			return true;
		}
	}

	public function update($table, $variables = array(), $where = array(), $limit = ''){
		
		if(empty($variables)){
			return false;
			exit;
		}
		
		$sql = "UPDATE ". $table ." SET ";
		foreach($variables as $field => $value){
			$updates[] = "`$field` = '$value'";
		}
		
		$sql .= implode(', ', $updates);
		
		if(!empty($where)){
			foreach($where as $field => $value){
				$value = $value;
				$clause[] = "$field = '$value'";
			}
			$sql .= ' WHERE '. implode(' AND ', $clause);
		}

		if( !empty( $limit ) ){
			$sql .= ' LIMIT '. $limit;
		}

		$query = $this->link->query($sql);

		if($this->link->error){
			return false;
		}else{
			return true;
		}
	}

	public function delete($table, $where = array(), $limit = ''){

		if(empty($where)){
			return false;
			exit;
		}

		$sql = "DELETE FROM ". $table;
		
		foreach( $where as $field => $value ){
			$value = $value;
			$clause[] = "$field = '$value'";
		}
		
		$sql .= " WHERE ". implode(' AND ', $clause);

		if(!empty($limit)){
			$sql .= " LIMIT ". $limit;
		}

		$query = $this->link->query( $sql );

		if($this->link->error){
			$this->log_db_errors($this->link->error, $sql);
			return false;
		}
		else{
			return true;
		}
	}

	public function exists($table = '', $check_val = '', $params = array()){

		if(empty($table) || empty($check_val) || empty($params)){
			return false;
			exit;
		}
		$check = array();
		foreach($params as $field => $value){
			if( !empty( $field ) && !empty( $value ) ){
				$check[] = "$field = '$value'";
			}
		}

		$check = implode(' AND ', $check);
		$rs_check = "SELECT $check_val FROM ".$table." WHERE $check";
		$numRows = $this->num_rows( $rs_check );

		if($numRows === 0) return false;
		else return true;
		exit;
	}

	protected function __destruct(){
		$this->disconnect();
	}

	protected function disconnect(){
		$this->link->close();
	}
}
?>