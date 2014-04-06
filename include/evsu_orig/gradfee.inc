<?php



require_once("db.inc");
require_once("model.inc");



class model_gradfee extends model
{
	var $result_array = array();
	
	function model_gradfee() {
		model::model( "tblgraduationfee" );
		$this->rowarray = array(
			"gradfee_id"			=> ROWTYPE_INTEGER,
			"year"					=> ROWTYPE_INTEGER,
			"school_id"				=> ROWTYPE_INTEGER,
			"department_id"			=> ROWTYPE_INTEGER,
			"course_id"				=> ROWTYPE_INTEGER,
			"feeelement_id"			=> ROWTYPE_INTEGER,
			"amount"				=> ROWTYPE_INTEGER,
		);
		$this->idcol = "gradfee_id";
	}

	function get_list( $year, $school_id ) {
		$this->result_array = array();
		$result = $this->query(
			"SELECT * FROM $this->tblname,tblfeeelement" .
			" WHERE $this->tblname.feeelement_id=tblfeeelement.feeelement_id" .
			" AND year=$year" .
			" AND school_id=$school_id" .
			" ORDER BY title,department_id,course_id"
		);
		if( ! $result ) return null;
		$n = $this->res->numRows();
		for( $i=0; $i<$n; $i++ ) {
			$this->result_array[] = $this->res->fetchRow(DB_FETCHMODE_ASSOC,$i);
		}
		return $result;
	}

	function get_list2( $year, $course_id ) {
		$department_id = get_department_id_from_course_id( $course_id );
		$school_id = get_school_id_from_course_id( $course_id );
		$result = $this->query(
			"SELECT * FROM $this->tblname,tblfeeelement" .
			" WHERE $this->tblname.feeelement_id=tblfeeelement.feeelement_id" .
			" AND year=$year" .
			" AND school_id=$school_id" .
			" AND (department_id=$department_id OR department_id=0)" .
			" AND (course_id=$course_id OR course_id=0)" .
			" ORDER BY title,department_id,course_id"
		);
		if( ! $result ) return null;
		$n = $this->res->numRows();
		for( $i=0; $i<$n; $i++ ) {
			$dat = $this->res->fetchRow(DB_FETCHMODE_ASSOC,$i);
			$id = $dat["feeelement_id"];
			$this->result_array[$id] = $dat;
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
