<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/school.inc");
	require_once("../include/course.inc");
	require_once("../include/teacher.inc");
	auth_check( AUTH_ADMIN );
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="base.css" />
<title> EVSU-OCC - System Administration </title>
</head>

<body>

<?php
	global $teacherpos_array;

	print_title( "System Administration", "Position management" );

	if( isset($_REQUEST["edit"]) ) {
		echo '<form method="POST">';
		$position_id = $_REQUEST["position_id"];
		print_hidden( array('position_id'=>$position_id) );
		$obj = new model_tblteacherpos;
		$obj->connect( auth_get_writeable() );
		$res = $obj->get_by_id($position_id);
		$dat = null;
		if( $res ) $dat = $obj->get_fetch_assoc(0);
		if( $position_id==0 ) {
			echo '<div class="error">Not selected</div>';
		} else if( ! isset($_REQUEST["exec"]) ) {
			$teacher_array[0] = ' - none - ';
			$teacher_array += get_teacher_array();
			echo '<table border="1"><tr>';
			echo '<td>' . $teacherpos_array[$position_id] . '</td>';
			echo '<td>' . mkhtml_select('teacher_id',$teacher_array,$dat['teacher_id']) . '</td>';
			echo '</tr></table>';
			echo '<input type="hidden" name="edit">';
			echo '<input type="submit" name="exec" value="Update">';
		} else {
			if( $dat==null ) {
				if( $obj->add( $_REQUEST )==false ) $errmsg = $obj->get_errormsg();
			} else {
				if( $obj->update( $_REQUEST )==false ) $errmsg = $obj->get_errormsg();
			}
			if( $errmsg ) {
				echo '<div class="error">' . $errmsg . '</div>';
			} else {
				echo '<div class="message">Updated successfully</div>';
			}
		}
		echo '</form>';
	} else {
		$list = new model_department;
		$list->connect();
		$list->get_list();
	
		echo '<form method="POST">';
		echo '<table border="1">';
		echo '<tr><th>&nbsp;</th><th>Position</th><th>Teacher</th></tr>';
		foreach( $teacherpos_array as $position_id=>$position_str ) {
			echo '<tr>';
			echo '<td><input type="radio" name="position_id" value="' . $position_id . '"></td>';
			echo '<td>' . $position_str . '</td>';
			echo '<td>' . lookup_teacher_name( lookup_teacherpos($position_id) ) . '</td>';
			echo '</tr>';
		}
		echo '</table>';

		echo '<input type="submit" name="edit" value="Edit">';
		echo '</form>';
	}

	print_footer();

	if( isset($_REQUEST["edit"]) ) {
		echo '<form method="POST" id="goback">';
		echo '<input type="submit" value="Go back">';
		echo '</form>';
	} else {
		echo '<form action="index.php" method="POST" id="goback">';
		echo '<input type="submit" value="Go back">';
		echo '</form>';
	}
?>

</body>

</html>
