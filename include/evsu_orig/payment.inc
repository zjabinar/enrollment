<?php



require_once("db.inc");
require_once("model.inc");
require_once("feeelement.inc");



/**
 *  Class for tblpayment.
 *  tblpayment holds data about students' payment record.
 */
class model_payment extends model {
	
	function model_payment() {
		model::model( "tblpayment" );
		$this->rowarray = array(
			"payment_id"	=> ROWTYPE_INTEGER,
			"sy_id"			=> ROWTYPE_INTEGER,
			"student_id"	=> ROWTYPE_INTEGER,
			"feeelement_id"	=> ROWTYPE_INTEGER,
			"payment"		=> ROWTYPE_INTEGER,
			"date"			=> ROWTYPE_DATE,
			"orno"			=> ROWTYPE_INTEGER,
			"user_id"		=> ROWTYPE_INTEGER
		);
		$this->idcol = "payment_id";
	}

	/**
	 * Overrides model::get_list()
	 * Runs query to get list of payment of particular student.
	 */
	function get_list($sy_id,$student_id) {
		return $this->query(
			"SELECT * FROM $this->tblname WHERE sy_id=$sy_id AND student_id=$student_id"
		);
	}

	/**
	 * Runs query to get list of payment of particular student grouped by feecategory.
	 */
	function get_list_group_by_category($sy_id,$student_id,$date=0,$orno=0) {
		return $this->query(
			"SELECT date,orno,tblfeeelement.feecategory_id,sum(payment) AS payment" .
			" FROM $this->tblname,tblfeeelement" .
			" WHERE sy_id=$sy_id" .
			" AND student_id=$student_id" .
			" AND $this->tblname.feeelement_id=tblfeeelement.feeelement_id" .
			($date!=0 ? " AND date='$date'" : "") .
			($orno!=0 ? " AND orno='$orno'" : "") .
			" GROUP BY date,orno,tblfeeelement.feecategory_id" .
			" ORDER BY date,orno"
		);
	}

	/**
	 * Runs query to get list of payment of optional payment of particular student.
	 */
	function get_list_optional($sy_id,$student_id) {
		$cmd = "SELECT $this->tblname.feeelement_id,title,feecategory_id,sum(payment) AS payment FROM $this->tblname,tblfeeelement"
			. " WHERE sy_id=$sy_id AND student_id=$student_id"
			. " AND $this->tblname.feeelement_id=tblfeeelement.feeelement_id"
			. " AND (fee_flag & " . FEEFLAG_OPTIONALFEE . ")!=0"
			. " GROUP BY feeelement_id";
		return $this->query( $cmd );
	}

	/**
	 * Runs query to get list of payment grouped by orno.
	 */
	function get_list_of_date_orno($sy_id_from,$sy_id_to,$date_from,$date_to,$collector_id=0) {
		$cmd = "SELECT date,orno,$this->tblname.student_id,first_name,middle_name,last_name,sum(payment) AS payment,$this->tblname.user_id,fullname"
			. " FROM $this->tblname,tblstudentinfo,tblauth"
			. " WHERE (sy_id BETWEEN $sy_id_from AND $sy_id_to)"
			. " AND $this->tblname.student_id=tblstudentinfo.student_id"
			. " AND $this->tblname.user_id=tblauth.user_id"
			. ($date_from>0 ? " AND date>='$date_from'" : "")
			. ($date_to>0 ? " AND date<='$date_to'" : "")
			. ($collector_id>0 ? " AND $this->tblname.user_id=$collector_id" : "")
			. " GROUP BY date,orno,$this->tblname.student_id"
			. " ORDER BY date,orno,$this->tblname.student_id";
		return $this->query( $cmd );
	}

	/**
	 * Gets payment data of particular student's particular feeelement.
	 */
	function get_payment_of( $sy_id,$student_id,$feeelement_id=0 ) {
		$cmd =
			"SELECT sum(payment) AS payment FROM $this->tblname" .
			" WHERE sy_id=$sy_id" .
			" AND student_id=$student_id" .
			($feeelement_id>0 ? " AND feeelement_id=$feeelement_id" : "");
		if( $this->query($cmd)==false ) return 0;
		$dat = $this->get_fetch_array(0);
		return $dat[0];
	}

	/**
	 * Runs query to get list of payment data of particular student's with date.
	 */
	function get_payment_list_of( $sy_id,$student_id,$date_to=0 ) {
		$cmd =
			"SELECT sum(payment) AS payment,feeelement_id FROM $this->tblname" .
			" WHERE sy_id=$sy_id" .
			" AND student_id=$student_id" .
			($date_to!=0 ? " AND date<='$date_to'" : "") .
			" GROUP BY feeelement_id";
		return $this->query($cmd);
	}

	/**
	 * Runs query to get summary of collection grouped by feeelement.
	 */
	function get_summary_by_feeelement($sy_id_from,$sy_id_to,$date_from,$date_to,$collector_id=0) {
		$cmd =
			"SELECT $this->tblname.feeelement_id,feecategory_id,title,sum(payment) AS payment" .
			" FROM $this->tblname,tblfeeelement" .
			" WHERE (sy_id BETWEEN $sy_id_from AND $sy_id_to)" .
			" AND $this->tblname.feeelement_id=tblfeeelement.feeelement_id" .
			($date_from>0 ? " AND date>='$date_from'" : "") .
			($date_to>0 ? " AND date<='$date_to'" : "") .
			($collector_id>0 ? " AND user_id=$collector_id" : "") .
			" GROUP BY feeelement_id";
		return $this->query( $cmd );
	}

	/**
	 * Runs query to get the list of feeelement which have payment on specific school year.
	 */
	function get_list_of_feeelement($sy_id) {
		$cmd =
			"SELECT $this->tblname.feeelement_id,feecategory_id,title" .
			" FROM $this->tblname,tblfeeelement" .
			" WHERE sy_id=$sy_id" .
			" AND $this->tblname.feeelement_id=tblfeeelement.feeelement_id" .
			" GROUP BY feeelement_id" .
			" ORDER BY feecategory_id,title";
		return $this->query( $cmd );
	}

	/**
	 * Runs query to get the list of students' payment by feeelement.
	 */
	function get_list_by_feeelement($sy_id_from,$sy_id_to,$date_from,$date_to) {
		$cmd =
			"SELECT $this->tblname.student_id,first_name,middle_name,last_name,date,orno,$this->tblname.feeelement_id,payment" .
			" FROM $this->tblname,tblstudentinfo" .
			" WHERE (sy_id BETWEEN $sy_id_from AND $sy_id_to)" .
			($date_from>0 ? " AND date>='$date_from'" : "") .
			($date_to>0 ? " AND date<='$date_to'" : "") .
			" AND $this->tblname.student_id=tblstudentinfo.student_id" .
			" ORDER BY date,orno,$this->tblname.student_id";
		return $this->query( $cmd );
	}

	/**
	 * Runs query to change date/orno of the specific record.
	 */
	function change_date_orno( $sy_id,$student_id,$date,$orno,$new_date,$new_orno ) {
		// check if new date is valid
		if( $new_date=='' ) {
			$this->set_error('Date is required');
			return false;
		}
		// begin transaction
		if( $this->begin_transaction()==false ) return false;
		// update extra information
		if( $orno!=$new_orno && $orno!='' && $new_orno!='' ) {
			$cmd = "UPDATE tblpayment_extrainfo SET orno='$new_orno' WHERE orno='$orno'";
			if( $this->query( $cmd )==false ) return $this->rollback();
		}
		// update
		if( $this->update_cond(
				array( 'sy_id'=>$sy_id, 'student_id'=>$student_id, 'orno'=>($orno=='' ? 'NULL' : $orno), 'date'=>$date ),
				array( 'date'=>$new_date, 'orno'=>($new_orno=='' ? 'NULL' : $new_orno) )
			)==false ) return $this->rollback();
		// end transaction
		return $this->end_transaction();
	}

	/**
	 * Overrides model::del()
	 * Runs query to delete specific record.
	 */
	function del( $sy_id,$student_id,$date,$orno ) {
		if( $this->begin_transaction()==false ) return false;
		if( $orno > 0 ) {
			$cmd = "DELETE FROM tblpayment_extrainfo WHERE orno='$orno'";
			if( $this->query( $cmd )==false ) return $this->rollback();
		}
		$cmd = "DELETE FROM $this->tblname"
			. " WHERE sy_id='$sy_id'"
			. " AND student_id='$student_id'"
			. ($orno > 0 ? " AND orno='$orno'" : "")
			. " AND date='$date'";
		if( $this->query( $cmd )==false ) return $this->rollback();
		return $this->end_transaction();
	}
}



/**
 * Class for table tblpayment_extrainfo.
 * tblpayment_extrainfo holds data about different payer of specified(orno) payment.
 */
class model_payment_extrainfo extends model {

	function model_payment_extrainfo() {
		model::model( "tblpayment_extrainfo" );
		$this->rowarray = array(
			"orno"		=> ROWTYPE_INTEGER,
			"payor"		=> ROWTYPE_STRING
		);
		$this->idcol = "orno";
	}
}



/**
 * Gets array of collector.
 * Returns array which index is user_id and value is fullname.
 */
function get_collector_array( $sy_id )
{
	global $g_dbac;
	$g_dbac->connect();
	$cmd = "SELECT tblauth.user_id,tblauth.fullname FROM tblpayment,tblauth"
		. " WHERE sy_id=$sy_id"
		. " AND tblpayment.user_id=tblauth.user_id"
		. " GROUP BY tblauth.user_id";
	$ar = array();
	$res = $g_dbac->query( $cmd );
	if( $res==false ) return $ar;
	for( $n=0; $n<$res->numRows(); $n++ ) {
		$dat = $res->fetchRow(DB_FETCHMODE_ARRAY,$n);
		$ar[$dat[0]] = $dat[1];
	}
	return $ar;
}



?>
