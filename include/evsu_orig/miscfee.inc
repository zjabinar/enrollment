<?php



require_once("db.inc");
require_once("model.inc");



define( 'MISCFLAG_NEWSTUDENT',		0x0100 );	// only for student whose entered semester is same as present semester
define( 'MISCFLAG_DOUBLEFORAYEAR',	0x0200 );	// double the amount if the student is enrolled for a whole year
define( 'MISCFLAG_NOTFOROUTSIDE',	0x0400 );	// no amount if the student is enrolled for outside campus
define( 'MISCFLAG_ONCEINAYEAR',		0x0800 );	// only first semester that students have enrolled. (always 1st sem if regular student, but 2nd if student is quitter.)



class model_miscfee extends model
{
	var $result_array = array();
	
	function model_miscfee() {
		model::model( "tblmiscfee" );
		$this->rowarray = array(
			"miscfee_id"		=> ROWTYPE_INTEGER,
			"enter_year"		=> ROWTYPE_INTEGER,
			"school_id"			=> ROWTYPE_INTEGER,
			"department_id"		=> ROWTYPE_INTEGER,
			"course_id"			=> ROWTYPE_INTEGER,
			"year_level"		=> ROWTYPE_INTEGER,
			"effective_year"	=> ROWTYPE_INTEGER,
			"feeelement_id"		=> ROWTYPE_INTEGER,
			"amount"			=> ROWTYPE_INTEGER,
			"semester_flag"		=> ROWTYPE_INTEGER,
		);
		$this->idcol = "miscfee_id";
	}

	function get_list( $enter_year, $school_id, $year ) {
		$this->result_array = array();
		$result = $this->query(
			"SELECT * FROM $this->tblname,tblfeeelement" .
			" WHERE $this->tblname.feeelement_id=tblfeeelement.feeelement_id" .
			" AND enter_year=$enter_year" .
			" AND school_id=$school_id" .
			" AND effective_year<=$year" .
			" ORDER BY title,effective_year,department_id,course_id,year_level"
		);
		if( ! $result ) return $result;
		$n = $this->res->numRows();
		for( $i=0; $i<$n; $i++ ) {
			$dat = $this->res->fetchRow(DB_FETCHMODE_ASSOC,$i);
			//$id = $dat["feecategory_id"]*100*100*100 + $dat["feeelement_id"]*100*100 + $dat["department_id"]*100 + $dat["course_id"];
			$id =  sprintf( "%02d:%s:%02d:%03d:%02d", $dat["feecategory_id"], $dat["title"], $dat["department_id"], $dat["course_id"], $dat["year_level"] );
			if( $dat["amount"]=='' ) {
				unset( $this->result_array[$id] );
			} else {
				$this->result_array[$id] = $dat;
			}
		}
		ksort( $this->result_array );
		return $result;
	}
	
	function get_list2( $enter_year, $course_id, $year_level, $year ) {
		$department_id = get_department_id_from_course_id( $course_id );
		$school_id = get_school_id_from_course_id( $course_id );
		$this->get_list( $enter_year,$school_id,$year );
		$newarray = array();
		$n = count($this->result_array);
		foreach( $this->result_array as $id => $val ) {
			if( $val["year_level"]>0 && $val["year_level"]!=$year_level ) continue;
			if( $val["course_id"]>0 && $val["course_id"]!=$course_id ) continue;
			if( $val["department_id"]>0 && $val["department_id"]!=$department_id ) continue;
			$idx = $val["feeelement_id"];
			$newarray[$idx] = $val;
		}
		$this->result_array = $newarray;
		return $result;
	}
	
	function get_numrows() {
		return count($this->result_array);
	}
	
	function get_fetch_assoc($row) {
		list($key,$val) = each($this->result_array);
		return $val;
	}
	
	function get( $id ) {
		$res = $this->query(
			"SELECT * FROM $this->tblname,tblfeeelement" .
			" WHERE $this->idcol=$id" .
			" AND $this->tblname.feeelement_id=tblfeeelement.feeelement_id"
		);
		if( ! $res ) return null;
		return $res->fetchRow(DB_FETCHMODE_ASSOC,0);
	}

	function set( $rowdata ) {
		$cmd = "SELECT miscfee_id FROM $this->tblname" .
			" WHERE enter_year='" . $rowdata["enter_year"] . "'" .
			" AND school_id='" . $rowdata["school_id"] . "'" .
			" AND department_id='" . $rowdata["department_id"] . "'" .
			" AND course_id='" . $rowdata["course_id"] . "'" .
			" AND year_level='" . $rowdata["year_level"] . "'" .
			" AND effective_year='" . $rowdata["effective_year"] . "'" .
			" AND feeelement_id='" . $rowdata["feeelement_id"] . "'";
		$res = $this->query( $cmd );
		if( $res && $res->numRows()>0 ) {
			$dat = $res->fetchRow(DB_FETCHMODE_ASSOC,0);
			$rowdata["miscfee_id"] = $dat["miscfee_id"];
			return model::update( $rowdata );
		} else {
			unset( $rowdata["miscfee_id"] );
			return model::add( $rowdata );
		}
	}
}



?>
