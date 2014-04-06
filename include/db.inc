<?php



require_once("DB.php");
require_once("../conf.php");
require_once("log.inc");



function my_error() {
	print( "DB access error" );
	exit;
}



class dbac_class {
	var $db;
	var $err;
	var $err_msg;
	var $log;
	var $transaction = 0;

	function dbac_class() {
		$this->db = false;
		$this->log = new class_log;
	}
	
	function connect() {
		if( $this->db == false ) {
			global $g_dbhostname,$g_dbname,$g_dbusername,$g_dbpassword;
			$dsn = sprintf( "mysql://%s:%s@%s/%s", $g_dbusername,base64_decode($g_dbpassword),$g_dbhostname,$g_dbname );
			$this->db = DB::connect( $dsn );
			//$this->db = DB::connect("mysql://unix()/accounting");
			if( DB::isError($this->db) ) {
				printf( "database connection error %s\n", $this->db->getMessage() );
				exit(0);
			}
		//	$this->db->setErrorHandling(PEAR_ERROR_CALLBACK,"my_error");
		}
		return $this->db;
	}
	
	function close() {
		// PHP will automaticaly close.
		// if( $this->db ) pg_close($this->db);
		// $this->db = false;
	}

	function get_db() {
		return $this->db;
	}
	
	function query( $cmd ) {
		$res = $this->db->query( $cmd );
		if( DB::isError($res) ) {
			$this->err = $res;
			$this->log->err_log( "$cmd" );
			return false;
		}
		$this->log->log( $cmd );
		return $res;
	}

	function set_error($msg) {
		$this->err_msg = $msg;
	}

	function get_errormsg() {
		if( $this->err_msg!='' ) return $this->err_msg;
		return $this->err->getMessage();
	}

	function begin_transaction() {
		$result = true;
		if( $this->transaction==0 ) {
			$result = $this->query( "BEGIN" );
			if( $result==false ) return $result;
		}
		$this->transaction++;
		return $result;
	}

	function end_transaction() {
		$result = true;
		if( $this->transaction==1 ) {
			$result = $this->query( "COMMIT" );
		}
		$this->transaction--;
		return $result;
	}

	function rollback() {
		$this->query( "ROLLBACK" );
		$this->transaction--;
		return false;
	}
}



$g_dbac = new dbac_class;



?>
