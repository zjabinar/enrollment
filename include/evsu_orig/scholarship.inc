<?php



require_once("db.inc");
require_once("model.inc");



define( 'SCHOLARFLAG_TOTALLYFREE',	0x01 );



class model_scholartype extends model {
	
	function model_scholartype() {
		model::model( "tblscholartype" );
		$this->rowarray = array(
			"scholartype_id"			=> ROWTYPE_INTEGER,
			"sy_id"						=> ROWTYPE_INTEGER,
			"title"						=> ROWTYPE_STRING,
			"tuition_deduction_rate"	=> ROWTYPE_INTEGER,
			"tuition_deduction_amount"	=> ROWTYPE_INTEGER,
			"flag"						=> ROWTYPE_INTEGER
		);
		$this->idcol = "scholartype_id";
	}
	
	/**
	 * Overrides model::get_list()
	 * Runs general query for SELECTing all the rows and sort by title.
	 * It returns DB_result or false if fail.
	 */
	function get_list($sy_id) {
		return $this->query( "SELECT * FROM $this->tblname WHERE sy_id=$sy_id ORDER BY title" );
	}
}



class model_scholarship extends model {
	
	function model_scholarship() {
		model::model( "tblscholarship" );
		$this->rowarray = array(
			"scholarship_id"	=> ROWTYPE_INTEGER,
			"sy_id"				=> ROWTYPE_INTEGER,
			"student_id"		=> ROWTYPE_INTEGER,
			"scholartype_id"	=> ROWTYPE_INTEGER
		);
		$this->idcol = "scholarship_id";
	}

	function get( $sy_id,$student_id ) {
		$cmd = "SELECT * FROM $this->tblname"
			. " WHERE sy_id=$sy_id"
			. " AND student_id=$student_id";
		return $this->query( $cmd );
	}

	function get_list( $sy_id,$scholartype_id=0 ) {
		$cmd =
			"SELECT $this->tblname.student_id,first_name,middle_name,last_name,course_id,scholartype_id"
			. " FROM $this->tblname,tblstudentinfo"
			. " WHERE sy_id=$sy_id"
			. ($scholartype_id>0 ? " AND $this->tblname.scholartype_id=$scholartype_id" : "")
			. " AND $this->tblname.student_id=tblstudentinfo.student_id"
			. " ORDER BY $this->tblname.scholartype_id,$this->tblname.student_id";
		return $this->query( $cmd );
	}
}



function get_scholartype_array( $sy_id )
{
	$obj = new model_scholartype;
	$obj->connect();
	$obj->get_list($sy_id);
	for( $i=0; $i<$obj->get_numrows(); $i++ ) {
		$dat = $obj->get_fetch_assoc($i);
		$ar[$dat['scholartype_id']] = $dat['title'];
	}
	return $ar;
}



function lookup_scholartype( $scholartype_id )
{
	if( $scholartype_id<=0 ) return '';
	$obj = new model_scholartype;
	$obj->connect();
	$obj->get_by_id( $scholartype_id );
	$dat = $obj->get_fetch_assoc(0);
	return $dat["title"];
}



/**
 * Gets scholartype of a student.
 * Returns scholartype_id. false if student is not scholar.
 */
function get_scholar( $student_id,$sy_id )
{
	$obj = new model_scholarship;
	$obj->connect();
	$obj->get( $sy_id,$student_id );
	if( $obj->get_numrows()==1 ) {
		$dat = $obj->get_fetch_assoc(0);
		return $dat["scholartype_id"];
	}
	return false;
}



