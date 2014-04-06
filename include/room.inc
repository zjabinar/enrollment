<?php



require_once("db.inc");
require_once("model.inc");



class model_building extends model {

	function model_building() {
		model::model( "tblbuilding" );
		$this->rowarray = array(
			"building_id"		=> ROWTYPE_INTEGER,
			"description"		=> ROWTYPE_STRING
		);
		$this->idcol = "building_id";
	}

	function get_list() {
		$cmd = "SELECT * FROM $this->tblname ORDER BY description";
		return $this->query( $cmd );
	}
}



class model_room extends model {
	
	function model_room() {
		model::model( "tblroom" );
		$this->rowarray = array(
			"room_id"			=> ROWTYPE_INTEGER,
			"room_code"			=> ROWTYPE_STRING,
			"building_id"		=> ROWTYPE_INTEGER,
			"description"		=> ROWTYPE_STRING
		);
		$this->idcol = "room_id";
	}

	function get_list( $building_id=0 ) {
		$cmd =
			"SELECT * FROM $this->tblname" .
			($building_id!=0 ? " WHERE building_id=$building_id" : "") .
			" ORDER BY room_code";
		return $this->query( $cmd );
	}
}



function get_room_array( $building_id=0 )
{
	$obj = new model_room;
	$obj->connect();
	$obj->get_list( $building_id );
	$ar = array();
	for( $i=0; $i<$obj->get_numrows(); $i++ ) {
		$dat = $obj->get_fetch_array($i);
		$ar[$dat[0]] = $dat[1];
	}
	return $ar;
}



function get_building_id_from_room_id( $room_id )
{
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT building_id FROM tblroom WHERE room_id=$room_id";
	$res = $g_dbac->query( $cmd );
	if( ! $res ) return null;
	$ar = $res->fetchRow(DB_FETCHMODE_DEFAULT);
	return $ar[0];

}



function get_building_array()
{
	$obj = new model_building;
	$obj->connect();
	$obj->get_list();
	$ar = array();
	for( $i=0; $i<$obj->get_numrows(); $i++ ) {
		$dat = $obj->get_fetch_array($i);
		$ar[$dat[0]] = $dat[1];
	}
	return $ar;
}



?>
