<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/course.inc");
	require_once("../include/teacher.inc");
	require_once("../include/class.inc");
	require_once("../include/classschedule.inc");
	require_once("../include/yearlevel.inc");
	require_once("../include/view_schedule.inc");
	auth_check( $_SESSION["office"] );
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="base.css" />
<title> Class list of teachers </title>
<style type="text/css">
<!--
	caption{ font-weight:bold; text-align:left; }
-->
</style>
</head>

<body>
<?php
	print_heading();
?>
<?php
	$sy_id = $_SESSION["sy_id"];
	$str_schoolyear = lookup_schoolyear($sy_id);
	if( isset($_SESSION["department_id"]) ) {
		$department_id = $_SESSION["department_id"];
	} else {
	}
	
	print_title( get_office_name($_SESSION["office"]), "Schedule of teachers", $str_schoolyear );

	echo '<form method="POST">';

	$teacher_id = $_REQUEST["teacher_id"];

	echo '<div class="prompt">Select a teacher</div>';
	$teacher_array[0] = ' - no teacher - ';
	$teacher_array += get_teacher_array($department_id,$sy_id);
	echo mkhtml_select( "teacher_id", $teacher_array, $teacher_id );
	echo '<input type="submit" value="View"><br>';

	if( isset($_REQUEST["teacher_id"]) ) {
		$list = new model_class;
		$list->connect();
		if( $teacher_id==0 ) {
			// If no teacher, lets get the list only from the department
			$list->get_list_by_teacher( $sy_id,$teacher_id,$department_id );
		} else {
			$list->get_list_by_teacher( $sy_id,$teacher_id );
		}
		
		$obj_schedule = new model_classschedule;
		$obj_schedule->connect();

		echo '<br>';
		echo '<table border="1">';
		echo '<caption>Subject List</caption>';
		echo '<tr><th>Subject</th><th>SubjectCode</th><th>Course</th><th>YearLevel</th><th>Sec.</th><th>Schedule</th><th>StudentList</th></tr>';
		for( $i=0; $i<$list->get_numrows(); $i++ ) {
			$dat = $list->get_fetch_assoc($i);
			if( ! isset($cache_course[$dat["course_id"]]) ) {
				$cache_course[$dat["course_id"]] = get_short_course_from_course_id($dat["course_id"]);
			}
			$schedule_ar = $obj_schedule->get_schedule_array($dat["class_id"]);
			$schedule_str = '';
			for( $j=0; $j<count($schedule_ar); $j++ ) {
				$schedule_str .= $schedule_ar[$j][1] . ' ' . $schedule_ar[$j][2] . ' ' . $schedule_ar[$j][0] . '<br>';
			}
			printf( "<tr>" );
			printf( "<td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td><small>%s</small></td> <td>%s</td>\n",
				mkstr_neat($dat["subject"]),
				mkstr_neat($dat["subject_code"]),
				mkstr_neat( $dat['major_ignore'] ? strtok($cache_course[$dat["course_id"]],' ') : $cache_course[$dat["course_id"]] ),
				lookup_yearlevel($dat["year_level"]),
				mkstr_neat(lookup_section_flag($dat["section_flag"])),
				mkstr_neat($schedule_str),
				'<input type="button" value="check" onClick="' . "window.open('list_classstudent.php?sy_id=$sy_id&class_id=" . $dat["class_id"]. "','_blank')" . '">' .
				'<input type="button" value="Slip" onClick="' . "window.open('slip_classstudent.php?sy_id=$sy_id&class_id=" . $dat["class_id"]. "','_blank')" . '">'
			);
			printf( "</tr>" );
		}
		echo '</table>';

		$list_schedule = new model_classschedule;
		$list_schedule->connect();
		$list_schedule->get_list_of_teacher($sy_id,$teacher_id);
		list($total_reg,$total_ireg) = calc_classschedule_total_time( $list_schedule );
		echo 'Total regular load ' . ($total_reg/60) . ' hours.<br>Total extra load ' . ($total_ireg/60) . ' hours.<br>';

		echo '<br>';
		print_classschedule_table( $list_schedule, "Time table" );

		if( $department_id>0 ) {
			echo "<input type=\"button\" value=\"Faculty Load Slip (" . get_department_from_department_id($department_id) . ")\" onclick=\"window.open('slip_facultyload.php?sy_id=$sy_id&department_id=$department_id&teacher_id=$teacher_id','_blank');\"><br>";
		}
		echo "<input type=\"button\" value=\"Faculty Load Slip\" onclick=\"window.open('slip_facultyload.php?sy_id=$sy_id&teacher_id=$teacher_id','_blank');\">";
	}

	echo '</form>';

	print_footer();

	echo '<form action="index.php" method="POST" id="goback">';
	echo '<input type="submit" value="Go back">';
	echo '<input type="hidden" name="sy_id" value="' . $sy_id . '">';
	echo '</form>';
?>

</body>

</html>
