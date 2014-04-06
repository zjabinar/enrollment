<?php



require_once("db.inc");
require_once("model.inc");



/**
 * Class for table tblsyinfo.
 * tblsyinfo holds information about the semester (Right now, only last_day_of_enroll).
 * It can have department specific data (if department_id is not null)
 **/
class model_syinfo extends model {
	
	/**
	 * Constructor
	 **/
	function model_syinfo() {
		model::model( "tblsyinfo" );
		$this->rowarray = array(
			"syinfo_id"				=> ROWTYPE_INTEGER,
			"sy_id"					=> ROWTYPE_INTEGER,
			"department_id"			=> ROWTYPE_INTEGER,
			"course_id"				=> ROWTYPE_INTEGER,
			"year_level"			=> ROWTYPE_INTEGER,
			"lastday_of_enrol"		=> ROWTYPE_DATE,
			"lastday_of_changing"	=> ROWTYPE_DATE
		);
		$this->idcol = "syinfo_id";
	}

	/**
	 * Runs query to get all the data of the semester.
	 **/
	function get_list( $sy_id ) {
		return $this->query(
			"SELECT * FROM $this->tblname" .
			" WHERE sy_id=$sy_id" .
			" ORDER BY department_id,course_id,year_level"
		);
	}

	/**
	 * Runs query to get latest date of certain department.
	 **/
	function get_lastday_of_changing( $sy_id,$department_id ) {
		return $this->query(
			"SELECT lastday_of_changing FROM $this->tblname" .
			" WHERE sy_id=$sy_id" .
			" AND (department_id=$department_id OR department_id IS NULL)" .
			" ORDER BY department_id DESC,lastday_of_changing" .
			" LIMIT 1"
		);
	}

	/**
	 * Runs query to get the data of the semester of certain department,course,year_level.
	 **/
	function get( $sy_id,$course_id,$year_level ) {
		$department_id = get_department_id_from_course_id($course_id);
		return $this->query(
			"SELECT * FROM $this->tblname" .
			" WHERE sy_id=$sy_id" .
			" AND (department_id=$department_id OR department_id IS NULL)" .
			" AND (course_id=$course_id OR course_id IS NULL)" .
			($year_level>0 ? " AND (year_level=$year_level OR year_level IS NULL)" : " AND year_level IS NULL") .
			" ORDER BY year_level DESC,course_id DESC,department_id DESC" .
			" LIMIT 1"
		);
	}
}



?>
