<?php



require_once("db.inc");
require_once("model.inc");



function get_weekday_array()
{
	return array( 'M','T','W','TH','F','S','SUN' );
}



class model_classschedule extends model {
	
	function model_classschedule() {
		model::model( "tblclassschedule" );
		$this->rowarray = array(
			"schedule_id"	=> ROWTYPE_INTEGER,
			"sy_id"			=> ROWTYPE_INTEGER,
			"class_id"		=> ROWTYPE_INTEGER,
			"room_id"		=> ROWTYPE_INTEGER,
			"time_st"		=> ROWTYPE_INTEGER,
			"time_end"		=> ROWTYPE_INTEGER
		);
		$this->idcol = "schedule_id";
	}

	function get_list( $class_id ) {
		$cmd =
			"SELECT * FROM $this->tblname" .
			" WHERE class_id=$class_id" .
			" ORDER BY time_st";
		return $this->query( $cmd );
	}

	function get_list_of_teacher( $sy_id,$teacher_id ) {
		$cmd =
			"SELECT * FROM $this->tblname,tblclass" .
			" WHERE $this->tblname.sy_id=$sy_id" .
			" AND $this->tblname.class_id=tblclass.class_id" .
			" AND tblclass.teacher_id=$teacher_id" .
			" ORDER BY time_st";
		return $this->query( $cmd );
	}

	function get_list_of_room( $sy_id,$room_id ) {
		$cmd =
			"SELECT * FROM $this->tblname,tblclass" .
			" WHERE $this->tblname.sy_id=$sy_id" .
			" AND $this->tblname.room_id=$room_id" .
			" AND $this->tblname.class_id=tblclass.class_id" .
			" ORDER BY time_st";
		return $this->query( $cmd );
	}
	
	function get_list_of_student( $sy_id,$student_id ) {
		$cmd =
			"SELECT * FROM $this->tblname,tblclass,tblregist" .
			" WHERE tblregist.sy_id=$sy_id" .
			" AND tblregist.student_id=$student_id" .
			" AND tblregist.class_id=$this->tblname.class_id" .
			" AND $this->tblname.class_id=tblclass.class_id" .
			" ORDER BY time_st";
		return $this->query( $cmd );
	}

	function check_conflicts( $sy_id,$time_st,$time_end,$room_id,$teacher_id,$exclude_schedule_id=0 ) {
		// check room schedule conflicts
		if( $room_id>0 ) {
		    $cmd =
			    "SELECT count(*) FROM $this->tblname" .
			    " WHERE sy_id=$sy_id" .
			    " AND room_id=$room_id" .
			    ($exclude_schedule_id>0 ? " AND schedule_id<>$exclude_schedule_id" : "") .
			    " AND ( time_st<$time_end AND time_end>$time_st )" .
			    " FOR UPDATE";
		    if( $this->query( $cmd )==false ) return false;
		    list($count) = $this->get_fetch_array(0);
		    if( $count>0 ) {
			    $this->set_error( 'Room schedule conflicts' );
			    return false;
		    }
		}

		// check teacher schedule conflicts
		if( $teacher_id>0 ) {
		    $cmd =
			    "SELECT count(*) FROM $this->tblname,tblclass" .
			    " WHERE $this->tblname.sy_id=$sy_id" .
			    " AND $this->tblname.class_id=tblclass.class_id" .
			    ($exclude_schedule_id>0 ? " AND schedule_id<>$exclude_schedule_id" : "") .
			    " AND tblclass.teacher_id=$teacher_id" .
			    " AND ( time_st<$time_end AND time_end>$time_st )" .
			    " FOR UPDATE";
		    if( $this->query( $cmd )==false ) return false;
		    list($count) = $this->get_fetch_array(0);
		    if( $count>0 ) {
			    $this->set_error( 'Teacher schedule conflicts' );
			    return false;
		    }
		}
		return true;
	}

	function check_and_add( $rowdata ) {
		$sy_id = $rowdata["sy_id"];
		$time_st = $rowdata["time_st"];
		$time_end = $rowdata["time_end"];
		$room_id = $rowdata["room_id"];
		$class_id = $rowdata["class_id"];
		if( $this->query( "SELECT teacher_id FROM tblclass WHERE class_id=$class_id" )==false ) return false;
		list($teacher_id) = $this->get_fetch_array(0);

		if( $this->begin_transaction()==false ) return $this->rollback();
		if( $this->check_conflicts( $sy_id,$time_st,$time_end,$room_id,$teacher_id )==false ) return $this->rollback();
		if( $this->add( $rowdata )==false ) return $this->rollback();
		if( $this->end_transaction()==false ) return $this->rollback();
		return true;
	}

	function check_and_update( $rowdata ) {
		$sy_id = $rowdata["sy_id"];
		$time_st = $rowdata["time_st"];
		$time_end = $rowdata["time_end"];
		$room_id = $rowdata["room_id"];
		$class_id = $rowdata["class_id"];
		if( $this->query( "SELECT teacher_id FROM tblclass WHERE class_id=$class_id" )==false ) return false;
		list($teacher_id) = $this->get_fetch_array(0);

		if( $this->begin_transaction()==false ) return $this->rollback();
		if( $this->check_conflicts( $sy_id,$time_st,$time_end,$room_id,$teacher_id,$rowdata["schedule_id"] )==false ) return $this->rollback();
		if( $this->update( $rowdata )==false ) return $this->rollback();
		if( $this->end_transaction()==false ) return $this->rollback();
		return true;
	}

	/**
	 *  Gets schedule of class.<br>
	 *  Returns array of array(RoomCode in string, Weekday in string, Time in string).<br>
	 *    ex.<br>
	 *  $obj = new model_classschedule;<br>
	 *  $obj->connect();<br>
	 *  $schedule_array = $obj->get_schedule_array( $class_id );<br>
	 *  foreach( $schedule_array[] as $schedule ) {<br>
	 *    printf( "Room:%s, Weekday:%s, Time:%s\n", $schedule[0],$schedule[1],$schedule[2] );<br>
	 *  }<br>
	 */
	function get_schedule_array($class_id) {
		$result = array();
		$weekday_array = get_weekday_array();
		if( $this->get_list( $class_id )==false ) return $result;
		for( $i=0; $i<$this->get_numrows(); $i++ ) {
			$dat = $this->get_fetch_assoc($i);
			list($wday,$st_ampm,$st_hour,$st_min,$end_ampm,$end_hour,$end_min,$len_hour,$len_min) = retrieve_time($dat);
			$room_code = lookup_room_name($dat["room_id"]);
			$week_str = $weekday_array[$wday];
			$time_str = sprintf( "%d:%02d-%d:%02d(%s)",
				$st_hour,$st_min,$end_hour,$end_min,
				($st_ampm ? "PM" : "AM")
			);
			$found = false;
			foreach( $result as $idx=>$val ) {
				if( ($val[0]==$room_code) && ($val[2]==$time_str) ) {
					$result[$idx][1] .= $week_str;
					$found = true;
					break;
				}
			}
			if( ! $found ) {
				$result[] = array( $room_code,$week_str,$time_str );
			}
		}
		return $result;
	}

	function del_by_class_id( $class_id ) {
		$cmd = "DELETE FROM $this->tblname WHERE class_id=$class_id";
		return $this->query( $cmd );
	}
}



function lookup_room_name($room_id)
{
        if( $room_id==0 ) return "";
        global $g_dbac;
        $g_dbac->connect();
        $cmd = "SELECT room_code FROM tblroom WHERE room_id=$room_id";
        $res = $g_dbac->query( $cmd );
        if( ! $res ) return "";
        $ar = $res->fetchRow();
        return $ar[0];
}



function retrieve_time( $dat ) {
	$time_st = $dat["time_st"];
	$time_end = $dat["time_end"];
	$wday = intval( $time_st / (24*60) );
	$st_ampm = intval(($time_st/60)%24) >= 12 ? 1 : 0;
	$st_hour = intval(($time_st/60-1)%12)+1;
	$st_min = intval($time_st % 60);
	$end_ampm = intval(($time_end/60)%24) >= 12 ? 1 : 0;
	$end_hour = intval(($time_end/60-1)%12+1);
	$end_min = intval($time_end % 60);
	$len_hour = intval( ($time_end-$time_st)/60 );
	$len_min = intval( ($time_end-$time_st)%60 );
	return array( $wday,$st_ampm,$st_hour,$st_min,$end_ampm,$end_hour,$end_min,$len_hour,$len_min );
}



?>
