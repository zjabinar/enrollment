<?php



require_once("db.inc");
require_once("model.inc");
require_once("util.inc");



/**
 * Class for table tblblockstudent.
 *   Holds student_id that is blocked by some offices.
 *   office_id is AUTH_xxx or Department No. Same as in authflag.
 */
class model_tblblockstudent extends model
{
	/**
	 * Constructor
	 */
	function model_tblblockstudent() {
		model::model( "tblblockstudent" );
		$this->rowarray = array(
			"block_id"		=> ROWTYPE_INTEGER,
			"student_id"	=> ROWTYPE_INTEGER,
			"office_id"		=> ROWTYPE_INTEGER,
			"message"		=> ROWTYPE_STRING,
			"date"			=> ROWTYPE_DATE
		);
		$this->idcol = "block_id";
	}

	/**
	 * Runs query to search about the blocking of the student.
	 * If office_id is set, it will only search the specified office.
	 * If office_id is not set or zero, it will search from all the offices.
	 */
	function get_by_student_id( $student_id, $office_id=0 ) {
		$cmd = "SELECT * FROM $this->tblname"
			. " WHERE student_id=$student_id"
			. ($office_id>0 ? " AND office_id=$office_id" : "");
		return $this->query($cmd);
	}

	/**
	 * Runs query to list all blocked students.
	 **/
	function get_list( $office_id=0 ) {
		$cmd = "SELECT * FROM $this->tblname,tblstudentinfo"
			. " WHERE $this->tblname.student_id=tblstudentinfo.student_id"
			. ($office_id>0 ? " AND office_id=$office_id" : "")
			. " ORDER BY $this->tblname.student_id";
		return $this->query($cmd);
	}
}



/**
 * Checks if the student is blocked.
 * If blocked, it returns array of associative array of each blocking info.
 * If not, null.
 */
function lookup_blockstudent( $student_id,$office_id=0 )
{
	$obj = new model_tblblockstudent;
	$obj->connect();
	$res = $obj->get_by_student_id($student_id,$office_id);
	if( $res && ($obj->get_numrows()>0) ) {
		$ar = array();
		for( $i=0; $i<$obj->get_numrows(); $i++ ) {
			$dat = $obj->get_fetch_assoc($i);
			$ar[$dat['block_id']] = $dat;
		}
		return $ar;
	} else {
		return null;
	}
}



/**
 * Prints information about blocking.
 * Parameter $blockdat is the value returned from lookup_blockstudent()
 */
function print_blockstudent_info( $blockdat )
{
	echo '<div class="warning">';
	foreach( $blockdat as $dat ) {
		echo 'The student is blocked by ' . get_office_name($dat['office_id']) . '<br>';
		echo '&nbsp;"' . $dat['message'] . '"';
		if( $dat['date']>0 ) echo ' (' . mkstr_date($dat['date']) . ')';
	}
	echo '</div>';
}



?>
