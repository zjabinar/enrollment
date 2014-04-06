<?php



require_once("db.inc");
require_once("model.inc");
require_once("enrol_class.inc");



/**
 * flag for campus_flag in tblstudentsenrolled
 *   low bits are reserved for campus_id (like main,mercedes,...) that might be added in the future..
 **/
define("CAMPUSFLAG_OUTSIDE",0x8000);



/**
 *  Class for table tblstudentsnerolled
 *  tblstudentsenrolled holds students' Enrollment information to the semester.
 */
class model_enrol_student extends model {
	
	function model_enrol_student() {
		model::model( "tblstudentsenrolled" );
		$this->rowarray = array(
			"enroll_id"			=> ROWTYPE_INTEGER,
			"sy_id"				=> ROWTYPE_INTEGER,
			"sy_id_end"			=> ROWTYPE_INTEGER,
			"student_id"		=> ROWTYPE_INTEGER,
			"date"				=> ROWTYPE_DATE,
			"date_officially"	=> ROWTYPE_DATE,
			"feebase_sy"		=> ROWTYPE_INTEGER,
			"course_id"			=> ROWTYPE_INTEGER,
			"year_level"		=> ROWTYPE_INTEGER,
			"section"			=> ROWTYPE_INTEGER,
			"campus_flag"		=> ROWTYPE_INTEGER,
			"date_dropped"		=> ROWTYPE_DATE,
			"refund_rate"		=> ROWTYPE_INTEGER
		);
		$this->idcol = "enroll_id";
	}

	/**
	 *  Runs query to get data from sy_id and student_id.
	 **/
	function get_by_id( $sy_id,$student_id ) {
		return $this->query(
			"SELECT * FROM $this->tblname" .
			" WHERE ($sy_id BETWEEN sy_id AND sy_id_end)" .
			" AND student_id=$student_id"
		);
	}

	/**
	 * Runs query to get data including first_name,middle_name,last_name
	 **/
	function get_info( $sy_id,$student_id ) {
		return $this->query(
			"SELECT $this->tblname.student_id,first_name,middle_name,last_name,$this->tblname.feebase_sy,$this->tblname.course_id,year_level,section,campus_flag,date_dropped,refund_rate,sy_id,sy_id_end" .
			" FROM $this->tblname,tblstudentinfo" .
			" WHERE ($sy_id BETWEEN sy_id AND sy_id_end)" .
			" AND $this->tblname.student_id=$student_id" .
			" AND $this->tblname.student_id=tblstudentinfo.student_id" );
	}

    /**
     *  Runs query to get list of schoolyear the student have enrolled.
     */
	function get_sy_list( $student_id ) {
		$cmd = "SELECT * FROM $this->tblname"
			 . " WHERE student_id=$student_id"
			 . " ORDER BY sy_id";
		return $this->query( $cmd );
	}

	/**
	 * Gets last sy_id that student has enrolled from the specified sy_id.
	 */
	function get_last_sy_id( $sy_id,$student_id ) {
		$cmd = "SELECT sy_id FROM $this->tblname"
			. " WHERE student_id=$student_id"
			. " AND sy_id<$sy_id"
			. " AND (date_officially IS NOT NULL)"
			. " AND (date_dropped IS NULL)"
			. " LIMIT 1";
		if( ! $this->query( $cmd ) ) return null;
		if( $this->get_numrows()<=0 ) return null;
		$dat = $this->get_fetch_array(0);
		return $dat[0];
	}

	function search_by_id( $student_id,$sy_id ) {
		if( strlen($student_id) < 1 ) return false;
		$from = str_pad( $student_id,LEN_STUDENTID,"0",STR_PAD_RIGHT );
		$to = str_pad( $student_id,LEN_STUDENTID,"9",STR_PAD_RIGHT );
		return $this->query(
			"SELECT $this->tblname.student_id,first_name,middle_name,last_name,$this->tblname.course_id" .
			" FROM $this->tblname,tblstudentinfo" .
			" WHERE ($sy_id BETWEEN sy_id AND sy_id_end)" .
			" AND $this->tblname.student_id=tblstudentinfo.student_id" .
			" AND ( ($this->tblname.student_id>=$from AND $this->tblname.student_id<=$to) OR ($this->tblname.student_id=$student_id) )" .
			" ORDER BY student_id" );
		//return $this->query( "SELECT $this->tblname.student_id,first_name,middle_name,last_name,course_id FROM $this->tblname,tblstudentinfo WHERE $this->tblname.sy_id=$sy_id AND $this->tblname.student_id=tblstudentinfo.student_id AND $this->tblname.student_id like '%" . $student_id . "%' ORDER BY student_id" );
	}

	function search_by_lastname( $last_name,$sy_id ) {
		if( strlen($last_name) < 1 ) return false;
		return $this->query(
			"SELECT $this->tblname.student_id,first_name,middle_name,last_name,$this->tblname.course_id" .
			" FROM $this->tblname,tblstudentinfo" .
			" WHERE ($sy_id BETWEEN sy_id AND sy_id_end)" .
			" AND $this->tblname.student_id=tblstudentinfo.student_id" .
			" AND last_name like '" . $last_name . "%'" .
			" ORDER BY last_name" );
	}

	function search_enrolled_by_id( $sy_id,$student_id,$department_id=0,$only_officially=false ) {
		if( strlen($student_id) < 1 ) return false;
		$from = str_pad( $student_id,LEN_STUDENTID,"0",STR_PAD_RIGHT );
		$to = str_pad( $student_id,LEN_STUDENTID,"9",STR_PAD_RIGHT );
		$cmd =
			"SELECT enroll_id,$this->tblname.student_id,first_name,middle_name,last_name,$this->tblname.course_id,date,date_officially,year_level,section,date_dropped,refund_rate" .
			" FROM $this->tblname,tblstudentinfo,tblcourse" .
			" WHERE ($sy_id BETWEEN sy_id AND sy_id_end)" .
			" AND $this->tblname.student_id=tblstudentinfo.student_id" .
			" AND ( ($this->tblname.student_id>=$from AND $this->tblname.student_id<=$to) OR ($this->tblname.student_id=$student_id) )" .
			" AND tblcourse.course_id=$this->tblname.course_id" .
			($department_id>0 ? " AND tblcourse.department_id=$department_id" : "") .
			($only_officially ? " AND ! (date_officially IS NULL)" : "") .
			" ORDER BY $this->tblname.student_id";
		return $this->query( $cmd );
	}

	function search_enrolled_by_lastname( $sy_id,$last_name,$department_id=0,$only_officially=false ) {
		if( strlen($last_name) < 1 ) return false;
		$cmd =
			"SELECT enroll_id,$this->tblname.student_id,first_name,middle_name,last_name,$this->tblname.course_id,date,date_officially,year_level,section,date_dropped,refund_rate" .
			" FROM $this->tblname,tblstudentinfo,tblcourse" .
			" WHERE ($sy_id BETWEEN sy_id AND sy_id_end)" .
			" AND $this->tblname.student_id=tblstudentinfo.student_id" .
			" AND last_name like '" . $last_name ."%'" .
			" AND tblcourse.course_id=$this->tblname.course_id" .
			($department_id>0 ? " AND tblcourse.department_id=$department_id" : "") .
			($only_officially ? " AND ! (date_officially IS NULL)" : "") .
			" ORDER BY last_name";
		return $this->query( $cmd );
	}

	function search_not_enrolled_by_id( $sy_id,$student_id,$department_id ) {
		if( strlen($student_id) < 1 ) return false;
		$from = str_pad( $student_id,LEN_STUDENTID,"0",STR_PAD_RIGHT );
		$to = str_pad( $student_id,LEN_STUDENTID,"9",STR_PAD_RIGHT );
		
		//// This query (using EXCEPT) is only allowed in PostgreSQL
		//$cmd =
		//	"SELECT student_id,first_name,middle_name,last_name,tblstudentinfo.course_id" .
		//	" FROM tblstudentinfo,tblcourse" .
		//	" WHERE ( (student_id>=$from AND student_id<=$to) OR (student_id=$student_id) )" .
		//	" AND enter_sy<=$sy_id" .
		//	" AND (graduate_sy=0 OR graduate_sy>=$sy_id)" .
		//	" AND tblcourse.course_id=tblstudentinfo.course_id AND tblcourse.department_id=$department_id" .
		//	" EXCEPT" .
		//	" SELECT $this->tblname.student_id,first_name,middle_name,last_name,tblstudentinfo.course_id" .
		//	" FROM $this->tblname,tblstudentinfo,tblcourse" .
		//	" WHERE $this->tblname.sy_id=$sy_id" .
		//	" AND $this->tblname.student_id=tblstudentinfo.student_id" .
		//	" AND ( ($this->tblname.student_id>=$from AND $this->tblname.student_id<=$to) OR ($this->tblname.student_id=$student_id) )" .
		//	" AND tblcourse.course_id=tblstudentinfo.course_id AND tblcourse.department_id=$department_id" .
		//	" ORDER BY student_id";
		
		//// This can be used both PostgreSQL and MySQL
		$cmd =
			"SELECT tblstudentinfo.student_id,first_name,middle_name,last_name,tblstudentinfo.course_id" .
			" FROM tblcourse,tblstudentinfo" .
			" LEFT OUTER JOIN $this->tblname" .
			" ON tblstudentinfo.student_id=$this->tblname.student_id AND ($sy_id BETWEEN $this->tblname.sy_id AND $this->tblname.sy_id_end)" .
			" WHERE ( (tblstudentinfo.student_id>=$from AND tblstudentinfo.student_id<=$to) OR (tblstudentinfo.student_id=$student_id) )" .
			" AND enter_sy<=$sy_id" .
			" AND (graduate_sy=0 OR graduate_sy>=$sy_id)" .
			" AND tblcourse.course_id=tblstudentinfo.course_id AND tblcourse.department_id=$department_id" .
			" AND enroll_id IS NULL" .
			" ORDER BY student_id";
		return $this->query( $cmd );
	}

	function search_not_enrolled_by_lastname( $sy_id,$last_name,$department_id ) {
		if( strlen($last_name) < 1 ) return false;

		//// This query (using EXCEPT) is only allowed in PostgreSQL
		//$cmd =
		//	"SELECT student_id,first_name,middle_name,last_name,tblstudentinfo.course_id" .
		//	" FROM tblstudentinfo,tblcourse" .
		//	" WHERE last_name like '" . $last_name ."%'" .
		//	" AND enter_sy<=$sy_id" .
		//	" AND (graduate_sy=0 OR graduate_sy>=$sy_id)" .
		//	" AND tblcourse.course_id=tblstudentinfo.course_id AND tblcourse.department_id=$department_id" .
		//	" EXCEPT" .
		//	" SELECT $this->tblname.student_id,first_name,middle_name,last_name,tblstudentinfo.course_id" .
		//	" FROM $this->tblname,tblstudentinfo,tblcourse" .
		//	" WHERE $this->tblname.sy_id=$sy_id" .
		//	" AND $this->tblname.student_id=tblstudentinfo.student_id" .
		//	" AND last_name like '" . $last_name ."%'" .
		//	" AND tblcourse.course_id=tblstudentinfo.course_id AND tblcourse.department_id=$department_id" .
		//	" ORDER BY student_id";

		//// This can be used both PostgreSQL and MySQL
		$cmd =
			"SELECT tblstudentinfo.student_id,first_name,middle_name,last_name,tblstudentinfo.course_id" .
			" FROM tblcourse,tblstudentinfo" .
			" LEFT OUTER JOIN $this->tblname" .
			" ON tblstudentinfo.student_id=$this->tblname.student_id AND ($sy_id BETWEEN $this->tblname.sy_id AND $this->tblname.sy_id_end)" .
			" WHERE last_name like '" . $last_name ."%'" .
			" AND enter_sy<=$sy_id" .
			" AND (graduate_sy=0 OR graduate_sy>=$sy_id)" .
			" AND tblcourse.course_id=tblstudentinfo.course_id AND tblcourse.department_id=$department_id" .
			" AND enroll_id IS NULL" .
			" ORDER BY last_name";
		return $this->query( $cmd );
	}

	function search_graduates( $sy_id,$department_id,$course_id,$year_level,$section ) {
		$cmd =
			"SELECT $this->tblname.student_id,first_name,middle_name,last_name,$this->tblname.course_id,$this->tblname.year_level,$this->tblname.section,graduate_sy" .
			" FROM $this->tblname,tblstudentinfo,tblcourse" .
			" WHERE ($sy_id BETWEEN $this->tblname.sy_id AND $this->tblname.sy_id_end)" .
			" AND $this->tblname.student_id=tblstudentinfo.student_id" .
			" AND tblstudentinfo.graduate_sy!=0" .
			" AND tblcourse.course_id=$this->tblname.course_id" .
			($department_id>0 ? " AND tblcourse.department_id=$department_id" : "") .
			($course_id>0 ? " AND $this->tblname.course_id=$course_id" : "") .
			($year_level>0 ? " AND $this->tblname.year_level=$year_level" : "") .
			($section>0 ? " AND $this->tblname.section=$section" : "") .
			" ORDER BY student_id";
		return $this->query($cmd);
	}
	
	function search_graduates_by_id( $sy_id,$student_id,$department_id ) {
		if( strlen($student_id) < 1 ) return false;
		$from = str_pad( $student_id,LEN_STUDENTID,"0",STR_PAD_RIGHT );
		$to = str_pad( $student_id,LEN_STUDENTID,"9",STR_PAD_RIGHT );
		$cmd =
			"SELECT $this->tblname.student_id,first_name,middle_name,last_name,$this->tblname.course_id,graduate_sy" .
			" FROM $this->tblname,tblstudentinfo,tblcourse" .
			" WHERE ($sy_id BETWEEN $this->tblname.sy_id AND $this->tblname.sy_id_end)" .
			" AND $this->tblname.student_id=tblstudentinfo.student_id" .
			" AND ( ($this->tblname.student_id>=$from AND $this->tblname.student_id<=$to) OR ($this->tblname.student_id=$student_id) )" .
			" AND tblstudentinfo.graduate_sy!=0" .
			" AND tblcourse.course_id=$this->tblname.course_id AND tblcourse.department_id=$department_id" .
			" ORDER BY student_id";
		return $this->query( $cmd );
	}

	function search_graduates_by_lastname( $sy_id,$last_name,$department_id ) {
		if( strlen($last_name) < 1 ) return false;
		$cmd =
			"SELECT $this->tblname.student_id,first_name,middle_name,last_name,$this->tblname.course_id,graduate_sy" .
			" FROM $this->tblname,tblstudentinfo,tblcourse" .
			" WHERE ($sy_id BETWEEN $this->tblname.sy_id AND $this->tblname.sy_id_end)" .
			" AND $this->tblname.student_id=tblstudentinfo.student_id" .
			" AND last_name like '" . $last_name ."%'" .
			" AND tblstudentinfo.graduate_sy!=0" .
			" AND tblcourse.course_id=$this->tblname.course_id AND tblcourse.department_id=$department_id" .
			" ORDER BY student_id";
		return $this->query( $cmd );
	}
	
	function search_not_graduates( $sy_id,$department_id,$course_id,$year_level,$section ) {
		$cmd =
			"SELECT $this->tblname.student_id,first_name,middle_name,last_name,$this->tblname.course_id,$this->tblname.year_level,$this->tblname.section,graduate_sy" .
			" FROM $this->tblname,tblstudentinfo,tblcourse" .
			" WHERE ($sy_id BETWEEN $this->tblname.sy_id AND $this->tblname.sy_id_end)" .
			" AND $this->tblname.student_id=tblstudentinfo.student_id" .
			" AND tblstudentinfo.graduate_sy=0" .
			" AND tblcourse.course_id=$this->tblname.course_id" .
			($department_id>0 ? " AND tblcourse.department_id=$department_id" : "") .
			($course_id>0 ? " AND $this->tblname.course_id=$course_id" : "") .
			($year_level>0 ? " AND $this->tblname.year_level=$year_level" : "") .
			($section>0 ? " AND $this->tblname.section=$section" : "") .
			" ORDER BY student_id";
		return $this->query($cmd);
	}

	function search_not_graduates_by_id( $sy_id,$student_id,$department_id ) {
		if( strlen($student_id) < 1 ) return false;
		$from = str_pad( $student_id,LEN_STUDENTID,"0",STR_PAD_RIGHT );
		$to = str_pad( $student_id,LEN_STUDENTID,"9",STR_PAD_RIGHT );
		$cmd =
			"SELECT $this->tblname.student_id,first_name,middle_name,last_name,$this->tblname.course_id" .
			" FROM $this->tblname,tblstudentinfo,tblcourse" .
			" WHERE ($sy_id BETWEEN $this->tblname.sy_id AND $this->tblname.sy_id_end)" .
			" AND $this->tblname.student_id=tblstudentinfo.student_id" .
			" AND ( ($this->tblname.student_id>=$from AND $this->tblname.student_id<=$to) OR ($this->tblname.student_id=$student_id) )" .
			" AND tblstudentinfo.graduate_sy=0" .
			" AND tblcourse.course_id=$this->tblname.course_id AND tblcourse.department_id=$department_id" .
			" ORDER BY student_id";
		return $this->query( $cmd );
	}

	function search_not_graduates_by_lastname( $sy_id,$last_name,$department_id ) {
		if( strlen($last_name) < 1 ) return false;
		$cmd =
			"SELECT $this->tblname.student_id,first_name,middle_name,last_name,$this->tblname.course_id" .
			" FROM $this->tblname,tblstudentinfo,tblcourse" .
			" WHERE ($sy_id BETWEEN $this->tblname.sy_id AND $this->tblname.sy_id_end)" .
			" AND $this->tblname.student_id=tblstudentinfo.student_id" .
			" AND last_name like '" . $last_name ."%'" .
			" AND tblstudentinfo.graduate_sy=0" .
			" AND tblcourse.course_id=$this->tblname.course_id AND tblcourse.department_id=$department_id" .
			" ORDER BY student_id";
		return $this->query( $cmd );
	}

	function get_list_enrolled( $sy_id,$department_id=0,$course_id=0,$year_level=0,$section=0 ) {
		$cmd =
			"SELECT $this->tblname.student_id,first_name,middle_name,last_name,$this->tblname.course_id,year_level,section,date_officially" .
			" FROM $this->tblname,tblstudentinfo,tblcourse" .
			" WHERE ($sy_id BETWEEN $this->tblname.sy_id AND $this->tblname.sy_id_end)" .
			" AND $this->tblname.student_id=tblstudentinfo.student_id" .
			" AND $this->tblname.course_id=tblcourse.course_id" .
			($department_id>0 ? " AND tblcourse.department_id=$department_id" : "") .
			($course_id>0 ? " AND tblcourse.course_id=$course_id" : "") .
			($year_level>0 ? " AND $this->tblname.year_level=$year_level" : "") .
			($section>0 ? " AND $this->tblname.section=$section" : "") .
			" ORDER BY $this->tblname.student_id";
		return $this->query( $cmd );
	}

	function search_officially_enrolled_by_id( $sy_id,$student_id,$department_id=0 ) {
		if( strlen($student_id) < 1 ) return false;
		$from = str_pad( $student_id,LEN_STUDENTID,"0",STR_PAD_RIGHT );
		$to = str_pad( $student_id,LEN_STUDENTID,"9",STR_PAD_RIGHT );
		$cmd =
			"SELECT $this->tblname.student_id,first_name,middle_name,last_name,$this->tblname.course_id,year_level,section,date_officially" .
			" FROM $this->tblname,tblstudentinfo,tblcourse" .
			" WHERE ($sy_id BETWEEN $this->tblname.sy_id AND $this->tblname.sy_id_end)" .
			" AND ( ($this->tblname.student_id>=$from AND $this->tblname.student_id<=$to) OR ($this->tblname.student_id=$student_id) )" .
			" AND !(date_officially IS NULL)" .
			" AND $this->tblname.student_id=tblstudentinfo.student_id" .
			" AND $this->tblname.course_id=tblcourse.course_id" .
			($department_id>0 ? " AND tblcourse.department_id=$department_id" : "") .
			" ORDER BY $this->tblname.student_id";
		return $this->query( $cmd );
	}
	
	function search_officially_enrolled_by_lastname( $sy_id,$last_name,$department_id=0 ) {
		if( strlen($last_name) < 1 ) return false;
		$cmd =
			"SELECT $this->tblname.student_id,first_name,middle_name,last_name,$this->tblname.course_id,year_level,section,date_officially" .
			" FROM $this->tblname,tblstudentinfo,tblcourse" .
			" WHERE ($sy_id BETWEEN $this->tblname.sy_id AND $this->tblname.sy_id_end)" .
			" AND last_name like '" . $last_name ."%'" .
			" AND !(date_officially IS NULL)" .
			" AND $this->tblname.student_id=tblstudentinfo.student_id" .
			" AND $this->tblname.course_id=tblcourse.course_id" .
			($department_id>0 ? " AND tblcourse.department_id=$department_id" : "") .
			" ORDER BY $this->tblname.student_id";
		return $this->query( $cmd );
	}
	
	function get_list_officially_enrolled( $sy_id,$department_id=0,$course_id=0,$year_level=0,$section=0,$date_from=0,$date_to=0,$include_dropped=false ) {
		$cmd =
			"SELECT $this->tblname.student_id,first_name,middle_name,last_name,$this->tblname.course_id,year_level,section,date_officially" .
			" FROM $this->tblname,tblstudentinfo,tblcourse" .
			" WHERE ($sy_id BETWEEN $this->tblname.sy_id AND $this->tblname.sy_id_end)" .
			" AND !(date_officially IS NULL)" .
			" AND $this->tblname.student_id=tblstudentinfo.student_id" .
			" AND $this->tblname.course_id=tblcourse.course_id" .
			($department_id>0 ? " AND tblcourse.department_id=$department_id" : "") .
			($course_id>0 ? " AND tblcourse.course_id=$course_id" : "") .
			($year_level>0 ? " AND $this->tblname.year_level=$year_level" : "") .
			($section>0 ? " AND $this->tblname.section=$section" : "") .
			($date_from!=0 ? " AND date_officially>='$date_from'" : "") .
			($date_to!=0 ? " AND date_officially<='$date_to'" : "") .
			(!$include_dropped ? " AND date_dropped IS NULL" : "") .
			" ORDER BY $this->tblname.student_id";
		return $this->query( $cmd );
	}

	function get_list_officially_enrolled2( $sy_id,$department_id=0,$course_id=0,$year_level=-1,$section=-1 ) {
		$cmd =
			"SELECT $this->tblname.student_id,first_name,middle_name,last_name,tblcourse.department_id,$this->tblname.course_id,tblcourse.short_name,major,minor,year_level,gender,date_of_birth,home_address" .
			" FROM $this->tblname,tblstudentinfo,tblcourse,tbldepartment" .
			" WHERE ($sy_id BETWEEN $this->tblname.sy_id AND $this->tblname.sy_id_end)" .
			" AND !(date_officially IS NULL)" .
			" AND date_dropped IS NULL" .
			" AND $this->tblname.student_id=tblstudentinfo.student_id" .
			" AND $this->tblname.course_id=tblcourse.course_id" .
			" AND tblcourse.department_id=tbldepartment.department_id" .
			($department_id>0 ? " AND tblcourse.department_id=$department_id" : "") .
			($course_id>0 ? " AND $this->tblname.course_id=$course_id" : "") .
			($year_level>=0 ? " AND $this->tblname.year_level=$year_level" : "") .
			($section>=0 ? " AND $this->tblname.section=$section" : "") .
			" ORDER BY tbldepartment.order_no,tbldepartment.long_name,tblcourse.school_id DESC,tblcourse.short_name,year_level,gender DESC,tblcourse.major,tblcourse.minor,last_name,first_name,$this->tblname.student_id";
		return $this->query( $cmd );
	}
	
	function get_summary( $sy_id,$department_id=0 ) {
		$cmd =
			"SELECT count(*) AS total,tblcourse.department_id,$this->tblname.course_id,tblcourse.short_name,major,minor,year_level,section,gender" .
			" FROM $this->tblname,tblcourse,tblstudentinfo,tbldepartment" .
			" WHERE ($sy_id BETWEEN sy_id AND sy_id_end)" .
			" AND date_officially IS NOT NULL" .
			" AND date_dropped IS NULL" .
			" AND $this->tblname.course_id=tblcourse.course_id" .
			" AND $this->tblname.student_id=tblstudentinfo.student_id" .
			" AND tblcourse.department_id=tbldepartment.department_id" .
			($department_id>0 ? " AND tblcourse.department_id=$department_id" : "") .
			" GROUP BY department_id,$this->tblname.course_id,year_level,section,gender" .
			" ORDER BY tbldepartment.order_no,tbldepartment.long_name,tblcourse.school_id DESC,tblcourse.short_name,year_level,tblcourse.major,tblcourse.minor,section,gender";
		return $this->query( $cmd );
	}

	/**
	 *  Cancel Official Enrollment of the student
	 */
	function cancel_official_enrol($sy_id,$student_id) {
		$this->get_by_id($sy_id,$student_id);
		$dat_enrol = $this->get_fetch_assoc(0);
		if( isset($dat_enrol["date_officially"]) ) {
			$dat_enrol["date_officially"] = 'NULL';
			return $this->update( $dat_enrol );
		}
		return true;
	}

	/**
	 *  Update Official Enrollment
	 *    Checks if the student is should be officially enrolled or not and update.
	 */
	function update_official_enrol($sy_id,$student_id) {
		$result = $this->get_by_id( $sy_id,$student_id );
		if( $result==false ) return false;
		$dat = $this->get_fetch_assoc(0);
		if( check_officially_enrolled_direct($sy_id,$student_id) ) {
			if( ! isset($dat["date_officially"]) ) {
				$dat["date_officially"] = date('Y-m-d');
				$result = $this->update( $dat );
			}
		} else {
			if( isset($dat["date_officially"]) ) {
				$dat["date_officially"] = 'NULL';
				$result = $this->update( $dat );
			}
		}
		return $result;
	}

	/**
	 * Runs query to get summary of units of all students
	 */
	function get_unitsummary($sy_id,$department_id=0) {
		$cmd =
			"SELECT tbldepartment.department_id,tblcourse.course_id,$this->tblname.year_level,$this->tblname.student_id,sum(unit) AS total_units" .
			" FROM $this->tblname,tblregist,tblclass,tblcourse,tbldepartment" .
			" WHERE $this->tblname.sy_id=$sy_id" .
			" AND $this->tblname.student_id=tblregist.student_id" .
			" AND tblregist.class_id=tblclass.class_id" .
			" AND $this->tblname.course_id=tblcourse.course_id" .
			($department_id>0 ? " AND tblcourse.department_id=$department_id" : "") .
			" AND tblcourse.department_id=tbldepartment.department_id" .
			" GROUP BY $this->tblname.student_id" .
			" ORDER BY tbldepartment.order_no,tbldepartment.short_name,tblcourse.school_id DESC,tblcourse.short_name,$this->tblname.year_level,total_units";
	 	return $this->query( $cmd );
	}

	/**
	 * Runs query to get ranked list by average grade of students
	 */
	function get_graderanklist($sy_id,$department_id,$course_id=0,$year_level=0,$section=0) {
		$cmd = "SELECT $this->tblname.student_id,first_name,middle_name,last_name,$this->tblname.course_id,$this->tblname.year_level,$this->tblname.section"
			. ",sum(grade_final*unit) AS total_grade,sum(unit) AS total_unit,sum(grade_final*unit)/sum(unit) AS average"
			. " FROM $this->tblname,tblregist,tblclass,tblcourse,tblstudentinfo"
			. " WHERE ($sy_id BETWEEN $this->tblname.sy_id AND $this->tblname.sy_id_end)"
			. " AND !(date_officially IS NULL)"
			. " AND (date_dropped IS NULL)"
			. " AND $this->tblname.student_id=tblstudentinfo.student_id"
			. " AND tblregist.sy_id=$sy_id"
			. " AND $this->tblname.student_id=tblregist.student_id"
			. " AND tblregist.class_id=tblclass.class_id"
			. " AND $this->tblname.course_id=tblcourse.course_id"
			. " AND tblregist.grade_final IS NOT NULL"
			. " AND tblregist.regist_flag & " . REGISTFLAG_GRADECONFIRM
			. ($course_id>0 ? " AND $this->tblname.course_id=$course_id" : " AND tblcourse.department_id=$department_id")
			. ($year_level>0 ? " AND $this->tblname.year_level=$year_level" : "")
			. ($section>0 ? " AND $this->tblname.section=$section" : "")
			. " GROUP BY $this->tblname.student_id"
			. " ORDER BY average";
		return $this->query($cmd);
	}
}



/**
 * Checks if the student is officially enrolled.
 * It will check using tblstudentsenrolled.date_officially.
 * Returns true if officially enrolled, false if not.
 */
function check_officially_enrolled( $sy_id,$student_id,$include_dropped=false )
{
	global $g_dbac;
	$g_dbac->connect();
	$cmd =
		"SELECT tblstudentsenrolled.student_id" .
		" FROM tblstudentsenrolled" .
		" WHERE ($sy_id BETWEEN tblstudentsenrolled.sy_id AND tblstudentsenrolled.sy_id_end)" .
		" AND tblstudentsenrolled.student_id=$student_id" .
		" AND !(date_officially IS NULL)" .
		(!$include_dropped ? " AND date_dropped IS NULL" : "");
	$res = $g_dbac->query( $cmd );
	if( $res && $res->numRows()>0 ) return true;
	return false;
}



/**
 * Checks if the student is officially enrolled.
 * It will check directly the database, regardless of tblstudentsenrolled.date_officially,
 *   if the student have really paid,
 *   or if the students have an guarantor,
 *   or if the students is a scholar.
 * Returns true if officially enrolled, false if not.
 */
function check_officially_enrolled_direct( $sy_id,$student_id )
{
	global $g_dbac;
	$g_dbac->connect();
	$cmd =
		"SELECT tblstudentsenrolled.student_id" .
		" FROM tblstudentsenrolled" .
		" LEFT JOIN tblpayment ON (tblstudentsenrolled.student_id=tblpayment.student_id AND (tblpayment.sy_id BETWEEN tblstudentsenrolled.sy_id AND tblstudentsenrolled.sy_id_end))" .
		" LEFT JOIN tblguarantor ON (tblstudentsenrolled.student_id=tblguarantor.student_id AND (tblguarantor.sy_id BETWEEN tblstudentsenrolled.sy_id AND tblstudentsenrolled.sy_id_end))" .
		" LEFT JOIN tblscholarship ON (tblstudentsenrolled.student_id=tblscholarship.student_id AND (tblscholarship.sy_id BETWEEN tblstudentsenrolled.sy_id AND tblstudentsenrolled.sy_id_end))" .
		" WHERE ($sy_id BETWEEN tblstudentsenrolled.sy_id AND tblstudentsenrolled.sy_id_end)" .
		" AND tblstudentsenrolled.student_id=$student_id" .
		" AND (tblpayment.student_id=tblstudentsenrolled.student_id OR tblguarantor.student_id=tblstudentsenrolled.student_id OR tblscholarship.student_id=tblstudentsenrolled.student_id)";
	$res = $g_dbac->query( $cmd );
	if( $res && $res->numRows()>0 ) return true;
	return false;
}



/**
 * Checks next year_level that student should enrol.
 */
function get_next_year_level( $student_id,$sy_id,$enter_sy ) {
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT year_level FROM tblstudentsenrolled WHERE student_id=$student_id ORDER BY sy_id DESC LIMIT 1";
	$res = $g_dbac->query( $cmd );
	if( (! $res) || ($res->numRows()<=0) ) {
		// not yet enrolled
		$year_level = intval($sy_id/10) - intval($enter_sy/10) + 1;
	} else {
		$dat = $res->fetchRow(DB_FETCHMODE_ASSOC,0);
		if( $dat['year_level']==0 ) {	// if any year_level, any year_level again
			$year_level = 0;
		} else if( ($sy_id%10)==0 ) {	// if 1st semester, increase year_level
			$year_level = $dat['year_level'] + 1;
		} else {
			$year_level = $dat['year_level'];
		}
	}
	if( $year_level < 0 ) $year_level = 0;
	if( $year_level > 5 ) $year_level = 5;
	return $year_level;
}



?>
