<?php



require_once("db.inc");
require_once("semester.inc");
require_once("model.inc");
require_once("classschedule.inc");



define('REGISTFLAG_REGULAR',		0x01);
define('REGISTFLAG_GRADECONFIRM',	0x02);



/**
 * Class for tblregist
 * This table holds enrollment information of students and classes.
 */
class model_regist_class extends model {
	
	function model_regist_class() {
		model::model( "tblregist" );
		$this->rowarray = array(
			"regist_id"		=> ROWTYPE_INTEGER,
			"sy_id"			=> ROWTYPE_INTEGER,
			"student_id"	=> ROWTYPE_INTEGER,
			"class_id"		=> ROWTYPE_INTEGER,
			"date"			=> ROWTYPE_DATE,
			"regist_flag"	=> ROWTYPE_INTEGER,
			"grade_midterm"	=> ROWTYPE_INTEGER,
			"grade_final"	=> ROWTYPE_INTEGER,
			"grade_remark"	=> ROWTYPE_INTEGER
		);
		$this->idcol = "regist_id";
	}
	
	/**
	 * Runs query to get list of classes of certain student of certain school year.
	 */
	function get_list( $sy_id,$student_id ) {
		return $this->query(
			"SELECT * FROM $this->tblname WHERE sy_id=$sy_id AND student_id=$student_id"
		);
	}
	
	/**
	 * Runs query to get list of classes in details of certain student of certain school year.
	 */
	function get_class_list($sy_id,$student_id) {
		return $this->query(
			"SELECT $this->tblname.class_id,subject,subject_code,course_id,major_ignore,year_level,section_flag,teacher_id,unit,exempt,flag,feeelement_id,fee_amount,tbldepartment.short_name AS department_name" .
			(array_key_exists("grade_midterm",$this->rowarray) ? ",grade_midterm,grade_final,grade_remark" : "") .
			(array_key_exists("regist_flag",$this->rowarray) ? ",regist_flag" : "") .
			" FROM $this->tblname,tblclass,tbldepartment" .
			" WHERE $this->tblname.sy_id=$sy_id" .
			" AND student_id=$student_id" .
			" AND $this->tblname.class_id=tblclass.class_id" .
			" AND tblclass.department_id=tbldepartment.department_id" .
			" ORDER BY department_name,subject,subject_code,year_level" );
	}

	/**
	 * Runs query to get list of students of certain class.
	 */
	function get_student_list($sy_id,$class_id) {
		return $this->query(
			"SELECT *" .
			" FROM $this->tblname,tblstudentinfo,tblstudentsenrolled" .
			" WHERE $this->tblname.sy_id=$sy_id" .
			" AND ($sy_id BETWEEN tblstudentsenrolled.sy_id AND tblstudentsenrolled.sy_id_end)" .
			" AND $this->tblname.class_id=$class_id" .
			" AND $this->tblname.student_id=tblstudentinfo.student_id" .
			" AND $this->tblname.student_id=tblstudentsenrolled.student_id" .
			" ORDER BY last_name" );
	}

	/**
	 * Runs query to get list of officially enrolled students of the class.
	 */
	function get_student_list_official($sy_id,$class_id) {
		return $this->query(
			"SELECT *" .
			" FROM $this->tblname,tblstudentinfo,tblstudentsenrolled" .
			" WHERE $this->tblname.sy_id=$sy_id" .
			" AND ($sy_id BETWEEN tblstudentsenrolled.sy_id AND tblstudentsenrolled.sy_id_end)" .
			" AND $this->tblname.class_id=$class_id" .
			" AND $this->tblname.student_id=tblstudentinfo.student_id" .
			" AND $this->tblname.student_id=tblstudentsenrolled.student_id" .
			" AND tblstudentsenrolled.date_officially IS NOT NULL" .
			" AND tblstudentsenrolled.date_dropped IS NULL" .
			" ORDER BY last_name" );
	}
	
	/**
	 * Gets the number of students enrolled regulary to the class.
	 */
	function get_student_count_reg($sy_id,$class_id) {
		$res = $this->query(
			"SELECT count(*) FROM $this->tblname" .
			" WHERE $this->tblname.sy_id=$sy_id" .
			" AND class_id=$class_id" .
			" AND (regist_flag & " . REGISTFLAG_REGULAR . ")"
		);
		if( ! $res ) return 0;
		$dat = $this->get_fetch_array(0);
		return $dat[0];
	}
	
	/**
	 * Gets the number of students enrolled irregulary to the class.
	 */
	function get_student_count_nreg($sy_id,$class_id) {
		$res = $this->query(
			"SELECT count(*) FROM $this->tblname" .
			" WHERE $this->tblname.sy_id=$sy_id" .
			" AND class_id=$class_id" .
			" AND !(regist_flag & " . REGISTFLAG_REGULAR . ")"
		);
		if( ! $res ) return 0;
		$dat = $this->get_fetch_array(0);
		return $dat[0];
	}
	
	/**
	 * Gets the number of students officially enrolled to the class.
	 */
	function get_student_count_officially($sy_id,$class_id) {
		$res = $this->query(
			"SELECT count(*) FROM $this->tblname,tblstudentsenrolled" .
			" WHERE $this->tblname.sy_id=$sy_id" .
			" AND ($sy_id BETWEEN tblstudentsenrolled.sy_id AND tblstudentsenrolled.sy_id_end)" .
			" AND class_id=$class_id" .
			" AND $this->tblname.student_id=tblstudentsenrolled.student_id" .
			" AND date_officially IS NOT NULL" .
			" AND date_dropped IS NULL"
		);
		if( ! $res ) return 0;
		$dat = $this->get_fetch_array(0);
		return $dat[0];
	}
	
//	function get_total_units($sy_id,$student_id) {
//		$this->query(
//			"SELECT sum(unit) FROM $this->tblname,tblclass" .
//			" WHERE $this->tblname.sy_id=$sy_id" .
//			" AND student_id=$student_id" .
//			" AND $this->tblname.class_id=tblclass.class_id" );
//		$ar = model::get_fetch_array(0);
//		return $ar[0];
//	}

	/**
	 * Gets total subject number of the student.
	 */
	function get_total_subjects($sy_id,$student_id) {
		$this->query(
			"SELECT count(*) FROM $this->tblname" .
			" WHERE $this->tblname.sy_id=$sy_id" .
			" AND student_id=$student_id" );
		$ar = model::get_fetch_array(0);
		return $ar[0];
	}

//	function get_total_subjects_flag($sy_id,$student_id,$flag) {
//		$this->query(
//			"SELECT tblclass.flag FROM $this->tblname,tblclass" .
//			" WHERE $this->tblname.sy_id=$sy_id" .
//			" AND student_id=$student_id" .
//			" AND $this->tblname.class_id=tblclass.class_id" .
//			" AND (flag & $flag != 0)" );
//		return model::get_numrows();
//	}

	/**
	 * Gets array of total number of subject,unit,unit for tuition, and number of classes for each classflag.
	 * Return value will be array of subject,unit,unit for tuition, array of numbers which index is each classflag, array of additional fees which is array of feeelement_id and fee_amount.
	 */
	function get_total_info( $sy_id,$student_id ) {
		$classflag_array = get_classflag_array(0,true);
		$this->query(
			"SELECT unit,flag,exempt,feeelement_id,fee_amount,subject_code FROM $this->tblname,tblclass" .
			" WHERE $this->tblname.sy_id=$sy_id" .
			" AND student_id=$student_id" .
			" AND $this->tblname.class_id=tblclass.class_id" );
		$subject = model::get_numrows();
		for( $i=0; $i<$subject; $i++ ) {
			$dat = model::get_fetch_array($i);
			$unit += $dat[0];
			if( $dat[2]==0 ) $unit_tuition += $dat[0];
			foreach( $classflag_array as $idx=>$val ) {
				if( $dat[1] & (0x01<<($idx-1)) ) {
					switch( $val["feeratetype"] ) {
						case FEERATETYPE_PERUNIT:		$class[$idx] += $dat[0];	break;
						case FEERATETYPE_PERSUBJECT:	$class[$idx] ++;			break;
						case FEERATETYPE_PERSEMESTER:	$class[$idx] = 1;			break;
					}
				}
			}
			if( $dat[3]>0 ) {
				$fees[] = array(
					'feeelement_id'=>$dat[3],
					'fee_amount'=>$dat[4],
					'subject_code'=>$dat[5]
				);
			}
		}
		return array($subject,$unit,$unit_tuition,$class,$fees);
	}

	/**
	 * Checks if certain student is enrolled to certain class.
	 * Return true if enrolled, false if not enrolled.
	 */
	function check_regist($sy_id,$student_id,$class_id) {
		$res = $this->query(
			"SELECT * FROM $this->tblname" .
			" WHERE sy_id=$sy_id" .
			" AND student_id=$student_id" .
			" AND class_id=$class_id"
		);
		if( $res==false ) return false;
		if( $this->get_numrows()<=0 ) return false;
		$dat = $this->get_fetch_assoc(0);
		if( $dat["regist_flag"] & REGISTFLAG_REGULAR ) return "R";
		return "NR";
	}

	/**
	 * Deletes the enrollement of student to the class.
	 */
	function del( $sy_id,$student_id,$class_id ) {
		return $this->query(
			"DELETE FROM $this->tblname" .
			" WHERE sy_id=$sy_id" .
			" AND student_id=$student_id" .
			" AND class_id=$class_id"
		);
	}

	/**
	 * Checks if the number of students exceeds the maximum for the class.
	 * Returns true if not, false if it exceeds.
	 */
	function check_max( $class_id,$regular ) {
		$cmd = "SELECT " . ($regular?"max_student_reg":"max_student_nreg") . " FROM tblclass" .
			" WHERE class_id=$class_id";
		if( $this->query( $cmd )==false ) return false;
		if( $this->get_numrows()<=0 ) return false;
		list($max_count) = $this->get_fetch_array(0);

		$cmd = "SELECT count(*) FROM tblregist" .
			" WHERE class_id=$class_id" .
			" AND (regist_flag & " . REGISTFLAG_REGULAR . ")=" . ($regular?REGISTFLAG_REGULAR:0) .
			" FOR UPDATE";
		if( $this->query( $cmd )==false ) return false;
		if( $this->get_numrows()<=0 ) return false;
		list($regist_count) = $this->get_fetch_array(0);
		
		if( $regist_count >= $max_count ) {
			$this->set_error( "Exceeds maximum students" );
			return false;
		}
		return true;
	}

	/**
	 * Checks conflicts of students schedule when specified class is added.
	 * Returns true if not, flase if there are.
	 */
	function check_schedule_conflicts( $class_id,$student_id ) {
		$schedule = new model_classschedule;
		$schedule->connect();
		$schedule->get_list( $class_id );
		for( $i=0; $i<$schedule->get_numrows(); $i++ ) {
			$sch = $schedule->get_fetch_assoc($i);
			$sy_id = $sch['sy_id'];
			$time_st = $sch['time_st'];
			$time_end = $sch['time_end'];
			$cmd =
				"SELECT count(*) FROM $this->tblname,tblclassschedule" .
				" WHERE $this->tblname.sy_id=$sy_id" .
				" AND $this->tblname.student_id=$student_id" .
				" AND $this->tblname.class_id=tblclassschedule.class_id" .
				" AND ( time_st<$time_end AND time_end>$time_st )" .
				" FOR UPDATE";
			if( $this->query( $cmd )==false ) return false;
			list($count) = $this->get_fetch_array(0);
			if( $count>0 ) {
				$this->set_error( 'Student class schedule conflicts' );
				return false;
			}
		}
		return true;
	}

	/**
	 * Checks the number of maximum students and adds the data if its OK.
	 * Returns true if successfully enrolled, false if not.
	 */
	function check_and_add( $rowdata,$ignore_conflicts=false ) {
		if( $this->begin_transaction()==false ) return $this->rollback();
		if( $this->check_max($rowdata["class_id"],$rowdata["regist_flag"] & REGISTFLAG_REGULAR )==false ) return $this->rollback();
		if( ! $ignore_conflicts ) {
			if( $this->check_schedule_conflicts($rowdata["class_id"],$rowdata["student_id"])==false ) return $this->rollback();
		}
		if( model::add( $rowdata )==false ) return $this->rollback();
		if( $this->end_transaction()==false ) return $this->rollback();
		return true;
	}
	
	/**
	 * Confirm grades.
	 * Set REGISTFLAG_GRADECONFIRM to regist_flag of officially enrolled student of specified class.
	 */
	function confirm_grade( $class_id ) {
		$cmd = "UPDATE $this->tblname,tblstudentsenrolled"
			. " SET regist_flag=regist_flag|" . REGISTFLAG_GRADECONFIRM
			. " WHERE $this->tblname.class_id=$class_id"
			. " AND $this->tblname.student_id=tblstudentsenrolled.student_id"
			. " AND ($this->tblname.sy_id BETWEEN tblstudentsenrolled.sy_id AND tblstudentsenrolled.sy_id_end)"
			. " AND tblstudentsenrolled.date_officially IS NOT NULL"
			. " AND tblstudentsenrolled.date_dropped IS NULL";
		return $this->query( $cmd );
	}

	/**
	 * Unconfirm grades.
	 * unset REGISTFLAG_GRADECONFIRM from regist_flag of specified class.
	 */
	function unconfirm_grade( $class_id ) {
		$cmd = "UPDATE $this->tblname"
			. " SET regist_flag=regist_flag&(~" . REGISTFLAG_GRADECONFIRM . ")"
			. " WHERE class_id=$class_id";
		return $this->query( $cmd );
	}
}



class model_add_class extends model_regist_class {
	
	function model_add_class() {
		model::model( "tbladd" );
		$this->rowarray = array(
			"add_id"		=> ROWTYPE_INTEGER,
			"sy_id"			=> ROWTYPE_INTEGER,
			"student_id"	=> ROWTYPE_INTEGER,
			"class_id"		=> ROWTYPE_INTEGER,
			"date"			=> ROWTYPE_DATE
		);
		$this->idcol = "add_id";
	}
	
	function add( $rowdata, $ignore_conflicts=false ) {
		$regist = new model_regist_class;
		$regist->connect( $this->writeable );
		if( $this->begin_transaction()==false ) return $this->rollback();
		if( $regist->check_and_add( $rowdata,$ignore_conflicts )==false ) return $this->rollback();
		if( model::add( $rowdata )==false ) return $this->rollback();
		if( $this->end_transaction()==false ) return $this->rollback();
		return true;
	}
}



class model_drop_class extends model_regist_class {
	
	function model_drop_class() {
		model::model( "tbldrop" );
		$this->rowarray = array(
			"drop_id"		=> ROWTYPE_INTEGER,
			"sy_id"			=> ROWTYPE_INTEGER,
			"student_id"	=> ROWTYPE_INTEGER,
			"class_id"		=> ROWTYPE_INTEGER,
			"date"			=> ROWTYPE_DATE
		);
		$this->idcol = "drop_id";
	}

	function add( $rowdata ) {
		$regist = new model_regist_class;
		$regist->connect( $this->writeable );
		if( $this->begin_transaction()==false ) return $this->rollback();
		if( model::add( $rowdata )==false ) return $this->rollback();
		if( $regist->del( $rowdata["sy_id"],$rowdata["student_id"],$rowdata["class_id"] )==false ) return $this->rollback();
		if( $this->end_transaction()==false ) return $this->rollback();
		return true;
	}

	function drop_all( $sy_id,$student_id ) {
		$regist = new model_regist_class;
		$regist->connect();
		$regist->get_list($sy_id,$student_id);
		for( $i=0; $i<$regist->get_numrows(); $i++ ) {
			$class_list[$i] = $regist->get_fetch_assoc($i);
		}
		if( $this->begin_transaction()==false ) return $this->rollback();
		if( $regist->get_numrows() > 0 ) {
			foreach( $class_list as $dat ) {
				if( $this->add( $dat )==false ) return $this->rollback();
			}
		}
		if( $this->end_transaction()==false ) return $this->rollback();
		return true;
	}

}



class model_change_class extends model_regist_class {
	
	function model_change_class() {
		model::model( "tblchange" );
		$this->rowarray = array(
			"change_id"		=> ROWTYPE_INTEGER,
			"sy_id"			=> ROWTYPE_INTEGER,
			"student_id"	=> ROWTYPE_INTEGER,
			"class_id"		=> ROWTYPE_INTEGER,
			"new_class_id"	=> ROWTYPE_INTEGER,
			"date"			=> ROWTYPE_DATE
		);
		$this->idcol = "change_id";
	}

	function add( $rowdata, $ignore_conflicts=false ) {
		$regist = new model_regist_class;
		$regist->connect( $this->writeable );
		if( $this->begin_transaction()==false ) return $this->rollback();
		if( model::add( $rowdata )==false ) return $this->rollback();
		if( $regist->del( $rowdata["sy_id"],$rowdata["student_id"],$rowdata["class_id"] )==false ) return $this->rollback();
		$rowdata["class_id"] = $rowdata["new_class_id"];
		if( $regist->check_and_add( $rowdata, $ignore_conflicts )==false ) return $this->rollback();
		if( $this->end_transaction()==false ) return $this->rollback();
		return true;
	}

}



?>
