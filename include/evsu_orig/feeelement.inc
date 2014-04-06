<?php



require_once("db.inc");
require_once("model.inc");



// These value must be same as in tblfeecategory
define( 'FEECATEGORY_REGISTRATION',		1 );
define( 'FEECATEGORY_TUITION',			2 );
define( 'FEECATEGORY_MISCELLANEOUS',	3 );
define( 'FEECATEGORY_OTHER',			4 );
define( 'FEECATEGORY_GRADUATION',		5 );



// Flag for fee_flag in tblfeeelement
define( 'FEEFLAG_FEERATE',		0x01 );
define( 'FEEFLAG_MISCFEE',		0x02 );
define( 'FEEFLAG_GRADFEE',		0x04 );
define( 'FEEFLAG_OPTIONALFEE',	0x08 );
define( 'FEEFLAG_ADDITIONALFEE',0x10 );

define( 'FEEFLAGGRP_COMPULSORY',	FEEFLAG_FEERATE|FEEFLAG_MISCFEE|FEEFLAG_GRADFEE|FEEFLAG_ADDITIONALFEE );
define( 'FEEFLAGGRP_OPTIONAL',		FEEFLAG_OPTIONALFEE );



class model_feecategory extends model
{
	function model_feecategory() {
		model::model( "tblfeecategory" );
		$this->rowarray = array(
			"feecategory_id"	=> ROWTYPE_INTEGER,
			"title"				=> ROWTYPE_STRING,
			"fee_flag"			=> ROWTYPE_INTEGER
		);
		$this->idcol = "feecategory_id";
	}
}



class model_feeelement extends model
{
	function model_feeelement() {
		model::model( "tblfeeelement" );
		$this->rowarray = array(
			"feeelement_id"		=> ROWTYPE_INTEGER,
			"title"				=> ROWTYPE_STRING,
			"feecategory_id"	=> ROWTYPE_INTEGER,
			"fee_flag"			=> ROWTYPE_INTEGER
		);
		$this->idcol = "feeelement_id";
	}
}



function get_feeelement_array( $list=null, $fee_flag=0, $with_details=false )
{
	global $g_dbac;
	$g_dbac->connect();
	$array = array();
	$cmd = "SELECT feeelement_id,title,feecategory_id,fee_flag FROM tblfeeelement";
	$n = 0;
	if( isset($list) ) {
		$cmd .= ' WHERE (';
		foreach( $list as $v ) {
			$cmd .= ($n==0 ? "" : " OR ") . "feecategory_id=$v";
			$n++;
		}
		$cmd .= ')';
	}
	if( $fee_flag!=0 ) {
		$cmd .= ($n==0 ? " WHERE " : " AND ") . "(fee_flag & $fee_flag)!=0";
	}
	$cmd .= " ORDER BY title";
	$res = $g_dbac->query( $cmd );
	if( ! $res ) return $array;
	$n = $res->numRows();
	for( $i=0; $i<$n; $i++ ) {
		$ar = $res->fetchRow(DB_FETCHMODE_ASSOC,$i);
		if( $with_details ) {
			$array[$ar["feeelement_id"]] = $ar;
		} else {
			$array[$ar["feeelement_id"]] = $ar["title"];
		}
	}
	return $array;
}



function get_feecategory_array( $list=null, $fee_flag=0 )
{
	global $g_dbac;
	$g_dbac->connect();
	$array = array();
	$cmd = "SELECT feecategory_id,title FROM tblfeecategory";
	$n = 0;
	if( isset($list) ) {
		$cmd .= ' WHERE (';
		foreach( $list as $v ) {
			$cmd .= ($n==0 ? "" : " OR ") . "feecategory_id=$v";
			$n++;
		}
		$cmd .= ')';
	}
	if( $fee_flag!=0 ) {
		$cmd .= ($n==0 ? " WHERE " : " AND ") . "(fee_flag & $fee_flag)!=0";
	}
	$cmd .= " ORDER BY feecategory_id";
	$res = $g_dbac->query( $cmd );
	if( ! $res ) return $array;
	$n = $res->numRows();
	for( $i=0; $i<$n; $i++ ) {
		$ar = $res->fetchRow(DB_FETCHMODE_DEFAULT,$i);
		$array[$ar[0]] = $ar[1];
	}
	return $array;
}



function lookup_feeelement_title( $feeelement_id ) {
	$obj = new model_feeelement;
	$obj->connect();
	if( $obj->get_by_id($feeelement_id) ) {
		$dat = $obj->get_fetch_assoc(0);
		return $dat['title'];
	}
	return '';
}
?>
