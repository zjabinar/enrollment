<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/auth_model.inc");
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/student.inc");
	require_once("../include/course.inc");
	require_once("../include/teacher.inc");
	require_once("../include/class.inc");
	require_once("../include/classschedule.inc");
	require_once("../include/yearlevel.inc");
	require_once("../include/enrol_student.inc");
	require_once("../include/enrol_class.inc");
	require_once("../include/grade.inc");
	auth_check( $_SESSION["office"] );
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title> Grades </title>
<style type="text/css">
<!--
	caption{ font-weight:bold; text-align:left; }
	body{ font-family:'arial'; font-size:9pt; line-height:1;color:black; }
	table{ font-family:'arial'; font-size:9pt; line-height:1;color:black; border-collapse:collapse }
	th { font-size:9pt; border-style:solid; border-width:thin; font-weight:normal }
	td { font-size:9pt; border-style:solid; border-width:thin }
-->
</style>
</head>

<body>

<?php
	$size_x = '4in';

	$sy_id = $_REQUEST["sy_id"];
	$str_schoolyear = lookup_schoolyear($sy_id);
	$student_id = $_REQUEST["student_id"];

	$obj_stdt = new model_enrol_student;
	$obj_stdt->connect();
	$obj_stdt->get_info($sy_id,$student_id);
	$student = $obj_stdt->get_fetch_assoc(0);

	$remarkarray = get_graderemark_array();
	
	$list = new model_regist_class;
	$list->connect();
	$list->get_class_list( $sy_id,$student_id );
	
	$sum_grade = 0;
	$sum_unit = 0;
	$no_average = true;
	
	echo '<div style="text-align:center; width:' . $size_x . '; font-size:9pt">' . $g_schoolname . '</div>';
	echo '<div style="text-align:center; width:' . $size_x . '; font-size:9pt">' . $g_schooladdress . '</div>';
	echo '<br>';
	echo '<div style="text-align:center; width:' . $size_x . '; font-size:9pt">STUDENT\'S REPORT CARD</div>';
	echo '<br>';
	echo mkstr_student_id($student_id) . ' &nbsp; ' . mkstr_name_fml($student['first_name'],$student['middle_name'],$student['last_name']) . '<br>';
	echo get_short_course_from_course_id($student['course_id']) . ' ' . lookup_yearlevel($student['year_level'],false) . ' ' . lookup_section($student['section']) . '<br>';
	echo $str_schoolyear . ' semester<br>';
	echo '<br>';
	echo '<table style="width:' . $size_x . '">';
	echo "<tr><th colspan=\"2\">Subject/Description</th><th>Final Rating</th><th>Units</th></tr>";
	for( $i=0; $i<$list->get_numrows(); $i++ ) {
		$dat = $list->get_fetch_assoc($i);
		printf( "<tr>" );
		printf( "<td style=\"border-style:solid none solid solid\">%s</td> <td style=\"border-style:solid solid solid none\">%s</td>",
			mkstr_neat($dat["subject_code"]),
			mkstr_neat($dat["subject"])
		);
		echo '<td align="center">';
		if( $dat['regist_flag'] & REGISTFLAG_GRADECONFIRM ) {
			if( $dat['grade_remark']>0 ) {
				echo $remarkarray[$dat['grade_remark']];
				$no_average = true;
			} else {
				echo mkstr_grade($dat['grade_final']);
			}
			if( $dat['grade_final']!='' ) {
				$sum_grade += $dat['grade_final'];
				$sum_unit += $dat['unit'];
			}
		} else {
			echo "&nbsp;";
			$no_average = true;
		}
		echo '<td align="center">' . mkstr_neat($dat["unit"]) . '</td>';
		printf( "</tr>" );
	}
	echo '</table>';
	if( ($sum_unit > 0) && ($no_average==false) ) {
		echo 'Average : ' . mkstr_gradeaverage( $sum_grade/$sum_unit );
	}

	echo '<table style="width:' . $size_x . '">';
	echo '<tr><td width="50%" style="border-style:none">';
	echo 'Prepared by:<br><br>';
	echo ' &nbsp; ' . mkstr_capitalize( auth_get_fullname() );	
	echo '</td><td style="border-style:none">';
	$registrar_id = lookup_teacherpos(TEACHERPOS_REGISTRAR);
	$registrar = lookup_teacher_name($registrar_id);
	$registrar_pos = lookup_teacher_position($registrar_id);
	echo 'Certified correct:<br><br>';
	echo '<center>' . mkstr_capitalize($registrar) . '<br>' . $registrar_pos . '</center>';
	echo '</td></tr>';
	echo '</table>';
?>

</body>

</html>
