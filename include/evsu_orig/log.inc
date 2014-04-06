<?php


require_once("auth.inc");


class class_log {
	var $in_transaction;
	var $fp;
	var $log_buf = array();

	function class_log() {
	//	$this->$fp = false;
	//	$this->$in_transaction = false;
	}

	function write( $array ) {
	//	$fp = @fopen("../sis.log","a+");
	//	if( $fp ) {
	//		flock($fp,LOCK_EX);
	//		if( count($array) > 0 ) {
	//			foreach( $array as $cmd ) {
	//				$log = date("Y/m/d H:i:s") . "\t" . auth_get_username() . "\t" . $cmd . "\n";
	//				fputs($fp,$log);
	//			}
	//		}
	//		fclose($fp);
	//	}
	}

	function log( $cmd ) {
	//	if( ! $this->in_transaction ) {
	//		if( strncasecmp($cmd,"BEGIN",5)==0 ) {
	//			$this->in_transaction = true;
	//		} else if( is_sqlwritecmd($cmd) ) {
	//			$this->write( array($cmd) );
	//		}
	//	} else {
	//		if( strncasecmp($cmd,"COMMIT",6)==0 ) {
	//			$this->write( $this->log_buf );
	//			$this->in_transaction = false;
	//			unset( $this->log_buf );
	//		} else if( strncasecmp($cmd,"ROLLBACK",8)==0 ) {
	//			$this->in_transaction = false;
	//			unset( $this->log_buf );
	//		} else if( is_sqlwritecmd($cmd) ) {
	//			$this->log_buf[] = $cmd;
	//		}
	//	}
	}

	function err_log( $cmd ) {
	//	$this->write( array("Error: $cmd") );
	}
}



function is_sqlwritecmd( $cmd )
{
	//if( (strncasecmp($cmd,"INSERT",6)==0) ||
	//	(strncasecmp($cmd,"UPDATE",6)==0) ||
	//	(strncasecmp($cmd,"DELETE",6)==0) ) {
	//	return true;
	//} else {
	//	return false;
	//}
}



?>
