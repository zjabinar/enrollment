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
	require_once("../include/grade.inc");
	//auth_check( $_SESSION["office"] );
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title> Grades </title>
<style type="text/css">
<!--
	caption{ font-weight:bold; text-align:left; }
	body{ font-family:'arial'; font-size:9pt; line-height:1;color:black; }
	table{ font-family:'arial'; font-size:9pt; line-height:1;color:black }
	th { border-color:#669999 };
	td { border-color:#669999 };
-->
</style>
</head>

<body>

<?php
	$sy_id = $_REQUEST["sy_id"];
	$str_schoolyear = lookup_schoolyear($sy_id);
	$student_id = $_REQUEST["student_id"];

	$remarkarray = get_graderemark_array();
	
	$list = new model_regist_class;
	$list->connect();
	$list->get_class_list( $sy_id,$student_id );
	
	$sum_grade = 0;
	$sum_unit = 0;
	
	echo $str_schoolyear;
	echo '<br>';
	echo '<table border="1" class="subject">';
	echo "<tr><th>Subject</th><th>Code</th><th>Course</th><th>YearLevel</th><th>Teacher</th><th>Units</th><th>Mid Grade</th><th>Final Grade</th><th>Grade Remark</th></tr>";
	for( $i=0; $i<$list->get_numrows(); $i++ ) {
		$dat = $list->get_fetch_assoc($i);
		if( ! isset($cache_course[$dat["course_id"]]) ) {
			$cache_course[$dat["course_id"]] = get_short_course_from_course_id($dat["course_id"]);
		}
		if( ! isset($cache_teacher[$dat["teacher_id"]]) ) {
			$cache_teacher[$dat["teacher_id"]] = lookup_teacher_name($dat["teacher_id"]);
		}
		printf( "<tr>" );
		printf( "<td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td align=\"center\">%s</td>",
			mkstr_neat($dat["subject"]),
			mkstr_neat($dat["subject_code"]),
			mkstr_neat( $dat['major_ignore'] ? strtok($cache_course[$dat["course_id"]],' ') : $cache_course[$dat["course_id"]] ),
			lookup_yearlevel($dat["year_level"]),
			mkstr_neat($cache_teacher[$dat["teacher_id"]]),
			mkstr_neat($dat["unit"]) . ($dat["exempt"]>0 ? "*" : "")
		);
		if( $dat['regist_flag'] & REGISTFLAG_GRADECONFIRM ) {
			printf( "<td align=\"center\">%s</td> <td align=\"center\">%s</td> <td align=\"center\">%s</td>\n",
				mkstr_neat( mkstr_grade($dat['grade_midterm']) ),
				mkstr_neat( mkstr_grade($dat['grade_final']) ),
				mkstr_neat( $remarkarray[$dat['grade_remark']] )
			);
			if( $dat['grade_final']!='' ) {
				$sum_grade += $dat['grade_final'];
				$sum_unit += $dat['unit'];
			}
		} else {
			echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>\n";
		}
		printf( "</tr>" );
	}
	echo '</table>';
	if( $sum_unit > 0 ) {
		echo 'Average : ' . mkstr_gradeaverage( $sum_grade/$sum_unit );
	}
?>

</body>

</html>
