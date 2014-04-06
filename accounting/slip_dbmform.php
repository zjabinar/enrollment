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
	require_once("../include/grade.inc");
	require_once("../include/school.inc");
	auth_check( $_SESSION["office"] );
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title> Faculty Teaching Load </title>
<style type="text/css">
<!--
	caption{ font-weight:bold; text-align:left; }
	body{ font-family:'courier'; font-size:10pt }
	caption{ font-weight:bold; }
	th{ padding:0 1; margin:0; font-family:'courier'; font-size:10pt; font-weight:normal }
	td{ padding:0 1; margin:0; font-family:'courier'; font-size:10pt }
	#list{ border-style:groove; border-width:thin }
	#list2{ border-style:none groove; border-width:thin }
-->
</style>
</head>

<body>

<?php
	$size_x = '96%';

	$sy_id = $_SESSION["sy_id"];
	if( $_REQUEST["department_id"]>0 ) {
		$department_id = $_REQUEST["department_id"];
	}
	$teacher_id = $_REQUEST["teacher_id"];

	$obj_teacher = new model_teacher;
	$obj_teacher->connect();
	$obj_teacher->get_by_id($teacher_id);
	$teacher = $obj_teacher->get_fetch_assoc(0);

	$school_id_flag = 0;

	$list = new model_class;
	$list->connect();
	$list->get_list_by_teacher( $sy_id,$teacher_id,$department_id );
	$school_id_cache = array();
	$course_cache = array();
	for( $i=0; $i<$list->get_numrows(); $i++ ) {
		$dat = $list->get_fetch_assoc($i);
		$subjects[] = $dat;
		if( !isset($school_id_cache[$dat['course_id']]) ) {
			$school_id_cache[$dat['course_id']] = get_school_id_from_course_id($dat['course_id']);
		}
		if( !isset($course_cache[$dat['course_id']]) ) {
			$ar = get_short_names_from_course_id($dat['course_id']);
			$course_cache[$dat['course_id']] = $ar[1];
		}
		$school_id_flag |= (0x01<<$school_id_cache[$dat['course_id']]);
	}

	echo '<div style="text-align:center;font-weight:bold;font-size:11pt">FACULTY TEACHING LOAD</div>';
	if( isset($department_id) ) {
		echo '<div style="text-align:center;font-weight:normal;font-size:11pt">' . get_department_from_department_id($department_id) . '</div>';
	}
	echo '<div style="text-align:center;font-size:10pt">' . lookup_schoolyear($sy_id). ' semester</div>';
	echo '<br>';

	echo '<table border="0" style="width:' . $size_x . '">';
	echo '<tr><td align="left" valign="top">';
	echo '  (DBM Form No. SUC-14)<br><br>';
	echo '  TEACHING LOAD<br>';
	echo '  <table border="0" align="center">';
	echo '  <tr><td>DOCTORAL</td><td>: ' . ($school_id_flag&(0x01<<SCHOOLID_POSTGRADUATE) ? 'x' : '') . '</td></tr>';
	echo '  <tr><td>MASTERAL</td><td>: ' . ($school_id_flag&(0x01<<SCHOOLID_MASTERS) ? 'x' : '') . '</td></tr>';
	echo '  <tr><td>COLLEGE</td><td>: ' . ($school_id_flag&(0x01<<SCHOOLID_UNDERGRADUATE) ? 'x' : '') . '</td></tr>';
	echo '  <tr><td>HIGH SCHOOL</td><td>: ' . ($school_id_flag&(0x01<<SCHOOLID_HIGHSCHOOL) ? 'x' : '') . '</td></tr>';
	echo '  </table>';
	echo '</td><td align="right" valign="top">';
	echo '  <table border="0">';
	echo '  <tr><td>Name of Faculty</td><td colspan="2">: ' . mkstr_name_fml($teacher['first_name'],$teacher['middle_name'],$teacher['last_name']) . '</td></tr>';
	echo '  <tr><td>Rank</td><td>: ' . $teacher['rank'] . '</td><td>Age : ' . calculate_age(strtotime($teacher['date_of_birth'])) . '</td></tr>';
	echo '  <tr><td>Doctoral Degree</td><td colspan="2">: ' . $teacher['doctor_degree'] . '</td></tr>';
	echo '  <tr><td>Masteral Degree</td><td colspan="2">: ' . $teacher['master_degree'] . '</td></tr>';
	echo '  <tr><td>Baccalaureate Degree</td><td colspan="2">: ' . $teacher['bachelor_degree'] . '</td></tr>';
	echo '  </table>';
	echo '</td></tr>';
	echo '</table>';
	echo '<br>';

	$obj_schedule = new model_classschedule;
	$obj_schedule->connect();

	$obj_regist = new model_regist_class;
	$obj_regist->connect();

	echo '<table align="center" style="width:' . $size_x . '; border-style:groove; border-width:thin; border-collapse:collapse" cellspacing="0" cellpadding="0">';
	echo '<tr><th id="list">SUBJECTS</th><th id="list">COURSE</th><th id="list">UNITS</th><th id="list">CONTACT HOURS PER WEEK</th><th id="list">NO. OF STUDENTS ENROLLED</th><th id="list">DROPPED</th><th id="list">CONDITIONAL</th><th id="list">FAILED</th><th id="list">PASSED</th></tr>';
	foreach( $subjects as $dat ) {
		$class_id = $dat['class_id'];
		
		$len = 0;
		$obj_schedule->get_list($dat["class_id"]);
		for( $i=0; $i<$obj_schedule->get_numrows(); $i++ ) {
			$sch = $obj_schedule->get_fetch_assoc($i);
			$len += $sch['time_end'] - $sch['time_st'];
		}
		
		$count_stdt = 0;
		$count_drop = 0;
		$count_cond = 0;
		$count_fail = 0;
		$count_pass = 0;
		$obj_regist->get_student_list_official($sy_id,$class_id);
		for( $i=0; $i<$obj_regist->get_numrows(); $i++ ) {
			$reg = $obj_regist->get_fetch_assoc($i);
			$count_stdt++;
			switch( $reg['grade_remark'] ) {
			case GRADEREMARK_DROPPED:		$count_drop++;	break;
			case GRADEREMARK_INCOMPLETE:	$count_cond++;	break;
			case GRADEREMARK_ONPROGRESS:	$count_cond++;	break;
			case GRADEREMARK_NOGRADE:		break;
			default:
				if( $reg['grade_final']=='' ) {
					$count_cond++;
				} else if( $reg['grade_final'] <= GRADE_PASS ) {
					$count_pass++;
				} else {
					$count_fail++;
				}
				break;
			}
		}
		echo '<tr>';
		echo '<td id="list2">' . $dat['subject_code'] . '</td>';
		echo '<td id="list2">' . $course_cache[$dat['course_id']] . '</td>';
		echo '<td id="list2" align="center">' . $dat['unit'] . '</td>';
		echo '<td id="list2" align="center">' . ($len/60) . '</td>';
		echo '<td id="list2" align="center">' . $count_stdt . '</td>';
		echo '<td id="list2" align="center">' . $count_drop . '</td>';
		echo '<td id="list2" align="center">' . $count_cond . '</td>';
		echo '<td id="list2" align="center">' . $count_fail . '</td>';
		echo '<td id="list2" align="center">' . $count_pass . '</td>';
		echo '</tr>';
	}
	echo '</table>';

	echo '<br>';
	echo '<table border="0" align="center" style="width:' . $size_x . '" cellspacing="0" cellpadding="0">';
	echo '<tr>';
	echo '  <td></td>';
	echo '  <td align="center" width="33%">' . mkstr_capitalize($teacher['title'] . ' ' . mkstr_name_fml($teacher['first_name'],$teacher['middle_name'],$teacher['last_name'])) . '<br>' . 'Instructor/Professor' . '</td>';
	echo '</tr>';
	echo '</table>';
?>

</body>

</html>
