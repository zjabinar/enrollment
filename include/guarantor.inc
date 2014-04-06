<?php



require_once("db.inc");
require_once("model.inc");



class model_guarantor extends model {
	
	function model_guarantor() {
		model::model( "tblguarantor" );
		$this->rowarray = array(
			"guarantor_id"	=> ROWTYPE_INTEGER,
			"sy_id"			=> ROWTYPE_INTEGER,
			"student_id"	=> ROWTYPE_INTEGER,
			"teacher_id"	=> ROWTYPE_INTEGER,
			"due_date"		=> ROWTYPE_DATE,
			"remark"		=> ROWTYPE_STRING
		);
		$this->idcol = "guarantor_id";
	}

	function get_by_id( $sy_id,$student_id ) {
		$cmd = "SELECT * FROM $this->tblname" .
			" WHERE sy_id=$sy_id AND student_id=$student_id";
		return $this->query( $cmd );
	}

	function get_list( $sy_id ) {
		$cmd = "SELECT * FROM $this->tblname,tblstudentinfo,tblstudentsenrolled"
			. " WHERE ($sy_id BETWEEN tblstudentsenrolled.sy_id AND tblstudentsenrolled.sy_id_end)"
			. " AND $this->tblname.sy_id=tblstudentsenrolled.sy_id"
			. " AND $this->tblname.student_id=tblstudentsenrolled.student_id"
			. " AND $this->tblname.student_id=tblstudentinfo.student_id"
			. " ORDER BY $this->tblname.student_id";
		return $this->query( $cmd );
	}
}



/**
 * Gets guarantor of a student.
 * Returns teacher_id of guarantor. false if the student do not have guarantor.
 */
function get_guarantor( $sy_id,$student_id ) {
	$obj = new model_guarantor;
	$obj->connect();
	$obj->get_by_id($sy_id,$student_id);
	if( $obj->get_numRows()>0 ) {
		$dat = $obj->get_fetch_assoc(0);
		return $dat["teacher_id"];
	}
	return false;
}

