<?php



require_once("db.inc");
require_once("model.inc");



class model_auth extends model {
	
	function model_auth() {
		model::model( "tblauth" );
		$this->rowarray = array(
			"user_id"		=> ROWTYPE_INTEGER,
			"username"		=> ROWTYPE_STRING,
			"passwd"		=> ROWTYPE_STRING,
			"authflag"		=> ROWTYPE_INTEGER,
			"authflag_w"	=> ROWTYPE_INTEGER,
			"active"		=> ROWTYPE_INTEGER,
			"fullname"		=> ROWTYPE_STRING
		);
		$this->idcol = "user_id";
	}

	function get_list() {
		return $this->query( "SELECT * FROM $this->tblname ORDER BY username" );
	}
}



function auth_get_fullname()
{
	$obj_user = new model_auth;
	$obj_user->connect();
	$obj_user->get_by_id( auth_get_userid() );
	$dat = $obj_user->get_fetch_assoc(0);
	return $dat["fullname"];
}



?>
