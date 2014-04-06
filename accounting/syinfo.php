<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/course.inc");
	require_once("../include/syinfo.inc");
	require_once("../include/yearlevel.inc");
	auth_check( AUTH_ACCOUNTING );
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="base.css" />
<title> Semester Info </title>
</head>

<body>
<?php
	print_heading();
?>
<?php
	$sy_id = $_SESSION["sy_id"];
	$str_schoolyear = lookup_schoolyear($sy_id);
	print_title( get_office_name($_SESSION["office"]), "Date of Enrollment", $str_schoolyear );

	echo "<form method=\"POST\">";
	if( isset($_REQUEST["update"]) ) {
		$list = new model_syinfo;
		$list->connect( auth_get_writeable() );
		
		$result = true;
		foreach( $_POST["lastday"] as $id => $v ) {
			$ar = array(
				"syinfo_id" => $id,
				"lastday_of_enrol" => retrieve_date($v[0]),
				"lastday_of_changing" => retrieve_date($v[1])
			);
			$result = $list->update( $ar );
			if( ! $result ) break;
		}
		if( ! $result ) {
			echo '<div class="error">' . $list->get_errormsg() . '</div>';
		} else {
			echo '<div class="message">Successfully updated</div>';
		}
	} else if( isset($_REQUEST["add"]) ) {
		if( ! isset($_REQUEST["exec"]) ) {
			echo '<div class="prompt">Enter department exception data</div>';
			echo '<table border="1">';
			echo '<tr><td>Department</td><td>' . mkhtml_select( "department_id",get_department_array(),MKHTML_SELECT_FIRST ) . '</td></tr>';
			$ca[0] = '- all -';
			$ca += get_course_array();
			echo '<tr><td>Course</td><td>' . mkhtml_select( "course_id",$ca,MKHTML_SELECT_FIRST ) . '</td></tr>';
			$ya[0] = '- all -';
			$ya += get_yearlevel_array();
			echo '<tr><td>YearLevel</td><td>' . mkhtml_select( "year_level",$ya,MKHTML_SELECT_FIRST ) . '</td></tr>';
			echo '<tr><td>Last day of Enrollment</td><td><input type="text" name="lastday_of_enrol">(MM/DD/YYYY)</td></tr>';
			echo '<tr><td>Last day of changing</td><td><input type="text" name="lastday_of_changing">(MM/DD/YYYY)</td></tr>';
			echo '</table>';
			echo '<input type="submit" name="exec" value="Add">';
			echo '<input type="hidden" name="add">';
		} else {
			$list = new model_syinfo;
			$list->connect( auth_get_writeable() );
			if( $_REQUEST['course_id']>0 ) $_REQUEST['department_id'] = get_department_id_from_course_id($_REQUEST['course_id']);
			$ar = array(
				'sy_id' => $sy_id,
				'department_id' => $_REQUEST['department_id'],
				'lastday_of_enrol' => retrieve_date($_REQUEST["lastday_of_enrol"]),
				'lastday_of_changing' => retrieve_date($_REQUEST["lastday_of_changing"])
			);
			if( $_REQUEST['course_id']>0 ) $ar['course_id'] = $_REQUEST['course_id'];
			if( $_REQUEST['year_level']>0 ) $ar['year_level'] = $_REQUEST['year_level'];
			if( $ar['lastday_of_enrol']==0 || $ar['lastday_of_changing']==0 ) {
				echo '<div class="error">Bad date</div>';
			} else if( $list->add( $ar )==false ) {
				echo '<div class="error">' . $list->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Added successfully</div>';
			}
		}
	} else if( isset($_REQUEST["del"]) ) {
		$list = new model_syinfo;
		$list->connect( auth_get_writeable() );
		if( ! isset($_REQUEST["syinfo_id"]) ) {
			echo '<div class="error">Data not selected!</div>';
		} else if( ! isset($_REQUEST["exec"]) ) {
			$list->get_by_id( $_REQUEST["syinfo_id"] );
			$dat = $list->get_fetch_assoc(0);
			echo '<div class="prompt">Delete following data?</div>';
			echo '<table border="1">';
			echo '<tr><td>Department</td><td>' . get_department_from_department_id($dat["department_id"]) . '</td></tr>';
			echo '<tr><td>Course</td><td>' . get_department_from_course_id($dat["course_id"]) . '</td></tr>';
			echo '<tr><td>YearLevel</td><td>' . lookup_yearlevel($dat["year_level"]) . '</td></tr>';
			echo '<tr><td>Last day of Enrollment</td><td>' . mkstr_date($dat["lastday_of_enrol"]) . '</td></tr>';
			echo '<tr><td>Last day of changing</td><td>' . mkstr_date($dat["lastday_of_changing"]) . '</td></tr>';
			echo '</table>';
			echo '<input type="submit" name="exec" value="Delete">';
			echo '<input type="hidden" name="del">';
			echo '<input type="hidden" name="syinfo_id" value="' . $_REQUEST["syinfo_id"] . '">';
		} else {
			if( $list->del( $_REQUEST["syinfo_id"] )==false ) {
				echo '<div class="error">' . $list->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Deleted successfully</div>';
			}
		}
	} else {
		$list = new model_syinfo;
		$list->connect( auth_get_writeable() );

		$list->get_list( $sy_id );
		if( $list->get_numrows()==0 ) {
			$array = array(
				'sy_id' => $sy_id,
				'lastday_of_enrol' => date('Y-m-j'),
				'lastday_of_changing' => date('Y-m-j')
			);
			$list->add( $array );
			$list->get_list( $sy_id );
		}

		echo '<div class="prompt">For ' . lookup_schoolyear($sy_id) . '</div><br>';
		
		echo "<table border=\"1\">";
		echo "<tr><th>&nbsp;</th><th>Last day of Enrollment</th><th>Last day of changing</th></tr>";
		$exception_count = 0;
		for( $i=0; $i<$list->get_numrows(); $i++ ) {
			$dat = $list->get_fetch_assoc($i);
			echo '<tr>';
			echo '<td>';
			$department_id = $dat["department_id"];
			$course_id = $dat["course_id"];
			$year_level = $dat["year_level"];
			if( ($department_id>0) || ($course_id>0) || ($year_level>0) ) {
				$exception_count++;
				echo '<input type="radio" name="syinfo_id" value="' . $dat["syinfo_id"] . '">only for ';
				if( $department_id>0 ) echo get_short_department_from_department_id($department_id) . ' ';
				if( $course_id>0 ) echo get_short_course_from_course_id($course_id) . ' ';
				if( $year_level>0 ) echo lookup_yearlevel($year_level) . ' ';
			} else {
				echo '&nbsp;';
			}
			echo '</td>';
			echo '<td><input type="text" name="lastday[' . $dat["syinfo_id"] . '][0]" value="' . mkstr_date($dat["lastday_of_enrol"]) . '"> (MM/DD/YYYY) </td>';
			echo '<td><input type="text" name="lastday[' . $dat["syinfo_id"] . '][1]" value="' . mkstr_date($dat["lastday_of_changing"]) . '"> (MM/DD/YYYY) </td>';
			echo '</tr>';
		}
		echo "</table>";
		echo '<input type="submit" name="update" value="Update"><br>';
		echo '<input type="submit" name="add" value="Add department exception"><br>';
		if( $exception_count>0 ) echo '<input type="submit" name="del" value="Delete department exception">';
	}

	echo "</form>";

	print_footer();

	if( isset($_REQUEST["update"]) ) {
		echo "<form method=\"POST\" id=\"goback\">";
		echo "<input type=\"submit\" value=\"Go back\">";
		echo "</form>";
	} else if( (isset($_REQUEST["add"])) || (isset($_REQUEST["del"])) ) {
		echo '<form method="POST" id=\"goback\">';
		echo '<input type="submit" value="Go back">';
		echo '</form>';
	} else {
		echo "<form action=\"index.php\" method=\"POST\" id=\"goback\">";
		echo "<input type=\"submit\" value=\"Go back\">";
		echo "<input type=\"hidden\" name=\"sy_id\" value=\"$sy_id\">";
		echo "</form>";
	}
?>

</body>

</html>
