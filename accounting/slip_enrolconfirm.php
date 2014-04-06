<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/auth_model.inc");
	require_once("../include/util.inc");
	require_once("../include/course.inc");
	require_once("../include/semester.inc");
	require_once("../include/teacher.inc");
	require_once("../include/student.inc");
	require_once("../include/enrol_student.inc");
	require_once("../include/enrol_class.inc");
	require_once("../include/classschedule.inc");
	require_once("../include/class.inc");
	require_once("../include/feeelement.inc");
	require_once("../include/assessment.inc");
	auth_check( $_SESSION["office"] );
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title> Enrollment Form </title>
<meta http-equiv="Content-Style-Type" content="text/css">
<style type="text/css">
<!--
	body{ font-family:'Courier New'; font-size:9pt }
	caption{ font-weight:bold; }
	table{ table-collapse:collapse }
	td{ font-family:'Courier New'; font-size:9pt }
	th{ font-weight:normal; font-family:'Courier New'; font-size:9pt; border-style:solid solid solid none; border-width:thin }
	th.box_left{ border-style:solid; border-width:thin }
	td.box{ border-style:none solid solid none; border-width:thin }
	td.box_left{ border-style:none solid solid solid; border-width:thin }
	td.box_span{ border-style:none solid none none; border-width:thin }
	td.box_left_span{ border-style:none solid none solid; border-width:thin }
	td.peso{ text-align:right; }
	td.peso{ text-align:right; }
	td.orno{ text-align:right; }
-->
</style>
</head>

<body>

<?php
	$sy_id = $_REQUEST["sy_id"];
	$student_id = $_REQUEST["student_id"];
	$copy = $_REQUEST["cp"];
	if( $copy<=0 ) $copy = 1;

	$student = new model_enrol_student;
	$student->connect();
	$student->get_info($sy_id,$student_id);
	$dat = $student->get_fetch_assoc(0);
	$department_id = get_department_id_from_course_id($dat["course_id"]);

	$size_x = '7.7in';

	$output = '';
	
	$output .= '<div style="text-align:center">' . mkstr_capitalize($g_schoolname) . ' - ' . get_department_from_department_id($department_id) . '</div>';
	$output .= '<div style="text-align:center;font-weight:bold;font-size:11pt">Enrollment FORM</div>';
	$output .= '<br>';

	$output .= '<table border="0" align="center" style="width:' . $size_x . '" cellspacing="0" cellpadding="0"><tr>';
	$output .= '<td>' . mkstr_student_id($student_id) . '</td>';
	$output .= '<td>' . mkstr_name_lfm($dat["first_name"],$dat["middle_name"],$dat["last_name"]) . '</td>';
	$output .= '<td align="right">' . lookup_schoolyear($sy_id). ' semester</td>';
	$output .= '</tr><tr>';
	$output .= '<td colspan="2">' . get_short_course_from_course_id($dat["course_id"]) . ' ' . lookup_yearlevel($dat["year_level"]) . ' ' . lookup_section($dat["section"]) . '</td>';
	$output .= '<td align="right">' . date("M j, Y") . '</td>';
	$output .= '</tr>';
	$output .= '</table>';
	$output .= '<br>';

	$list = new model_regist_class;
	$list->connect();
	$list->get_class_list($sy_id,$student_id);

	$obj_schedule = new model_classschedule;
	$obj_schedule->connect();

	$output .= '<table border="1" cellspacing="0" cellpadding="0" align="center" style="width:' . $size_x . '">';
	$output .= '<tr><th class="box_left">DAY</th><th>TIME</th><th>ROOM</th><th>SUBJECT CODE</th><th>UNITS</th><th>INSTRUCTOR</th><th>SIGNATURE</th></tr>';
	for( $i=0; $i<$list->get_numrows(); $i++ ) {
		$dat = $list->get_fetch_assoc($i);
		$schedule_ar = $obj_schedule->get_schedule_array($dat["class_id"]);
		if( count($schedule_ar)==0 ) $schedule_ar = array( array('&nbsp;','&nbsp;','') );
		$schedule_count = count($schedule_ar);
		for( $count=0; $count<$schedule_count; $count++ ) {
			if( $count<$schedule_count-1 ) $span = '_span';
			else $span = '';
			$output .= '<tr>';
			$output .= '<td nowrap="nowrap" class="box_left' . $span . '" style="padding-left:1mm;padding-right:1mm">' . mkstr_neat($schedule_ar[$count][1]) . '</td>';
			$output .= '<td nowrap="nowrap" class="box' . $span . '" style="padding-left:1mm;padding-right:1mm">' . mkstr_neat($schedule_ar[$count][2]) . '</td>';
			$output .= '<td nowrap="nowrap" class="box' . $span . '" style="padding-left:1mm;padding-right:1mm">' . mkstr_neat($schedule_ar[$count][0]) . '</td>';
			if( $count==0 ) {
				if( $schedule_count>1 ) {
					$rowspan_str = ' rowspan="' . $schedule_count . '"';
				} else {
					$rowspan_str = '';
				}
				$output .= '<td' . $rowspan_str . ' class="box" style="padding-left:1mm;padding-right:1mm">' . $dat["subject_code"] . '</td>';
				$output .= '<td' . $rowspan_str . ' class="box" align="center">' . mkstr_neat($dat["unit"]) . '</td>';
				$output .= '<td' . $rowspan_str . ' class="box" style="padding-left:1mm;padding-right:1mm">' . mkstr_neat( lookup_teacher_name($dat["teacher_id"]) ) . '</td>';
				$output .= '<td' . $rowspan_str . ' class="box">&nbsp;</td>';
			}
			$output .= '</tr>';
		}
	}
	$output .= '</table>';
	
	$output .= '<br>';
	$output .= '<table border="0" cellspacing="0" cellpadding="0" align="center" style="width:' . $size_x . '">';
	$output .= '<tr>';
	$output .= '<td align="left" style="width:2.5in">';
	$assessment = calc_assessment($sy_id,$student_id);
	foreach( $assessment as $idx=>$val ) {
		$total += $val["amount"];
		if( $val["feecategory_id"]==FEECATEGORY_TUITION ) $total_tuition += $val["amount"];
	}
	$output .= mkstr_capitalize( auth_get_fullname() ) . '<br>';
	$output .= 'Total due : P' . mkstr_peso($total) . '<br>';
	$output .= '(Tuition P' . mkstr_peso($total_tuition) . ')';
	$output .= '</td>';
	$output .= '<td align="center">';
	$dean_id = get_dean_id_from_department_id($department_id);
	$dean = lookup_teacher_name($dean_id);
	$dean_pos = lookup_teacher_position($dean_id);
	$registrar_id = lookup_teacherpos(TEACHERPOS_REGISTRAR);
	$registrar = lookup_teacher_name($registrar_id);
	$registrar_pos = lookup_teacher_position($registrar_id);
	$output .= '<center>' . mkstr_capitalize($dean) . '<br>' . $dean_pos . '</center>';
	$output .= '</td>';
	$output .= '<td align="right" style="width:2.5in">';
	$output .= '<center>' . mkstr_capitalize($registrar) . '<br>' . $registrar_pos . '</center>';
	$output .= '</td></tr>';
	$output .= '</table>';

	$y = 0;
	for( $i=0; $i<$copy; $i++ ) {
		if( $i!=0 ) {
			echo '<div style="position:absolute; top:' . ($y+$_COOKIE["opt_printer_y"]) . 'mm;left:' . ($_COOKIE["opt_printer_x"]) . 'mm;border-style:dashed;border-width:1 0 0 0">&nbsp;&nbsp;</div>';
			echo '<div style="position:absolute; top:' . ($y+$_COOKIE["opt_printer_y"]) . 'mm;left:' . (8.25*25.4+$_COOKIE["opt_printer_x"]) . 'mm;border-style:dashed;border-width:1 0 0 0">&nbsp;&nbsp;</div>';
		}
		echo '<div style="position:absolute; top:' . ($y+0.1*25.4+$_COOKIE["opt_printer_y"]) . 'mm;left:' . (0.25*25.4+$_COOKIE["opt_printer_x"]) . 'mm">';
		echo $output;
		echo '</div>';
		$y += 5.5 * 25.4;
	}
?>

</body>

</html>
