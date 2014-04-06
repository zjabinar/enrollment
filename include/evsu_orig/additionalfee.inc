<?php



require_once("db.inc");
require_once("model.inc");



/**
 * Class for tbladditionalfee.
 * This table holds fees which is added only to pariticular students.
 */
class model_additionalfee extends model
{
	var $result_array = array();
	
	function model_additionalfee() {
		model::model( "tbladditionalfee" );
		$this->rowarray = array(
			"additionalfee_id"		=> ROWTYPE_INTEGER,
			"sy_id"					=> ROWTYPE_INTEGER,
			"student_id"			=> ROWTYPE_INTEGER,
			"feeelement_id"			=> ROWTYPE_INTEGER,
			"amount"				=> ROWTYPE_INTEGER,
		);
		$this->idcol = "additionalfee_id";
	}

	function get_list( $sy_id, $student_id ) {
		$this->result_array = array();
		$result = $this->query(
			"SELECT * FROM $this->tblname,tblfeeelement" .
			" WHERE $this->tblname.feeelement_id=tblfeeelement.feeelement_id" .
			" AND sy_id=$sy_id" .
			" AND student_id=$student_id" .
			" ORDER BY title"
		);
		if( ! $result ) return null;
		$n = $this->res->numRows();
		for( $i=0; $i<$n; $i++ ) {
			$this->result_array[] = $this->res->fetchRow(DB_FETCHMODE_ASSOC,$i);
		}
		return $result;
	}

	function get_numrows() {
		return count($this->result_array);
	}
	
	function get_fetch_assoc($row) {
		list($key,$val) = each($this->result_array);
		return $val;
	}
	
	function get( $id ) {
		$res = $this->query(
			"SELECT * FROM $this->tblname,tblfeeelement" .
			" WHERE $this->tblname.feeelement_id=tblfeeelement.feeelement_id" .
			" AND $this->idcol=$id"
		);
		if( ! $res ) return null;
		return $res->fetchRow(DB_FETCHMODE_ASSOC,0);
	}
}



?>
