<?php



require_once("db.inc");
require_once("model.inc");



// These values are for tblfeeratetitle.feeratetype
define( 'FEERATETYPE_TUITION',		1 );
define( 'FEERATETYPE_ADDING',		2 );
define( 'FEERATETYPE_CHANGING',		3 );
define( 'FEERATETYPE_DROPPING',		4 );
define( 'FEERATETYPE_LATE1ST',		5 );
define( 'FEERATETYPE_LATE2ND',		6 );
define( 'FEERATETYPE_LATE3RD',		7 );
define( 'FEERATETYPE_PERUNIT',		8 );
define( 'FEERATETYPE_PERSUBJECT',	9 );
define( 'FEERATETYPE_PERSEMESTER',	10 );



class model_feeratetitle extends model
{
	function model_feeratetitle() {
		model::model( "tblfeeratetitle" );
		$this->rowarray = array(
			"feeratetitle_id"	=> ROWTYPE_INTEGER,
			"feeratetype"		=> ROWTYPE_INTEGER,
			"feeelement_id"		=> ROWTYPE_INTEGER,
			"department_id"		=> ROWTYPE_INTEGER,
			"defaultval"		=> ROWTYPE_INTEGER,
			"short_name"		=> ROWTYPE_STRING,
		);
		$this->idcol = "feeratetitle_id";
	}

	function get_list() {
		$cmd = "SELECT * FROM $this->tblname,tblfeeelement"
			. " WHERE $this->tblname.feeelement_id=tblfeeelement.feeelement_id";
		return $this->query( $cmd );
	}

	function check_if_used( $feeratetitle_id ) {
		$cmd = "SELECT count(*) FROM tblclass WHERE (flag & (0x01<<($feeratetitle_id-1)))!=0";
		$res = $this->query( $cmd );
		if( ! $res ) return -1;
		$dat = $res->fetchRow(DB_FETCHMODE_ARRAY,0);
		return $dat[0];
	}

	function del( $feeratetitle_id ) {
		$result = $this->check_if_used( $feeratetitle_id );
		if( $result!=0 ) {
			$this->set_error( "The flag is already set in the class" );
			return false;
		} else {
			$this->begin_transaction();
			$cmd = "DELETE FROM tblfeerate WHERE feeratetitle_id=$feeratetitle_id";
			$ret = $this->query( $cmd );
			if( $ret ) {
				$ret = model::del( $feeratetitle_id );
				if( $ret ) {
					return $this->end_transaction();
				}
			}
			$this->rollback();
			return $ret;
		}
	}
}



class model_feerate extends model
{
	var $result_array = array();
	
	function model_feerate() {
		model::model( "tblfeerate" );
		$this->rowarray = array(
			"feerate_id"		=> ROWTYPE_INTEGER,
			"enter_year"		=> ROWTYPE_INTEGER,
			"school_id"			=> ROWTYPE_INTEGER,
			"department_id"		=> ROWTYPE_INTEGER,
			"course_id"			=> ROWTYPE_INTEGER,
			"year_level"		=> ROWTYPE_INTEGER,
			"effective_syid"	=> ROWTYPE_INTEGER,
			"feeratetitle_id"	=> ROWTYPE_INTEGER,
			"amount"			=> ROWTYPE_INTEGER
		);
		$this->idcol = "feerate_id";
	}

	function get_list( $year,$school_id,$sy_id ) {
		$this->result_array = array();
		$cmd = "SELECT feeratetitle_id,feeelement_id FROM tblfeeratetitle";
		$result = $this->query( $cmd );
		if( ! $result ) return null;
		$n = $this->res->numRows();
		for( $i=0; $i<$n; $i++ ) {
			$dat = $this->res->fetchRow(DB_FETCHMODE_ASSOC,$i);
			$feeratetitle_array[$dat["feeratetitle_id"]] = $dat;
			$idx = $dat["feeratetitle_id"]*100*1000*10;
			$dat["id"] = $idx;
			$this->result_array[$idx] = $dat;
		}
		$cmd = "SELECT * FROM $this->tblname" .
			   " WHERE enter_year=$year" .
			   " AND school_id=$school_id" .
			   " AND effective_syid<=$sy_id" .
			   " ORDER BY feeratetitle_id,effective_syid,department_id,course_id,year_level";
		$result = $this->query( $cmd );
		if( ! $result ) return null;
		$n = $this->res->numRows();
		for( $i=0; $i<$n; $i++ ) {
			$dat = $this->res->fetchRow(DB_FETCHMODE_ASSOC,$i);
			$dat["feeelement_id"] = $feeratetitle_array[$dat["feeratetitle_id"]]["feeelement_id"];
			$idx = $dat["feeratetitle_id"]*100*1000*10 + $dat["department_id"]*1000*10 + $dat["course_id"]*10 + $dat["year_level"];
			if( $dat["amount"]=='' ) {
				unset( $this->result_array[$idx] );
			} else {
				$dat["id"] = $idx;
				$this->result_array[$idx] = $dat;
			}
		}
		ksort( $this->result_array );
		return $result;
	}

	function get_numrows() {
		return count($this->result_array);
	}

	function get_fetch_assoc($row) {
		list($key,$val) = each($this->result_array);
		return $val;
	}

	function get( $enter_year,$course_id,$year_level,$sy_id ) {
		$ar = array();
		$department_id = get_department_id_from_course_id( $course_id );
		$school_id = get_school_id_from_course_id( $course_id );
		if( $this->get_list( $enter_year,$school_id,$sy_id )==false ) return false;
		$n = $this->get_numrows();
		for( $i=0; $i<$n; $i++ ) {
			$dat = $this->get_fetch_assoc($i);
			if( ($dat["year_level"]!=0) && ($dat["year_level"]!=$year_level) ) continue;
			if( ($dat["course_id"]!=0) && ($dat["course_id"]!=$course_id) ) continue;
			if( ($dat["department_id"]!=0) && ($dat["department_id"]!=$department_id) ) continue;
			$id = $dat["feeratetitle_id"];
			$ar[$id] = array( $dat["amount"], $dat["feeelement_id"] );
		}
		return $ar;
	}

	function set( $enter_year,$school_id,$sy_id,$dat ) {
		$feeratetitle_id = intval(($dat["id"]) / (100*1000*10));
		$department_id = intval(($dat["id"] / (1000*10)) % 100);
		$course_id = intval(($dat["id"] / 10) % 1000);
		$year_level = intval(($dat["id"]) % 10);
		$cmd = "SELECT * FROM $this->tblname"
			. " WHERE enter_year=$enter_year"
			. " AND effective_syid=$sy_id"
			. " AND feeratetitle_id=$feeratetitle_id"
			. " AND school_id=$school_id"
			. " AND department_id=$department_id"
			. " AND course_id=$course_id"
			. " AND year_level=$year_level";
		$result = $this->query( $cmd );
		if( ! $result ) return null;
		$n = $this->res->numRows();
		if( $n==0 ) {
			$newdat = array(
				"enter_year"		=> $enter_year,
				"school_id"			=> $school_id,
				"department_id"		=> sprintf("%d",$department_id),
				"course_id"			=> sprintf("%d",$course_id),
				"year_level"		=> sprintf("%d",$year_level),
				"effective_syid"	=> $sy_id,
				"feeratetitle_id"	=> $feeratetitle_id,
				"amount"			=> sprintf("%d",$dat["amount"])
			);
			return model::add( $newdat );
		} else {
			$newdat = model::get_fetch_assoc(0);
			$newdat["amount"] = $dat["amount"];
			return model::update( $newdat );
		}
	}
}



function get_feeratetitle_array( $with_detail=false )
{
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT * FROM tblfeeratetitle,tblfeeelement"
		. " WHERE tblfeeratetitle.feeelement_id=tblfeeelement.feeelement_id"
		. " ORDER BY feeratetitle_id";
	$res = $g_dbac->query( $cmd );
	if( ! $res ) return null;
	$n = $res->numRows();
	for( $i=0; $i<$n; $i++ ) {
		$ar = $res->fetchRow(DB_FETCHMODE_ASSOC,$i);
		$title = $ar["title"];
		if( ! empty($ar["short_name"]) ) $title .= " (" . $ar["short_name"] . ")";
		switch( $ar["feeratetype"] ) {
			case FEERATETYPE_TUITION:		$title .= ' (per unit)';						break;
			case FEERATETYPE_ADDING:		$title = 'Adding of subject (per subject)';		break;
			case FEERATETYPE_CHANGING:		$title = 'Changing of subject (per subject)';	break;
			case FEERATETYPE_DROPPING:		$title = 'Dropping of subject (per subject)';	break;
			case FEERATETYPE_LATE1ST:		$title .= ' (1st day)';							break;
			case FEERATETYPE_LATE2ND:		$title .= ' (2nd day)';							break;
			case FEERATETYPE_LATE3RD:		$title .= ' (3rd day)';							break;
			case FEERATETYPE_PERUNIT:		$title .= ' (per unit)';						break;
			case FEERATETYPE_PERSUBJECT:	$title .= ' (per subject)';						break;
			case FEERATETYPE_PERSEMESTER:	$title .= ' (per semester)';					break;
		}
		$ar["title"] = $title;
		if( $with_detail ) {
			$array[$ar["feeratetitle_id"]] = $ar;
		} else {
			$array[$ar["feeratetitle_id"]] = $ar["title"];
		}
	}
	return $array;
}



function get_classflag_array( $department_id=0,$with_details=false )
{
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT tblfeeratetitle.*,tblfeeelement.title"
		. " FROM tblfeeratetitle,tblfeeelement"
		. " WHERE tblfeeratetitle.feeelement_id=tblfeeelement.feeelement_id"
		. ($department_id>0 ? " AND (department_id=$department_id OR department_id IS NULL)" : "")
		. " AND (feeratetype=" . FEERATETYPE_PERUNIT . " OR feeratetype=" . FEERATETYPE_PERSUBJECT . " OR feeratetype=" . FEERATETYPE_PERSEMESTER . ")"
		. " ORDER BY feeratetitle_id";
	$res = $g_dbac->query( $cmd );
	if( ! $res ) return null;
	$n = $res->numRows();
	for( $i=0; $i<$n; $i++ ) {
		$ar = $res->fetchRow(DB_FETCHMODE_ASSOC,$i);
		if( $with_details ) {
			$array[$ar["feeratetitle_id"]] = $ar;
		} else {
			$array[$ar["feeratetitle_id"]] = $ar["short_name"];
		}
	}
	return $array;
}



function get_classflag_string( $classflag )
{
	static $classflag_array=null;
	if( $classflag_array==null ) $classflag_array = get_classflag_array();
	$str = '';
	foreach( $classflag_array as $idx=>$val ) {
		if( $classflag & (0x01<<($idx-1)) ) $str .= $val . ' ';
	}
	return $str;
}																			



?>
