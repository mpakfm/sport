<?php

namespace classes;

class Mysql {
	public static $obj;
	
	public $mysqli;
	public $db_error;
	public $db_query;
	
	/**
	 * Singletone initiation
	 * @param string $server
	 * @param string $user
	 * @param string $pwd
	 * @param string $database
	 * @return self
	 */
	public static function init($server,$user,$pwd,$database){
		return self::$obj ? self::$obj : self::$obj = new Mysql ($server,$user,$pwd,$database);
	}
	/**
	* Constructor
	* Create connect
	*/
	private function __construct($server,$user,$pwd,$database){
		$this->mysqli = new \mysqli($server, $user, $pwd, $database); 
		$this->mysqli->set_charset("utf8");
	}
	/**
	* Clone method
	* Denied in used
	*/
	private function __clone(){}
	
	public function query ($sql,$args=array()) {
		$this->db_query = $sql;
		if (!empty($args)) {
			$stmp = $this->mysqli->prepare($sql);
			if ( $this->mysqli->errno )  {
				$di = debug_backtrace();
				\Mpakfm\Printu::log("query: {$this->db_query}\nMessage: ".$this->mysqli->error." in file: {$di[0]['file']}, line: {$di[0]['line']}","DB ERROR",'file','mysql.log');
			}
			$stmp->bind_param("s",$p );
			if (count($args)==1) $p = $args[0];
			else $p = $args;
			//$stmp->bind_param("s",&$args[0] );
			$stmp->execute();
			$stmp->bind_result($line);
			return $stmp;
		} else {
			$result = $this->mysqli->query($sql);
			if ( $this->mysqli->errno )  {
				$di = debug_backtrace();
				\Mpakfm\Printu::log("query: {$this->db_query}\nMessage: ".$this->mysqli->error." in file: {$di[0]['file']}, line: {$di[0]['line']}","DB ERROR",'file','mysql.log');
			}
			return $result;
		}
		
	}
	/**
	* put your comment there...
	* 
	* @param mixed $result
	* @param mixed $output: obj,assoc,anum
	*/
	public function get_results ($result, $output='obj') {
		if (!$result) return false;
		$m = 'get_results_'.$output;
		return $this->$m($result);
	}
	/**
	* put your comment there...
	* 
	*/
	public function get_results_obj ($result) {
		if (!$result) return false;
		$ar = array();
		while ($obj = $result->fetch_object()) {
	    	$ar[] = $obj;
		}
		return $ar;
	}
	/**
	* put your comment there...
	* 
	*/
	public function get_results_assoc ($result) {
		if (!$result) return false;
		$ar = array();
		while ($assoc = $result->fetch_assoc()) {
	    	$ar[] = $assoc;
		}
		return $ar;
	}
	/**
	* put your comment there...
	* 
	*/
	public function get_results_anum ($result) {
		if (!$result) return false;
		$ar = array();
		while ($anum = $result->fetch_array(MYSQLI_NUM)) {
	    	$ar[] = $anum;
		}
		return $ar;
	}
	
	public function get_row ($result, $output='assoc') {
		if (!$result) return false;
		$m = 'get_row_'.$output;
		return $this->$m($result);
	}
	
	public function get_row_obj ($result) {
		if (!$result) return false;
		return $result->fetch_object();
	}
	
	public function get_row_assoc ($result) {
		if (!$result) return false;
		return $result->fetch_assoc();
	}
	
	public function get_row_anum ($result) {
		if (!$result) return false;
		return $result->fetch_row();
	}
	
	public function affected_rows () {
		return $this->mysqli->affected_rows;
	}
	
	public function num_rows ($result) {
		return $result->num_rows;
	}
	
	public function insert_id () {
		return $this->mysqli->insert_id;
	}
	
	public function __destruct () {
		$this->mysqli->close();
	}
	
}

