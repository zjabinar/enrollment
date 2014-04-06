<?php



require_once("db.inc");
require_once("model.inc");
require_once("util.inc");
require_once("yearlevel.inc");
require_once("section.inc");



define( "LEN_STUDENTID", 6 );



class model_student extends model {
	
	function model_student() {
		model::model( "tblstudentinfo" );
		$this->rowarray = array(
			"student_id"	=> ROWTYPE_INTEGER,
			"first_name"	=> ROWTYPE_STRING,
			"middle_name"	=> ROWTYPE_STRING,
			"last_name"		=> ROWTYPE_STRING,
			"civil_status"	=> ROWTYPE_INTEGER,
			"p_first_name"	=> ROWTYPE_STRING,
			"p_middle_name"	=> ROWTYPE_STRING,
			"p_last_name"	=> ROWTYPE_STRING,
			"p_relation"	=> ROWTYPE_STRING,
			"date_of_birth"	=> ROWTYPE_DATE,
			"place_of_birth"=> ROWTYPE_STRING,
			"course_id"		=> ROWTYPE_INTEGER,
			"enter_sy"		=> ROWTYPE_INTEGER,
			"graduate_sy"	=> ROWTYPE_INTEGER,
			"feebase_sy"	=> ROWTYPE_INTEGER,
			"present_address"	=> ROWTYPE_STRING,
			"home_address"		=> ROWTYPE_STRING,
			"parent_address"	=> ROWTYPE_STRING,
			"gender"		=> ROWTYPE_STRING,
			"elem_school"		=> ROWTYPE_STRING,
			"elem_grad_year"	=> ROWTYPE_INTEGER,
			"second_school" => ROWTYPE_STRING,
			"second_grad_year"	=> ROWTYPE_INTEGER,
			"course_completed"	=> ROWTYPE_STRING,
			"last_school"		=> ROWTYPE_STRING,
			"last_school_year"	=> ROWTYPE_INTEGER
		);
		$this->idcol = "student_id";
	}

	function search_by_id( $student_id,$sy_id=0,$department_id=0 ) {
		if( strlen($student_id) < 1 ) return false;
		$from = str_pad( $student_id,LEN_STUDENTID,"0",STR_PAD_RIGHT );
		$to = str_pad( $student_id,LEN_STUDENTID,"9",STR_PAD_RIGHT );
		return $this->query(
			"SELECT * FROM $this->tblname" .
			($department_id>0 ? ",tblcourse" : "") .
			" WHERE ( (student_id>=$from AND student_id<=$to) OR (student_id=$student_id) )" .
			($department_id>0 ? " AND $this->tblname.course_id=tblcourse.course_id AND tblcourse.department_id=$department_id" : "") .
			($sy_id>0 ? " AND enter_sy<=$sy_id AND (graduate_sy=0 OR graduate_sy>=$sy_id)" : "") .
			" ORDER BY student_id" );
	//	return $this->query( "SELECT * FROM $this->tblname WHERE student_id like '" . $student_id . "%' ORDER BY student_id" );
	}

	function search_by_lastname( $last_name,$sy_id=0,$department_id=0 ) {
		if( strlen($last_name) < 1 ) return false;
		return $this->query(
			"SELECT * FROM $this->tblname" .
			($department_id>0 ? ",tblcourse" : "") .
			" WHERE (last_name like '" . $last_name . "%')" .
			($department_id>0 ? " AND $this->tblname.course_id=tblcourse.course_id AND tblcourse.department_id=$department_id" : "") .
			($sy_id>0 ? " AND enter_sy<=$sy_id AND (graduate_sy=0 OR graduate_sy>=$sy_id)" : "") .
			" ORDER BY last_name" );
	}

	function add_auto( $rowdata ) {
		$base_id = intval( ($rowdata["enter_sy"]/10) % 100 ) * 10000;
		if( $this->begin_transaction()==false ) return $this->rollback();
		$cmd = "SELECT max(student_id) FROM $this->tblname" .
			" WHERE student_id>=$base_id AND student_id<" . ($base_id+10000) .
			" FOR UPDATE";
		if( $this->query( $cmd )==false ) return $this->rollback();
		list($id) = $this->get_fetch_array(0);
		if( $id<$base_id ) {	// This means that it's first student of the year!
			$id = $base_id;
		}
		$rowdata["student_id"] = $id +  1;
		if( $this->add( $rowdata )==false ) return $this->rollback();
		if( $this->end_transaction()==false ) return $this->rollback();
		return $rowdata["student_id"];
	}
}



function get_department_id_from_student_id( $student_id )
{
	global $g_dbac;
	$g_dbac->connect();
	$result = $g_dbac->query(
		"SELECT department_id FROM tblstudentinfo,tblcourse" .
		" WHERE tblstudentinfo.student_id=$student_id" .
		" AND tblstudentinfo.course_id=tblcourse.course_id"
	);
	if( $result!=false ) {
		$ar = $result->fetchRow();
		return $ar[0];
	}
	return 0;
}



function mkstr_student_id( $student_id )
{
	return sprintf( "%0" . LEN_STUDENTID . "d", $student_id );
}



function print_studentinfo_simple( $student_id,$sy_id=0,$no_department=false,$disp_feebase=false )
{
	global $g_dbac;
	$g_dbac->connect();
	if( $sy_id>0 ) {
		$cmd =
			"SELECT first_name,middle_name,last_name,tbldepartment.short_name AS department_name,tblcourse.short_name AS course_name,major,minor,year_level,section,tblstudentsenrolled.feebase_sy,date_dropped,refund_rate,sy_id,sy_id_end" .
			" FROM tblstudentinfo,tblstudentsenrolled,tbldepartment,tblcourse" .
			" WHERE tblstudentsenrolled.student_id=$student_id" .
			" AND ($sy_id BETWEEN tblstudentsenrolled.sy_id AND tblstudentsenrolled.sy_id_end)" .
			" AND tblstudentsenrolled.student_id=tblstudentinfo.student_id" .
			" AND tblstudentsenrolled.course_id=tblcourse.course_id" .
			" AND tblcourse.department_id=tbldepartment.department_id";
		$res = $g_dbac->query($cmd);
		if( $res!=false ) {
			$ar = $res->fetchRow(DB_FETCHMODE_ASSOC);
		}
	}
	if( count($ar)<=0 ) {
		$cmd =
			"SELECT first_name,middle_name,last_name,tbldepartment.short_name AS department_name,tblcourse.short_name AS course_name,major,minor" .
			" FROM tblstudentinfo,tbldepartment,tblcourse" .
			" WHERE tblstudentinfo.student_id=$student_id" .
			" AND tblstudentinfo.course_id=tblcourse.course_id" .
			" AND tblcourse.department_id=tbldepartment.department_id";
		$res = $g_dbac->query($cmd);
		if( ($res!=false) && ($res->numRows()>0) ) {
			$ar = $res->fetchRow(DB_FETCHMODE_ASSOC);
		}
	}
	echo mkstr_student_id($student_id) . " &nbsp; ";
	echo mkstr_name_fml($ar["first_name"],$ar["middle_name"],$ar["last_name"]) . "<br>";
	if( ! $no_department ) echo $ar["department_name"] . " &nbsp; ";
	echo $ar["course_name"];
	if( $ar["major"]!='' ) {
		echo ' - ' . $ar["major"];
		if( $ar["minor"]!='' ) echo ',' . $ar["minor"];
	}
	if( isset($ar["year_level"]) ) {
		echo ' ' . lookup_yearlevel($ar["year_level"]) . ' ' . lookup_section($ar["section"]);
	}
	echo '<br>';
	if( $disp_feebase && isset($ar["feebase_sy"]) ) {
		echo 'Fees base on ' . get_year_from_schoolyear($ar["feebase_sy"]) . '<br>';
	}
	if( $ar["date_dropped"] ) {
		echo 'Officially dropped on ' . mkstr_date($ar["date_dropped"]) . ' : Refund rate ' . $ar["refund_rate"] . '%<br>';
	}
	if( $ar["sy_id_end"] && ($ar["sy_id"] != $ar["sy_id_end"]) ) {
		echo lookup_schoolyear($ar["sy_id"]) . ' - ' . lookup_schoolyear($ar["sy_id_end"]) . '<br>';
	}
	return $ar;
}



function get_civilstatus_array()
{
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT * FROM tblcivilstatus";
	$res = $g_dbac->query( $cmd );
	if( ! $res ) return null;
	$n = $res->numRows();
	for( $i=0; $i<$n; $i++ ) {
		$ar = $res->fetchRow(DB_FETCHMODE_DEFAULT,$i);
		$array[$ar[0]] = $ar[1];
	}
	return $array;
}



function lookup_civilstatus( $civilstatus_id )
{
	if( $civilstatus_id<=0 ) return null;
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT title FROM tblcivilstatus WHERE civilstatus_id=$civilstatus_id";
	$res = $g_dbac->query( $cmd );
	if( ! $res ) return null;
	if( $res->numRows()==0 ) return null;
	$ar = $res->fetchRow(DB_FETCHMODE_DEFAULT);
	return $ar[0];
}

?>
