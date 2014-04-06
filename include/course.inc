<?php


require_once("db.inc");
require_once("model.inc");



class model_department extends model {
	function model_department () {
		model::model( "tbldepartment" );
		$this->rowarray = array(
			"department_id"	=> ROWTYPE_INTEGER,
			"short_name"	=> ROWTYPE_STRING,
			"long_name"		=> ROWTYPE_STRING,
			"dean_id"		=> ROWTYPE_INTEGER,
			"order_no"		=> ROWTYPE_INTEGER
		);
		$this->idcol = "department_id";
	}

	function get_list() {
		$cmd = "SELECT * FROM $this->tblname ORDER BY order_no,short_name";
		return $this->query( $cmd );
	}
}



class model_course extends model {
	function model_course() {
		model::model( "tblcourse" );
		$this->rowarray = array(
			"course_id"		=> ROWTYPE_INTEGER,
			"department_id"	=> ROWTYPE_INTEGER,
			"short_name"	=> ROWTYPE_STRING,
			"long_name"		=> ROWTYPE_STRING,
			"major"			=> ROWTYPE_STRING,
			"minor"			=> ROWTYPE_STRING,
			"school_id"		=> ROWTYPE_INTEGER
		);
		$this->idcol = "course_id";
	}

	function get_list() {
		return $this->query( "SELECT * FROM $this->tblname ORDER BY department_id,short_name" );
	}
}



function get_school_id_from_course_id( $course_id )
{
	if( $course_id<=0 ) return 0;
	global $g_dbac;
	$g_dbac->connect();
	$cmd =  "SELECT school_id FROM tblcourse WHERE course_id=$course_id";
	$res = $g_dbac->query( $cmd );
	if( $res==false ) return 0;
	$ar = $res->fetchRow(DB_FETCHMODE_DEFAULT,0);
	return $ar[0];
}



/*
function get_school_id_from_department_id( $department_id )
{
	if( $department_id<=0 ) return 0;
	global $g_dbac;
	$g_dbac->connect();
	$cmd =  "SELECT school_id FROM tbldepartment" .
			" WHERE department_id=$department_id";
	$res = $g_dbac->query( $cmd );
	if( $res==false ) return 0;
	$ar = $res->fetchRow(DB_FETCHMODE_DEFAULT,0);
	return $ar[0];
}
*/



function get_department_from_course_id( $course_id )
{
	if( $course_id<=0 ) return '';
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT tbldepartment.long_name FROM tbldepartment,tblcourse WHERE tblcourse.course_id=$course_id AND tbldepartment.department_id=tblcourse.department_id";
	$res = $g_dbac->query( $cmd );
	if( $res==false ) {
		return "";
	} else {
		$array = $res->fetchRow();
		return $array[0];
	}
}


function get_department_id_from_course_id( $course_id )
{
	if( $course_id<=0 ) return 0;
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT department_id FROM tblcourse WHERE course_id=$course_id";
	$res = $g_dbac->query( $cmd );
	if( $res==false ) {
		return 0;
	} else {
		$array = $res->fetchRow();
		return $array[0];
	}
}


function get_department_from_department_id( $department_id )
{
	if( $department_id<=0 ) return '';
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT long_name FROM tbldepartment WHERE department_id=$department_id";
	$res = $g_dbac->query( $cmd );
	if( $res==false ) {
		return "";
	} else {
		$array = $res->fetchRow();
		return $array[0];
	}
}



function get_short_department_from_department_id( $department_id )
{
	if( $department_id<=0 ) return '';
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT short_name FROM tbldepartment WHERE department_id=$department_id";
	$res = $g_dbac->query( $cmd );
	if( $res==false ) {
		return "";
	} else {
		$array = $res->fetchRow();
		return $array[0];
	}
}


function get_course_from_course_id( $course_id )
{
	if( $course_id<=0 ) return '';
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT long_name,major,minor FROM tblcourse WHERE course_id=$course_id";
	$res = $g_dbac->query( $cmd );
	if( $res==false ) {
		return "";
	} else {
		$array = $res->fetchRow();
		$result = $array[0];
		if( $array[1]!='' ) {
			$result .= ' - ' . $array[1];
			if( $array[2]!='' ) $result .= ',' . $array[2];
		}
		return $result;
	}
}


function get_short_course_from_course_id( $course_id )
{
	if( $course_id<=0 ) return '';
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT short_name,major,minor FROM tblcourse WHERE course_id=$course_id";
	$res = $g_dbac->query( $cmd );
	if( $res==false ) {
		return "";
	} else {
		$array = $res->fetchRow();
		$result = $array[0];
		if( $array[1]!='' ) {
			$result .= ' - ' . $array[1];
			if( $array[2]!='' ) $result .= ',' . $array[2];
		}
		return $result;
	}
}


function get_short_names_from_course_id( $course_id )
{
	if( $course_id<=0 ) return '';
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT tbldepartment.short_name,tblcourse.short_name,major,minor" .
		" FROM tbldepartment,tblcourse" .
		" WHERE tblcourse.course_id=$course_id AND tbldepartment.department_id=tblcourse.department_id";
	$res = $g_dbac->query( $cmd );
	if( $res==false ) {
		return "";
	} else {
		$array = $res->fetchRow();
		return $array;
	}
}


function get_course_array( $school_id=0,$department_id=0,$with_detail=false )
{
	global $g_dbac;
	$g_dbac->connect();
	$cmd =
		"SELECT tblcourse.course_id,tbldepartment.short_name AS department_name,tblcourse.short_name,tblcourse.long_name,tblcourse.major,tblcourse.minor,tblcourse.department_id" .
		" FROM tblcourse,tbldepartment" .
		" WHERE tblcourse.department_id=tbldepartment.department_id";
	if( $department_id!=0 ) {
		$cmd .= " AND tblcourse.department_id=$department_id";
	}
	if( $school_id!=0 ) {
		$cmd .= " AND tblcourse.school_id=$school_id";
	}
	$cmd .= " ORDER BY tbldepartment.short_name,tblcourse.short_name,tblcourse.major,tblcourse.minor";
	$res = $g_dbac->query( $cmd );
	if( ! $res ) return array();
	$n = $res->numRows();
	if( $n==0 ) return array();
	for( $i=0; $i<$n; $i++ ) {
		$ar = $res->fetchRow(DB_FETCHMODE_ASSOC,$i);
		if( $with_detail ) {
			$array[$ar["course_id"]] = $ar;
		} else {
			$idx = ($department_id==0 ? $ar["department_name"] . " " : "") . $ar["short_name"] . " " . $ar["major"] . " " . $ar["minor"];
			$array[$ar["course_id"]] = $idx;
		}
	}
	return $array;
}


//=============================
// returns all the courses including virtual course ( a course represents for all major )
//   * course_id of the virtual course will be a minus value of one of the course
//=============================
function get_course_array_virtual( $school_id=0,$department_id=0 )
{
	$array = get_course_array($school_id,$department_id);

	global $g_dbac;
	$g_dbac->connect();
	$cmd =
		"SELECT tblcourse.course_id,tbldepartment.short_name,tblcourse.short_name" .
		" FROM tblcourse,tbldepartment" .
		" WHERE tblcourse.department_id=tbldepartment.department_id";
	if( $department_id!=0 ) {
		$cmd .= " AND tblcourse.department_id=$department_id";
	}
	if( $school_id!=0 ) {
		$cmd .= " AND tblcourse.school_id=$school_id";
	}
	$cmd .=
		" GROUP BY tbldepartment.short_name,tblcourse.short_name" .
		" HAVING count(*)>1";
	$res = $g_dbac->query( $cmd );
	if( ! $res ) return $array;
	$n = $res->numRows();
	if( $n==0 ) return $array;
	for( $i=0; $i<$n; $i++ ) {
		$ar = $res->fetchRow(DB_FETCHMODE_DEFAULT,$i);
		$idx = ($department_id==0 ? $ar[1] . " " : "") . $ar[2] . "\t----------";
		$array[-$ar[0]] = $idx;
	}
	asort($array);
	return $array;
}


function get_department_array( $school_id=0 )
{
	global $g_dbac;
	$g_dbac->connect();
	if( $school_id==0 ) {
		$cmd = "SELECT department_id,long_name FROM tbldepartment";
	} else {
		$cmd = "SELECT tbldepartment.department_id,tbldepartment.long_name"
			. " FROM tbldepartment,tblcourse"
			. " WHERE tbldepartment.department_id=tblcourse.department_id"
			. " AND tblcourse.school_id=$school_id"
			. " GROUP BY tbldepartment.department_id";
	}
	$res = $g_dbac->query( $cmd );
	if( ! $res ) return null;
	$n = $res->numRows();
	for( $i=0; $i<$n; $i++ ) {
		$ar = $res->fetchRow(DB_FETCHMODE_DEFAULT,$i);
		$array[$ar[0]] = $ar[1];
	}
	asort($array);
	return $array;
}



function get_short_department_array( $school_id=0 )
{
	global $g_dbac;
	$g_dbac->connect();
	if( $school_id==0 ) {
		$cmd = "SELECT department_id,short_name FROM tbldepartment";
	} else {
		$cmd = "SELECT tbldepartment.department_id,tbldepartment.short_name"
			. " FROM tbldepartment,tblcourse"
			. " WHERE tbldepartment.department_id=tblcourse.department_id"
			. " AND tblcourse.school_id=$school_id"
			. " GROUP BY tbldepartment.department_id";
	}
	$res = $g_dbac->query( $cmd );
	if( ! $res ) return null;
	$n = $res->numRows();
	for( $i=0; $i<$n; $i++ ) {
		$ar = $res->fetchRow(DB_FETCHMODE_DEFAULT,$i);
		$array[$ar[0]] = $ar[1];
	}
	asort($array);
	return $array;
}



function get_dean_id_from_department_id( $department_id )
{
	if( $department_id<=0 ) return 0;
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT dean_id FROM tbldepartment WHERE department_id=$department_id";
	$res = $g_dbac->query( $cmd );
	if( ! $res ) return "";
	$dat = $res->fetchRow(DB_FETCHMODE_DEFAULT,0);
	return $dat[0];
}



?>
