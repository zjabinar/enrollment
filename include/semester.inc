<?php



require_once("model.inc");



class model_schoolyear extends model {
	
	function model_schoolyear() {
		model::model( "tblschoolyear" );
		$this->rowarray = array(
			"sy_id"		=> ROWTYPE_INTEGER,
			"year"		=> ROWTYPE_INTEGER,
			"semester"	=> ROWTYPE_INTEGER
		);
		$this->idcol = "sy_id";
	}

	function get_list() {
		return $this->query( "SELECT * FROM $this->tblname ORDER BY $this->idcol" );
	}

	function add( $rowdata ) {
		$rowdata["sy_id"] = $rowdata["year"] * 10 + $rowdata["semester"];
		return model::add( $rowdata );
	}
}



$lookup_semester = array(
	"1" => "1st",
	"2" => "2nd",
	"3" => "summer"
);



function get_semester_array()
{
	global $lookup_semester;
	return $lookup_semester;
}



function mkstr_schoolyear( $year,$semester )
{
	global $lookup_semester;
	return "SY " . $year . "-" . ($year+1) . " " . $lookup_semester[$semester];
}



function get_schoolyear_array()
{
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT sy_id,year,semester FROM tblschoolyear ORDER BY sy_id DESC";
	$res = $g_dbac->query( $cmd );
	if( ! $res ) return null;
	$n = $res->numRows();
	for( $i=0; $i<$n; $i++ ) {
		$ar = $res->fetchRow(DB_FETCHMODE_DEFAULT,$i);
		$array[$ar[0]] = mkstr_schoolyear($ar[1],$ar[2]);
	}
	#asort($array);
	return $array;
}



function lookup_schoolyear( $sy_id )
{
//	global $g_dbac;
//	$g_dbac->connect();
//	$cmd = "SELECT year,semester FROM tblschoolyear WHERE sy_id=$sy_id";
//	$res = $g_dbac->query( $cmd );
//	if( ! $res ) return null;
//	if( $res->numRows()==0 ) return null;
//	$ar = $res->fetchRow(DB_FETCHMODE_DEFAULT);
//	return mkstr_schoolyear( $ar[0],$ar[1] );
	return mkstr_schoolyear( intval($sy_id/10),$sy_id%10 );
}


function evsu_sy( $sy_id )
{
//	global $g_dbac;
//	$g_dbac->connect();
//	$cmd = "SELECT year,semester FROM tblschoolyear WHERE sy_id=$sy_id";
//	$res = $g_dbac->query( $cmd );
//	if( ! $res ) return null;
//	if( $res->numRows()==0 ) return null;
//	$ar = $res->fetchRow(DB_FETCHMODE_DEFAULT);
//	return mkstr_schoolyear( $ar[0],$ar[1] );
	return mkstr_schoolyear_evsu( intval($sy_id/10),$sy_id%10 );
}
function evsu_sem( $sy_id )
{
	return mkstr_semester_evsu( intval($sy_id/10),$sy_id%10 );
}

function mkstr_schoolyear_evsu( $year,$semester )
{
	global $lookup_semester;
	return $year . "-" . ($year+1) ;
}

function mkstr_semester_evsu( $year,$semester )
{
	global $lookup_semester;
	return $lookup_semester[$semester];
}

function get_enteredyear_array()
{
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT year FROM tblschoolyear GROUP BY year";
	$res = $g_dbac->query( $cmd );
	if( ! $res ) return null;
	$n = $res->numRows();
	for( $i=0; $i<$n; $i++ ) {
		$ar = $res->fetchRow(DB_FETCHMODE_DEFAULT,$i);
		$array[$ar[0]] = $ar[0];
	}
	asort($array);
	return $array;
}



function get_year_from_schoolyear($sy_id)
{
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT year FROM tblschoolyear WHERE sy_id=$sy_id";
	$res = $g_dbac->query( $cmd );
	if( ! $res ) return null;
	$ar = $res->fetchRow(DB_FETCHMODE_ASSOC,0);
	return $ar["year"];
}



function get_semester_from_schoolyear($sy_id)
{
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT semester FROM tblschoolyear WHERE sy_id=$sy_id";
	$res = $g_dbac->query( $cmd );
	if( ! $res ) return null;
	$ar = $res->fetchRow(DB_FETCHMODE_ASSOC,0);
	return $ar["semester"];
}



/**
 * Gets the schoolyear which is the end of this year.
 *  ex. if given schoolyear is 20043 or 20051 or 20052, than 20052.
 */
function get_schoolyear_end($sy_id)
{
	$year = intval($sy_id / 10);
	$semester = $sy_id % 10;
	if( $semester>2 ) $year++;
	return $year * 10 + 2;
}



?>
