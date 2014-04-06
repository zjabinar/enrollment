<?php



require_once("db.inc");
require_once("model.inc");
require_once("util.inc");



/* Length of teacher ID */
define( "LEN_TEACHERID",6 );



/**
 * Class for table tblteacher.
 * tblteacher holds personal information about teachers.
 */
class model_teacher extends model {
	
	function model_teacher() {
		model::model( "tblteacher" );
		$this->rowarray = array(
			"teacher_id"		=> ROWTYPE_INTEGER,
			"first_name"		=> ROWTYPE_STRING,
			"middle_name"		=> ROWTYPE_STRING,
			"last_name"			=> ROWTYPE_STRING,
			"title"				=> ROWTYPE_STRING,
			"rank"				=> ROWTYPE_STRING,
			"position"			=> ROWTYPE_STRING,
			"department_id"		=> ROWTYPE_INTEGER,
			"address"			=> ROWTYPE_STRING,
			"noclass"			=> ROWTYPE_INTEGER,
			"date_of_birth"		=> ROWTYPE_DATE,
			"doctor_degree"		=> ROWTYPE_STRING,
			"master_degree"		=> ROWTYPE_STRING,
			"bachelor_degree"	=> ROWTYPE_STRING
		);
		$this->idcol = "teacher_id";
	}

	/**
	 * Runs query to list all.
	 * Sorts by teacher ID
	 */
	function get_list() {
		return $this->query( "SELECT * FROM $this->tblname ORDER BY $this->idcol" );
	}
	
	/**
	 * Runs query to search by teacher_id.
	 * It will search those id start with given numeral.
	 */
	function search_by_id( $teacher_id ) {
		if( strlen($teacher_id) < 1 ) return false;
		$from = str_pad( $teacher_id,LEN_TEACHERID,"0",STR_PAD_RIGHT );
		$to = str_pad( $teacher_id,LEN_TEACHERID,"9",STR_PAD_RIGHT );
		return $this->query(
			"SELECT * FROM $this->tblname" .
			" WHERE (teacher_id>=$from AND teacher_id<=$to) OR (teacher_id=$teacher_id)" .
			" ORDER BY teacher_id" );
		//return $this->query( "SELECT * FROM $this->tblname WHERE teacher_id like '%" . $teacher_id . "%' ORDER BY teacher_id" );
	}

	/**
	 * Runs query to search by teacher_id
	 * It will search those last name includes given string.
	 */
	function search_by_lastname( $last_name ) {
		if( strlen($last_name) < 1 ) return false;
		return $this->query( "SELECT * FROM $this->tblname WHERE last_name like '%" . $last_name . "%' ORDER BY last_name" );
	}

}



/**
 *  Gets teacher's full name from teacher_id.
 */
function lookup_teacher_name($teacher_id)
{
	if( $teacher_id==0 ) return "";
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT first_name,middle_name,last_name,title FROM tblteacher WHERE teacher_id=$teacher_id";
	$res = $g_dbac->query( $cmd );
	if( ! $res ) return "";
	$ar = $res->fetchRow();
	return $ar[3] . ' ' . mkstr_name_fml($ar[0],$ar[1],$ar[2]);
}


/**
 *  Gets teacher's title, first, middle, last name from teacher_id.
 */
function lookup_teacher_name_array($teacher_id)
{
	if( $teacher_id==0 ) return "";
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT first_name,middle_name,last_name,title FROM tblteacher WHERE teacher_id=$teacher_id";
	$res = $g_dbac->query( $cmd );
	if( ! $res ) return "";
	$ar = $res->fetchRow();
	return array($ar[3],$ar[0],$ar[1],$ar[2]);
}


/**
 * Gets teacher's position from teacher_id.
 */
function lookup_teacher_position($teacher_id)
{
	if( $teacher_id==0 ) return "";
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT position FROM tblteacher WHERE teacher_id=$teacher_id";
	$res = $g_dbac->query( $cmd );
	if( ! $res ) return "";
	$ar = $res->fetchRow();
	return $ar[0];
}


/**
 *  Gets teacher's department_id from teacher_id.
 */
function lookup_teacher_department_id($teacher_id)
{
	if( $teacher_id==0 ) return "";
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT department_id FROM tblteacher WHERE teacher_id=$teacher_id";
	$res = $g_dbac->query( $cmd );
	if( ! $res ) return "";
	$ar = $res->fetchRow();
	return $ar[0];
}


/**
 * get array of teachers
 *   If $department_id is set, only teacher who is assinged to the department
 *   or teacher not assigned to any colleges will appear.
 *   If $sy_id is also set, only teacher who have class in the department in
 *   the specified school year will appear.
 *   If $exclude_noteacher is true, teacher who have no teaching load will not appear.
 */
function get_teacher_array( $department_id=0, $sy_id=0, $exclude_noteacher=false )
{
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT tblteacher.teacher_id,first_name,middle_name,last_name";
	if( $department_id>0 && $sy_id>0 ) {
		$cmd .= " FROM tblteacher,tblclass" .
			" WHERE tblteacher.teacher_id=tblclass.teacher_id" .
			" AND tblclass.sy_id=$sy_id" .
			" AND tblclass.department_id=$department_id" .
			($exclude_noteacher ? " AND (tblteacher.noclass=0 OR tblteacher.noclass IS NULL)" : "") .
			" GROUP BY teacher_id";
	} else if( $sy_id>0 ) {
		$cmd .= " FROM tblteacher,tblclass" .
			" WHERE tblteacher.teacher_id=tblclass.teacher_id" .
			" AND tblclass.sy_id=$sy_id" .
			($exclude_noteacher ? " AND (tblteacher.noclass=0 OR tblteacher.noclass IS NULL)" : "") .
			" GROUP BY teacher_id";
	} else if( $department_id>0 ) {
		$cmd .= " FROM tblteacher" .
			" WHERE department_id=$department_id OR department_id IS NULL" .
			($exclude_noteacher ? " AND (tblteacher.noclass=0 OR tblteacher.noclass IS NULL)" : "");
	} else {
		$cmd .= " FROM tblteacher" .
			($exclude_noteacher ? " WHERE (tblteacher.noclass=0 OR tblteacher.noclass IS NULL)" : "");
	}
	$cmd .= " ORDER BY last_name,middle_name,first_name";
	$array = array();
	$res = $g_dbac->query( $cmd );
	if( ! $res ) return $array;
	$n = $res->numRows();
	for( $i=0; $i<$n; $i++ ) {
		$ar = $res->fetchRow(DB_FETCHMODE_DEFAULT,$i);
		$array[$ar[0]] = mkstr_name_lfm($ar[1],$ar[2],$ar[3]);
	}
	return $array;
}



//==============================
// make teacher string id from id (i.e. 10001 -> 010001)
//==============================
function mkstr_teacher_id( $id )
{
	return sprintf( '%0' . LEN_TEACHERID . 'd', $id );
}



//==============================
// make teacher id from idtype and idno id (i.e. 01 + 0001 -> 010001)
//==============================
function retrieve_teacher_id( $id )
{
	return $id;
}



/**
 * Values for position_id in tblteacherpos
 */
define( "TEACHERPOS_PRESIDENT",		1 );
define( "TEACHERPOS_VPACADEMIC",	2 );
define( "TEACHERPOS_VPADMIN",		3 );
define( "TEACHERPOS_REGISTRAR",		4 );
$teacherpos_array = array (
	TEACHERPOS_PRESIDENT	=> "President",
	TEACHERPOS_VPACADEMIC	=> "VP for Academic affairs",
	TEACHERPOS_VPADMIN		=> "VP for Administration",
	TEACHERPOS_REGISTRAR	=> "Registrar"
);



/**
 * Class for table tblteacherpos.
 * tblteacherpos holds teacher_id of specified position
 * position_id is value of TEACHERPOS_xxx
 */
class model_tblteacherpos extends model {

	function model_tblteacherpos() {
		model::model( "tblteacherpos" );
		$this->rowarray = array(
			"position_id"		=> ROWTYPE_INTEGER,
			"teacher_id"		=> ROWTYPE_INTEGER
		);
		$this->idcol = "position_id";
	}
}



/**
 * lookup from tblteacherpos
 * returns teacher_id. zero if not assigned.
 */
function lookup_teacherpos( $position_id )
{
	$obj = new model_tblteacherpos;
	$obj->connect();
	$res = $obj->get_by_id( $position_id );
	if( $res ) {
		$dat = $obj->get_fetch_assoc(0);
		return $dat['teacher_id'];
	}
	return 0;
}



?>
