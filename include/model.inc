<?php



require_once("db.inc");



define( "ROWTYPE_STRING",0 );
define( "ROWTYPE_INTEGER",1 );
define( "ROWTYPE_DATE",2 );
define( "ROWTYPE_BOOL",3 );



/**
 *  Base class that provides basic function for accessing the database.
 *  All classes that coresponds to certain table will be inherited from this class.
 */
class model {
	var $db;
	var $tblname;
	var $res;
	var $rowarray;
	var $idcol;
	var $writeable;

	function model( $tblname ) {
		$this->db = false;
		$this->tblname = $tblname;
		$this->rowarray = 0;
		$this->idcol = 0;
	}

	/**
	 * Connect to database.
	 * This method must be called before any other methods that access to the database.
	 * Set $writeable to true when you make changes in database.
	 * (i.e. INSERT, UPDATE, DELETE)
	 */
	function connect( $writeable=false ) {
		global $g_dbac;
		$this->db = $g_dbac->connect($writeable);
		$this->writeable = $writeable;
	}

	/**
	 * Close connection to database.
	 * This is not necessary to call, because PHP automatically closes.
	 */
	function close() {
		global $g_dbac;
		$g_dbac->close();
	}

	/**
	 * Runs query.
	 * $cmd is an SQL command.
	 * If SQL fails, it returns false.
	 * If success, it returns DB_result for queries and DB_OK for manipulation.
	 */
	function query( $cmd ) {
		if( ! $this->writeable ) {
			if( is_sqlwritecmd($cmd) ) {
				$this->set_error( "Permission denied" );
				return false;
			}
		}
		global $g_dbac;
		$this->res = $g_dbac->query( $cmd );
		return $this->res;
	}

	/**
	 * Runs general query for SELECTing all the rows.
	 * It returns DB_result or false if fail.
	 */
	function get_list() {
		return $this->query( "SELECT * FROM $this->tblname" );
	}
	
	/**
	 * Returns number of fields of previous query.
	 */
	function get_numfields() {
		if( ! $this->res ) return 0;
		return $this->res->numCols();
	}

	/**
	 * Returns number of rows of previous query.
	 */
	function get_numrows() {
		if( ! $this->res ) return 0;
		return $this->res->numRows();
	}
	
//	function get_result($i,$j) {
//		if( ! $this->res ) return null;
//		$array = $this->res->fetchRow(DB_FETCHMODE_DEFAULT,$i);
//		return $array[$j];
//	}

	/**
	 * Returns sequencial array of specified row of previous query.
	 */
	function get_fetch_array( $row ) {
		if( ! $this->res ) return null;
		return $this->res->fetchRow(DB_FETCHMODE_DEFAULT,$row);
	}

	/**
	 * Returns associative array of specified row of previous query.
	 */
	function get_fetch_assoc( $row ) {
		if( ! $this->res ) return null;
		return $this->res->fetchRow(DB_FETCHMODE_ASSOC,$row);
	}

	/**
	 * Sets the error.
	 */
	function set_error( $msg ) {
		global $g_dbac;
		$g_dbac->set_error($msg);
	}

	/**
	 * Get the error message that occured before.
	 */
	function get_errormsg() {
		global $g_dbac;
		return $g_dbac->get_errormsg();
		//return pg_errormessage($this->db);
		//return "";
	}

	/**
	 * Runs query to get all the columns of spceified PRIMARY KEY.
	 * Returns DB_result or false if fails.
	 */
	function get_by_id( $id ) {
		if( $id=="" ) return false;
		return $this->query( "SELECT * FROM $this->tblname WHERE " . $this->idcol  . "=" . $id );
	}

	/**
	 * Runs query to get all the columns of spceified row.
	 * $rowdata should be an associative array that holds field name and its value
	 * which you want to retrieve.
	 * Returns DB_result or false if fails.
	 */
	function get_id_by_cond( $rowdata ) {
		$cmd = "SELECT $this->idcol FROM $this->tblname";
		$n = 0;
		foreach( $this->rowarray as $field => $value ) {
			if( $rowdata[$field] != "" ) {
				if( $n==0 ) {
					$cmd .= " WHERE ";
				} else {
					$cmd .= " AND ";
				}
				$cmd .= $field . "='" . $rowdata[$field] . "'";
				$n++;
			}
		}
		if( $n>0 ) {
			$res = $this->query( $cmd );
			if( ($res != false) && ($res->numRows()==1) ) {
				list($id) = $res->fetchRow(DB_FETCHMODE_DEFAULT,0);
				return $id;
			}
		}
		return 0;
	}

	/**
	 * Runs "UPDATE" query.
	 * $rowdata should be associative array of filed name and its value to be updated.
	 */
	function update( $rowdata ) {
		$cmd = "UPDATE $this->tblname SET ";
		$n = 0;
		foreach( $this->rowarray as $field => $type ) {
			if( $field==$this->idcol ) continue;
			if( $rowdata[$field] != "" ) {
				if( $n!=0 ) $cmd .= ",";
				if( strcasecmp($rowdata[$field],'NULL')==0 ) {
					$cmd .= $field . "=" . $rowdata[$field];
				} else {
					if( $this->check_type($rowdata[$field],$type)==false ) return false;
					$cmd .= $field . "='" . $rowdata[$field] . "'";
				}
				$n++;
			}
		}
		if( $n==0 ) return false;
		$cmd .= " WHERE " . $this->idcol . "=" . $rowdata[$this->idcol];
		return $this->query( $cmd );
	}
	
	/**
	 * Runs "UPDATE" query.
	 * $cond is associative array of field name and value for the condition.
	 * $rowdata should be associative array of field name and its value to be updated.
	 */
	function update_cond( $cond, $rowdata ) {
		$cmd = "UPDATE $this->tblname SET ";
		$n = 0;
		foreach( $this->rowarray as $field => $type ) {
			if( $rowdata[$field] != "" ) {
				if( $n!=0 ) $cmd .= ",";
				if( strcasecmp($rowdata[$field],'NULL')==0 ) {
					$cmd .= $field . "=" . $rowdata[$field];
				} else {
					if( $this->check_type($rowdata[$field],$type)==false ) return false;
					$cmd .= $field . "='" . $rowdata[$field] . "'";
				}
				$n++;
			}
		}
		if( $n==0 ) return false;
		
		$cmd .= " WHERE ";
		$n = 0;
		foreach( $cond as $field => $value ) {
			if( $n!=0 ) $cmd .= " AND ";
			if( strcasecmp($value,'NULL')==0 ) {
				$cmd .= "$field IS NULL";
			} else {
				$cmd .= "$field='$value'";
			}
			$n++;
		}
		if( $n==0 ) return false;
		
		return $this->query( $cmd );
	}

	/**
	 * Runs "INSERT" query.
	 * $rowdata should be associative array of filed name and its value to be added.
	 */
	function add( $rowdata ) {
		$cmd = "INSERT INTO $this->tblname (";
		$n = 0;
		foreach( $this->rowarray as $field => $value ) {
			if( $rowdata[$field] != "" ) {
				if( $n!=0 ) $cmd .= ",";
				$cmd .= $field;
				$n++;
			}
		}
		if( $n==0 ) return false;
		$cmd .= ") VALUES (";
		$n = 0;
		foreach( $this->rowarray as $field => $type ) {
			if( $rowdata[$field] != "" ) {
				if( $n!=0 ) $cmd .= ",";
				if( strcasecmp($rowdata[$field],'NULL')==0 ) {
					$cmd .= $rowdata[$field];
				} else {
					if( $this->check_type($rowdata[$field],$type)==false ) return false;
					$cmd .= "'" . $rowdata[$field] . "'";
				}
				$n++;
			}
		}
		$cmd .= ")";
		return $this->query( $cmd );
	}

	/**
	 * Runs 'INSERT' to add multiple data given by $array.
	 **/
//	function add_array( $array ) {
//		$count = 0;
//		if( $this->begin_transaction()==false ) return $this->rollback();
//		foreach( $array as $dat ) {
//			if( $this->add( $dat )==false ) return $this->rollback();
//			$count++;
//		}
//		if( $this->end_transaction()==false ) return $this->rollback();
//		return $count;
//	}
	function add_array( $array ) {
		// search all fields
		$fieldarray = array();
		foreach( $array as $dat ) {
			foreach( array_keys($dat) as $field ) {
				if( ! in_array($field,$fieldarray) ) $fieldarray[] = $field;
			}
		}
		// make INSERT INTO cmd
		$cmd = "INSERT INTO $this->tblname (";
		$n = 0;
		foreach( $fieldarray as $field ) {
			if( ! array_key_exists($field,$this->rowarray) ) {
				$this->set_error( "field $field not exists" );
				return false;
			}
			if( $n!=0 ) $cmd .= ",";
			$cmd .= $field;
			$n++;
		}
		if( $n==0 ) return false;
		$cmd .= ") VALUES";
		$cound = 0;
		foreach( $array as $rowdata ) {
			if( $count!=0 ) $cmd .= ",";
			$cmd .= " (";
			$n = 0;
			foreach( $fieldarray as $field ) {
				if( $n!=0 ) $cmd .= ",";
				if( $rowdata[$field]=="" ) {
					$cmd .= "DEFAULT";
				} else if( strcasecmp($rowdata[$field],'NULL')==0 ) {
					$cmd .= $rowdata[$field];
				} else {
					if( $this->check_type($rowdata[$field],$this->rowarray[$field])==false ) return false;
					$cmd .= "'" . $rowdata[$field] . "'";
				}
				$n++;
			}
			$cmd .= ")";
			$count++;
		}
		return $this->query( $cmd );
	}
	
	// automatically adds data using maximum id
	function add_auto( $rowdata ) {
		if( $this->begin_transaction()==false ) return $this->rollback();
		$cmd = "SELECT max($this->idcol) FROM $this->tblname FOR UPDATE";
		if( $this->query( $cmd )==false ) return $this->rollback();
		list($id) = $this->get_fetch_array(0);
		$rowdata[$this->idcol] = $id + 1;
		if( $this->add( $rowdata )==false ) return $this->rollback();
		if( $this->end_transaction()==false ) return $this->rollback();
		return $rowdata[$this->idcol];
	}

	// automatically adds data by using minimum id which is not in use.
	function add_auto_compliment( $rowdata ) {
		if( $this->begin_transaction()==false ) return $this->rollback();
		$cmd = "SELECT $this->idcol FROM $this->tblname ORDER BY $this->idcol FOR UPDATE";
		if( $this->query( $cmd )==false ) return $this->rollback();
		$id = 1;
		for( $n=0; $n<$this->get_numrows(); $n++ ) {
			$dat = $this->get_fetch_array($n);
			if( $id < $dat[0] ) break;
			$id++;
		}
		$rowdata[$this->idcol] = $id;
		if( $this->add( $rowdata )==false ) return $this->rollback();
		if( $this->end_transaction()==false ) return $this->rollback();
		return $rowdata[$this->idcol];
	}

	function del( $id ) {
		return $this->query( "DELETE FROM $this->tblname WHERE " . $this->idcol . "='" . $id . "'" );
	}

	function del_cond( $rowdata ) {
		$cmd = "DELETE FROM $this->tblname WHERE ";
		foreach( $this->rowarray as $field => $value ) {
			if( $rowdata[$field] != "" ) {
				if( $n!=0 ) $cmd .= "AND ";
				if( strcasecmp($rowdata[$field],'NULL')==0 ) {
					$cmd .= $field . "=" . $rowdata[$field];
				} else {
					$cmd .= $field . "='" . $rowdata[$field] . "'";
				}
				$n++;
			}
		}
		return $this->query( $cmd );
	}

	function begin_transaction() {
		global $g_dbac;
		return $g_dbac->begin_transaction();
	}

	function end_transaction() {
		global $g_dbac;
		return $g_dbac->end_transaction();
	}

	function rollback() {
		global $g_dbac;
		return $g_dbac->rollback();
	}

	/**
	 * Checks the value if it matches certain type.
	 * It also trim the white space in beginning and end of the string. (if type is string).
	 * Returns true if ok, false if not ok.
	 */
	function check_type( &$value, $type ) {
		switch( $type ) {
			case ROWTYPE_INTEGER:
				if( ! is_numeric($value) ) {
					$this->set_error( "bad integer '$value'" );
					return false;
				}
				break;
			case ROWTYPE_STRING:
				$value = str_replace( array('<','>'),'',$value );	// remove '<' and '>' for security
				$value = trim($value);	// remove unnecessary white space
				break;
			case ROWTYPE_DATE:
				list($year,$month,$day) = sscanf( $value,"%d-%d-%d" );
				if( ( ($year<=1900) || ($year>3000) ) ||
					( ($month<=0) || ($month>12) ) ||
					( ($day<=0) || ($day>31) ) ) {
					$this->set_error( "bad date '$value'" );
					return false;
				}
				break;
		}
		return true;
	}
}



?>
