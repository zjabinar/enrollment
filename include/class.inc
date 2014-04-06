<?php



require_once("db.inc");
require_once("model.inc");
require_once("yearlevel.inc");
require_once("section.inc");
require_once("feerate.inc");



class model_class extends model {
	
	function model_class() {
		model::model( "tblclass" );
		$this->rowarray = array(
			"class_id"			=> ROWTYPE_INTEGER,
			"sy_id"				=> ROWTYPE_INTEGER,
			"year_level"		=> ROWTYPE_INTEGER,
			"department_id"		=> ROWTYPE_INTEGER,
			"course_id"			=> ROWTYPE_INTEGER,
			"major_ignore"		=> ROWTYPE_INTEGER,
			"section_flag"		=> ROWTYPE_INTEGER,
			"max_student_reg"	=> ROWTYPE_INTEGER,
			"max_student_nreg"	=> ROWTYPE_INTEGER,
			"subject"			=> ROWTYPE_STRING,
			"subject_code"		=> ROWTYPE_STRING,
			"unit"				=> ROWTYPE_INTEGER,
			"exempt"			=> ROWTYPE_INTEGER,
			"flag"				=> ROWTYPE_INTEGER,
			"teacher_id"		=> ROWTYPE_INTEGER,
			"feeelement_id"		=> ROWTYPE_INTEGER,
			"fee_amount"		=> ROWTYPE_INTEGER,
			"unit_lec"			=> ROWTYPE_INTEGER,
			"unit_lab"			=> ROWTYPE_INTEGER
		);
		$this->idcol = "class_id";
	}
	
	function get_list($sy_id,$department_id,$course_id=0,$year_level=0,$section=0,$option=0) {
		$cmd = "SELECT $this->tblname.* FROM $this->tblname";
		if( $course_id>0 ) $cmd .= ",tblcourse";
		$cmd .= " WHERE sy_id=$sy_id";
		$cmd .= " AND $this->tblname.department_id=$department_id";
		if( $course_id>0 ) {
			$course_name = strtok( get_short_course_from_course_id($course_id), ' ' );
			$cmd .= " AND ( ($this->tblname.course_id=tblcourse.course_id AND ($this->tblname.course_id=$course_id OR (tblcourse.short_name='$course_name' AND $this->tblname.major_ignore>0)) ) OR $this->tblname.course_id IS NULL)";
		}
		if( $year_level>0 ) $cmd .= " AND (year_level=$year_level OR year_level=0)";
		if( $section>0 ) $cmd .= " AND ((section_flag & " . intval(0x01<<($section-1)) . ")>0 OR section_flag=0)";
		if( $option>0 ) $cmd .= " AND ((flag & " . intval(0x01<<($option-1)) . ")>0)";
		if( $course_id>0 ) $cmd .= " GROUP BY class_id";
		$cmd .= " ORDER BY subject,subject_code,year_level,section_flag";
		// echo $cmd;
		return $this->query( $cmd );
	}

	function get_list_by_teacher( $sy_id,$teacher_id,$department_id=0 ) {
		$cmd =
			"SELECT * FROM $this->tblname" .
			" WHERE $this->tblname.sy_id=$sy_id" .
			($teacher_id>0 ? " AND $this->tblname.teacher_id=$teacher_id" : " AND $this->tblname.teacher_id IS NULL") .
			($department_id>0 ? " AND $this->tblname.department_id=$department_id" : "") .
			" ORDER BY subject,subject_code";
		return $this->query( $cmd );
	}

	function get_list_by_room( $sy_id,$room_id ) {
		$cmd =
			"SELECT * FROM $this->tblname,tblclassschedule" .
			" WHERE $this->tblname.sy_id=$sy_id" .
			" AND $this->tblname.class_id=tblclassschedule.class_id" .
			" AND room_id=$room_id" .
			" GROUP BY $this->tblname.class_id";
		return $this->query( $cmd );
	}
}



/**
 * Prints basic information about a class
 * Returns class info array
 **/
function print_classinfo_simple( $class_id )
{
	$obj_class = new model_class;
	$obj_class->connect();
	$obj_class->get_by_id( $class_id );
	$dat = $obj_class->get_fetch_assoc(0);

	echo $dat["subject_code"] . ' ' . $dat["subject"] . '<br>';
	if( $dat["year_level"]>0 || $dat["section_flag"]>0 ) {
		if( $dat["year_level"]>0 ) echo lookup_yearlevel( $dat["year_level"] ) . ' ';
		if( $dat["section_flag"]>0 ) echo lookup_section_flag($dat["section_flag"]);
		echo '<br>';
	}
	echo lookup_teacher_name( $dat["teacher_id"] ) . '<br>';
	echo $dat["unit"] . ' units &nbsp;&nbsp;' . get_classflag_string( $dat["flag"] ) . '<br>';
	echo ('Maximun students ' . $dat["max_student_reg"] . '(regular), ' . $dat["max_student_nreg"] . '(irregular)');
	return $dat;
}



?>
