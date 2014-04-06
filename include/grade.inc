<?php



require_once("model.inc");



define( "GRADE_PASS", 300 );	// under 3.00 is the passing grade


// grade remark value. it must be same as in tblgraderemark
define( "GRADEREMARK_INCOMPLETE", 1 );
define( "GRADEREMARK_DROPPED", 2 );
define( "GRADEREMARK_ONPROGRESS", 3);
define( "GRADEREMARK_NOGRADE", 4 );


class model_graderemark extends model {
	
	function model_graderemark() {
		model::model( "tblgraderemark" );
		$this->rowarray = array(
			"id"		=> ROWTYPE_INTEGER,
			"remark"	=> ROWTYPE_STRING
		);
		$this->idcol = "id";
	}

}



function get_graderemark_array()
{
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT * FROM tblgraderemark";
	$res = $g_dbac->query( $cmd );
	if( ! $res ) return null;
	$n = $res->numRows();
	for( $i=0; $i<$n; $i++ ) {
		$ar = $res->fetchRow(DB_FETCHMODE_DEFAULT,$i);
		$array[$ar[0]] = $ar[1];
	}
	return $array;
}



?>
