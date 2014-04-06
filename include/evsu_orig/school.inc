<?php



// value of school_id. This must be same values as in tblschoollevel.
define( 'SCHOOLID_HIGHSCHOOL', 1);
define( 'SCHOOLID_UNDERGRADUATE', 2);
define( 'SCHOOLID_MASTERS', 3);
define( 'SCHOOLID_POSTGRADUATE', 4);



function get_schoolid_array()
{
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT school_id,name FROM tblschoollevel";
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



function lookup_schoolid( $school_id )
{
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT name FROM tblschoollevel WHERE school_id=$school_id";
	$res = $g_dbac->query( $cmd );
	if( ! $res ) return null;
	$ar = $res->fetchRow(DB_FETCHMODE_DEFAULT);
	return $ar[0];
}



?>
