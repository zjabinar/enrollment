<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/school.inc");
	require_once("../include/course.inc");
	auth_check( AUTH_ADMIN );
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<script type="text/JavaScript" src="../option/option.js"></script>
<link rel="stylesheet" type="text/css" href="base.css" />
<title> EVSU-OCC - System Administration </title>
</head>

<body onLoad="optionOnLoad()" onResize="optionOnResize()">

<?php
	print_title( "System Administration", "Course management" );

	if( isset($_REQUEST["add"]) ) {
		if( ! isset($_REQUEST["exec"]) ) {
			echo '<form method="POST">';
			echo '<table border="1">';
			echo '<tr><td>department</td><td>' . mkhtml_select("department_id",get_department_array(),MKHTML_SELECT_FIRST) . '</td></tr>';
			echo '<tr><td>short name</td><td><input type="text" name="short_name"></td></tr>';
			echo '<tr><td>long name</td><td><input type="text" name="long_name"></td></tr>';
			echo '<tr><td>major</td><td><input type="text" name="major"></td></tr>';
			echo '<tr><td>minor</td><td><input type="text" name="minor"></td></tr>';
			echo '<tr><td>school level</td><td>' . mkhtml_select("school_id",get_schoolid_array(),MKHTML_SELECT_FIRST) . '</td></tr>';
			echo '</table>';
			echo '<input type="hidden" name="add">';
			echo '<input type="submit" name="exec" value="Add">';
			echo '</form>';
		} else {
			$course = new model_course;
			$course->connect( auth_get_writeable() );
			if( $course->add( $_REQUEST )==false ) {
				echo '<div class="error">' . $course->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Added successfully</div>';
			}
		}
	} else if( isset($_REQUEST["edit"]) ) {
		if( ! isset($_REQUEST["exec"]) ) {
			$course = new model_course;
			$course->connect();
			$course->get_by_id( $_REQUEST["course_id"] );
			$dat = $course->get_fetch_assoc(0);
			echo '<form method="POST">';
			echo '<table border="1">';
			echo '<tr><td>department</td><td>' . mkhtml_select("department_id",get_department_array(),$dat["department_id"]) . '</td></tr>';
			echo '<tr><td>short name</td><td><input type="text" name="short_name" value="' . $dat["short_name"] . '"></td></tr>';
			echo '<tr><td>long name</td><td><input type="text" name="long_name" value="' . $dat["long_name"] . '"></td></tr>';
			echo '<tr><td>major</td><td><input type="text" name="major" value="' . $dat["major"] . '"></td></tr>';
			echo '<tr><td>minor</td><td><input type="text" name="minor" value="' . $dat["minor"] . '"></td></tr>';
			echo '<tr><td>school level</td><td>' . mkhtml_select("school_id",get_schoolid_array(),$dat["school_id"]) . '</td></tr>';
			echo '</table>';
			echo '<input type="hidden" name="edit">';
			echo '<input type="submit" name="exec" value="Update">';
			echo '<input type="hidden" name="course_id" value="' . $_REQUEST["course_id"] . '">';
			echo '</form>';
		} else {
			$course = new model_course;
			$course->connect( auth_get_writeable() );
			if( $course->update( $_REQUEST )==false ) {
				echo '<div class="error">' . $course->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Updated successfully</div>';
			}
		}
	} else if( isset($_REQUEST["del"]) ) {
		if( ! isset($_REQUEST["exec"]) ) {
			$course = new model_course;
			$course->connect();
			$course->get_by_id( $_REQUEST["course_id"] );
			$dat = $course->get_fetch_assoc(0);
			echo '<form method="POST">';
			echo '<div class="prompt">';
			echo 'Deleting course ' . $dat["short_name"] . '(' . $dat["long_name"] . ' ' . $dat["major"] . ' ' . $dat["minor"] . ').<br>';
			echo 'OK?';
			echo '</div>';
			echo '<input type="hidden" name="del">';
			echo '<input type="submit" name="exec" value="Delete">';
			echo '<input type="hidden" name="course_id" value="' . $_REQUEST["course_id"] . '">';
			echo '</form>';
		} else {
			$course = new model_course;
			$course->connect( auth_get_writeable() );
			if( $course->del( $_REQUEST["course_id"] )==false ) {
				echo '<div class="error">' . $course->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Deleted successfully</div>';
			}
		}
	} else {
		$list = new model_course;
		$list->connect();
		$list->get_list();
	
		echo '<form method="POST">';
		echo '<div id="scrolllist">';
		echo '<table border="1">';
		echo '<tr><th>&nbsp;</th><th>department</th><th>short name</th><th>long name</th><th>major</th><th>minor</th><th>SchoolLevel</th></tr>';
		for( $i=0; $i<$list->get_numrows(); $i++ ) {
			$dat = $list->get_fetch_assoc($i);
			echo '<tr>';
			echo '<td><input type="radio" name="course_id" value="' . $dat["course_id"] . '"></td>';
			echo '<td>' . get_short_department_from_department_id($dat["department_id"]) . '</td>';
			echo '<td>' . $dat["short_name"] . '</td>';
			echo '<td>' . $dat["long_name"] . '</td>';
			echo '<td>' . mkstr_neat($dat["major"]) . '</td>';
			echo '<td>' . mkstr_neat($dat["minor"]) . '</td>';
			echo '<td>' . lookup_schoolid($dat['school_id']) . '</td>';
			echo '</tr>';
		}
		echo '</table>';
		echo '</div>';

		echo '<input type="submit" name="add" value="Add">';
		echo '<input type="submit" name="edit" value="Edit">';
		echo '<input type="submit" name="del" value="Delete">';
		echo '</form>';
	}

	print_footer();

	if( isset($_REQUEST["add"]) || isset($_REQUEST["edit"]) || isset($_REQUEST["del"]) || isset($_REQUEST["cmd"]) ) {
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
