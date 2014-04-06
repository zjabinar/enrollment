<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/course.inc");
	require_once("../include/teacher.inc");
	require_once("../include/class.inc");
	require_once("../include/classschedule.inc");
	require_once("../include/enrol_class.inc");
	//auth_check( $_SESSION["office"] );
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title> Faculty Load Slip </title>
<style type="text/css">
<!--
	caption{ font-weight:bold; text-align:left; }
	body{ font-family:'Courier New'; font-size:9pt }
	caption{ font-weight:bold; }
	th{ font-family:'Courier New'; font-size:9pt; font-weight:normal }
	td{ font-family:'Courier New'; font-size:9pt }
	#list{ border-style:groove; border-width:thin }
-->
</style>
</head>

<body>

<?php
	//$sy_id = $_SESSION["sy_id"];
	$sy_id = $_REQUEST["sy_id"];
	if( isset($_REQUEST["department_id"]) ) {
		$department_id = $_REQUEST["department_id"];
	}
	$teacher_id = $_REQUEST["teacher_id"];

	//$size_x = '7.7in';
	$size_x = '100%';

	echo '<div style="text-align:center">' . mkstr_capitalize($g_schoolname) . '</div>';
	echo '<div style="text-align:center;font-weight:bold;font-size:11pt">FACULTY LOAD' . (isset($department_id) ? ' - ' . get_department_from_department_id($department_id) : '') . '</div>';
	echo '<br>';

	echo '<table border="0" align="center" style="width:' . $size_x . '" cellspacing="0" cellpadding="0"><tr>';
	echo '<td>' . mkstr_teacher_id($teacher_id) . '</td>';
	echo '<td align="right">' . lookup_schoolyear($sy_id). ' semester</td>';
	echo '</tr><tr>';
	echo '<td>' . lookup_teacher_name($teacher_id) . '</td>';
	echo '<td align="right">' . date("M j, Y") . '</td>';
	echo '</tr>';
	echo '</table>';
	echo '<br>';

	$list = new model_class;
	$list->connect();
	$list->get_list_by_teacher( $sy_id,$teacher_id,$department_id );

	$obj_schedule = new model_classschedule;
	$obj_schedule->connect();

	$obj_regist = new model_regist_class;
	$obj_regist->connect();

	echo '<table align="center" style="width:' . $size_x . '; border-style:groove; border-width:thin; border-collapse:collapse" cellspacing="0" cellpadding="0">';
	echo '<tr><th id="list">Code</th><th id="list">Subject</th><th id="list">Schedule</th><th id="list">Room</th><th id="list">StudentNo</th></tr>';
	for( $i=0; $i<$list->get_numrows(); $i++ ) {
		$dat = $list->get_fetch_assoc($i);
		$schedule_ar = $obj_schedule->get_schedule_array($dat["class_id"]);
		$n = max( 1, count($schedule_ar) );
		for( $j=0; $j<$n; $j++ ) {
			$style = ' style="border-style:groove; border-width:thin"';
			if( $n > 1 ) {
				if( $j==0 ) {
					$style = ' style="border-style:groove groove none groove; border-width:thin"';
				} else if( $j==($n-1) ) {
					$style = ' style="border-style:none groove groove groove; border-width:thin"';
				} else {
					$style = ' style="border-style:none groove none groove; border-width:thin"';
				}
			}
			echo '<tr>';
			if( $j==0 ) {
				echo '<td id="list" rowspan="' . $n . '">' . mkstr_neat($dat["subject_code"]) . '</td>';
				echo '<td id="list" rowspan="' . $n . '">' . mkstr_neat($dat["subject"]) . '</td>';
			}
			echo '<td' . $style . '>' . $schedule_ar[$j][1] . '&nbsp;' . $schedule_ar[$j][2] . '</td>';
			echo '<td' . $style . '>' . mkstr_neat($schedule_ar[$j][0]) . '</td>';
			if( $j==0 ) {
				echo '<td id="list" rowspan="' . $n . '" align="center">' . $obj_regist->get_student_count_officially($sy_id,$dat["class_id"]) . '</td>';
			}
			echo '</tr>';
		}
	}
	echo '</table>';

	if( ! isset($department_id) ) {
		$department_id = lookup_teacher_department_id($teacher_id);
	}
	$dean_id = get_dean_id_from_department_id($department_id);
	$dean = lookup_teacher_name($dean_id);
	$dean_pos = lookup_teacher_position($dean_id);

	$vpacad_id = lookup_teacherpos(TEACHERPOS_VPACADEMIC);
	$vpacad = lookup_teacher_name($vpacad_id);
	$vpacad_pos = lookup_teacher_position($vpacad_id);

	$president_id = lookup_teacherpos(TEACHERPOS_PRESIDENT);
	$president = lookup_teacher_name($president_id);
	$president_pos = lookup_teacher_position($president_id);

	echo '<br>';
	echo '<table border="0" align="center" style="width:' . $size_x . '" cellspacing="0" cellpadding="0">';
	echo '<tr>';
	echo '  <td align="center" width="33%">' . mkstr_capitalize($dean) . '<br>' . $dean_pos . '</td>';
	echo '  <td align="center" width="34%">' . mkstr_capitalize($vpacad)  . '<br>' . $vpacad_pos . '</td>';
	echo '  <td align="center" width="33%">' . mkstr_capitalize($president) . '<br>' . $president_pos . '</td>';
	echo '</tr>';
	echo '</table>';
?>

</body>

</html>
