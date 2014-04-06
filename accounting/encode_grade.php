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
	auth_check( $_SESSION["office"] );
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<script type="text/JavaScript" src="../option/option.js"></script>
<link rel="stylesheet" type="text/css" href="base.css" />
<title> Grades </title>
</head>

<body onLoad="optionOnLoad()" onResize="optionOnResize()">
<?php
	print_heading();
?>
<?php
	$sy_id = $_SESSION["sy_id"];
	$str_schoolyear = lookup_schoolyear($sy_id);
	if( isset($_SESSION["department_id"]) ) {
		$department_id = $_SESSION["department_id"];
	} else {
		$department_id = 0;
	}
	
	print_title( get_office_name($_SESSION["office"]), "Encode grades", $str_schoolyear );

	echo '<form method="POST">';

	$teacher_id = $_REQUEST["teacher_id"];

	echo '<div class="prompt">Select a teacher</div>';
	$teacher_array[0] = ' - no teacher - ';
	$teacher_array += get_teacher_array($department_id,$sy_id);
	echo mkhtml_select( "teacher_id", $teacher_array, $teacher_id );
	echo '<input type="submit" value="Go"><br>';

	echo '</form>';

	if( isset($_REQUEST["teacher_id"]) ) {
		echo '<form method="POST" action="encode_grade_sub.php" name="mainform">';
		
		$list = new model_class;
		$list->connect();
		$list->get_list_by_teacher( $sy_id,$teacher_id,$department_id );
		
		$obj_schedule = new model_classschedule;
		$obj_schedule->connect();

		echo '<br>';
		echo '<div' . ($list->get_numrows()>2 ? ' id="scrolllist"' : '') . '>';
		echo '<table border="1">';
		echo '<tr><th></th><th>Subject</th><th>SubjectCode</th><th>Course</th><th>YearLevel</th><th>Sec.</th><th>Schedule</th></tr>';
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
			printf( "<td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td><small>%s</small></td>\n",
				'<input type="radio" name="class_id" value="' . $dat["class_id"] . '">',
				mkstr_neat($dat["subject"]),
				mkstr_neat($dat["subject_code"]),
				mkstr_neat( $dat['major_ignore'] ? strtok($cache_course[$dat["course_id"]],' ') : $cache_course[$dat["course_id"]] ),
				lookup_yearlevel($dat["year_level"]),
				mkstr_neat(lookup_section_flag($dat["section_flag"])),
				mkstr_neat($schedule_str)
			);
			printf( "</tr>" );
		}
		echo '</table>';
		echo '</div>';

		echo '<input type="submit" value="Encode">';
		echo "<br><input type=\"button\" value=\"DBM Form\" onClick=\"window.open('slip_dbmform.php?teacher_id=$teacher_id&department_id=$department_id','_blank')\">";
		
		echo '</form>';
	}

	print_footer();

	echo '<form action="index.php" method="POST" id="goback">';
	echo '<input type="submit" value="Go back">';
	echo '<input type="hidden" name="sy_id" value="' . $sy_id . '">';
	echo '</form>';
?>

</body>

</html>
