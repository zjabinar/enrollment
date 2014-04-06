<?php
	//session_start();
	//require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/course.inc");
	require_once("../include/teacher.inc");
	require_once("../include/class.inc");
	require_once("../include/classschedule.inc");
	require_once("../include/yearlevel.inc");
	require_once("../include/enrol_class.inc");
	require_once("../include/view_schedule.inc");
	//auth_check( $_SESSION["office"] );
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title> Class list of student </title>
<style type="text/css">
<!--
	caption{ font-weight:bold; text-align:left; }
	body{ font-family:'arial'; font-size:9pt; line-height:1;color:black; }
	table{ font-family:'arial'; font-size:9pt; line-height:1;color:black }
	table.subject { border-collapse:collapse; border-color:#669999; border-style:solid }
	table.timetable { border-collapse:collapse; border-color:#669999; border-style:solid }
	th { border-color:#669999 }
	td { border-color:#669999 }
	td.class { background-color:#d8d8d8 }
-->
</style>
</head>

<body>

<?php
	$sy_id = $_REQUEST["sy_id"];
	$str_schoolyear = lookup_schoolyear($sy_id);
	$student_id = $_REQUEST["student_id"];

	$list = new model_regist_class;
	$list->connect();
	$list->get_class_list( $sy_id,$student_id );
		
	$obj_schedule = new model_classschedule;
	$obj_schedule->connect();
	echo $str_schoolyear;

	echo '<br>';
	echo '<table border="1" class="subject">';
	echo '<caption>Subject List</caption>';
	echo '<tr><th>Subject</th><th>Code</th><th>Teacher</th><th>Course</th><th>YearLevel</th><th>Sec.</th><th>Unit</th><th>Schedule</th></tr>';
	for( $i=0; $i<$list->get_numrows(); $i++ ) {
		$dat = $list->get_fetch_assoc($i);
		if( ! isset($cache_course[$dat["course_id"]]) ) {
			$cache_course[$dat["course_id"]] = get_short_course_from_course_id($dat["course_id"]);
		}
		if( ! isset($cache_teacher[$dat["teacher_id"]]) ) {
			$cache_teacher[$dat["teacher_id"]] = lookup_teacher_name($dat["teacher_id"]);
		}
		$schedule_ar = $obj_schedule->get_schedule_array($dat["class_id"]);
		$schedule_str = '';
		for( $j=0; $j<count($schedule_ar); $j++ ) {
			$schedule_str .= $schedule_ar[$j][1] . ' ' . $schedule_ar[$j][2] . ' ' . $schedule_ar[$j][0] . '<br>';
		}
		printf( "<tr>" );
		printf( "<td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td align=\"center\">%s</td> <td><small>%s</small></td>\n",
			mkstr_neat($dat["subject"]),
			mkstr_neat($dat["subject_code"]),
			mkstr_neat($cache_teacher[$dat["teacher_id"]]),
			mkstr_neat( $dat['major_ignore'] ? strtok($cache_course[$dat["course_id"]],' ') : $cache_course[$dat["course_id"]] ),
			lookup_yearlevel($dat["year_level"]),
			mkstr_neat(lookup_section_flag($dat["section_flag"])),
			mkstr_neat($dat["unit"]),
			mkstr_neat($schedule_str)
		);
		printf( "</tr>" );
	}
	echo '</table>';

	$list_schedule = new model_classschedule;
	$list_schedule->connect();
	$list_schedule->get_list_of_student($sy_id,$student_id);
	echo '<br>';
	print_classschedule_table( $list_schedule, "Time table", false );
?>

</body>

</html>
