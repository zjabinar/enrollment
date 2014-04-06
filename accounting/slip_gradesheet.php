<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/auth_model.inc"); 
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/course.inc");
	require_once("../include/student.inc");
	require_once("../include/teacher.inc");
	require_once("../include/class.inc");
	require_once("../include/enrol_class.inc");
	require_once("../include/grade.inc");
	auth_check( $_SESSION["office"] );
?>

<html>

<head>
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title> Grading Sheet </title>
<style type="text/css">
<!--
body{ font-size:12pt }
th{ font-weight:normal }
table{ font-size:11pt }
-->
</style>
</head>

<body>

<?php
	$size_x = "90%";

	$sy_id = $_SESSION["sy_id"];
	$class_id = $_REQUEST["class_id"];
	$str_schoolyear = lookup_schoolyear($sy_id);
	$per_page = $_REQUEST["per_page"];

	$class_obj = new model_class;
	$class_obj->connect();
	$class_obj->get_by_id( $class_id );
	$class = $class_obj->get_fetch_assoc(0);

	$graderemark_array[0] = '';
	$graderemark_array += get_graderemark_array();

	$header = '';
	$header .= '<div style="text-align:center; width:' . $size_x . '; line-height:1.2em">';
	$header .= '<span style="font-size:9pt">' . $g_schoolname . '</span><br>';
	$header .= '<span style="font-size:12pt">' . mkstr_capitalize(get_department_from_department_id($class['department_id'])) . '</span><br>';
	$header .= '<span style="font-size:9pt">' . $g_schooladdress . '</span><br>';
	$header .= '<br>';
	$header .= '<span style="font-size:14pt">Grading Sheet</span><br>';
	$header .= '<span style="font-size:12pt">' . $str_schoolyear . ' semester</span><br>';
	if( $class['course_id']>0 ) {
		$header .= '<br>';
		$header .= '<span style="font-size:12pt">' . get_course_from_course_id($class['course_id']) . '</span><br>';
	}
	$header .= '<br>';
	$header .= '</div>';

	$header .= '<table border="0" style="width:' . $size_x . '" align="center">';
	$header .= '<tr>';
	$header .= '<td style="font-size:12pt">' . $class['subject_code'] . '</td>';
	$header .= '<td style="font-size:12pt">' . $class['subject'] . '</td>';
	$header .= '<td style="font-size:12pt">' . $class['unit'] . 'units</td>';
	$header .= '<td style="font-size:12pt">' . lookup_teacher_name($class['teacher_id']) . '</td>';
	$header .= '</tr>';
	$header .= '</table>';

	$footer = '';
	$footer .= '<table border="0" style="width:' . $size_x . '" align="center">';
	$footer .= '<tr><td>';
	$footer .= 'Verified Correct:<br><br>';
	$footer .= '<center>';
	$teacherid = get_dean_id_from_department_id($class['department_id']);
	$footer .= mkstr_capitalize( lookup_teacher_name($teacherid) ) . '<br>';
	$footer .= lookup_teacher_position($teacherid) . '<br>';
	$footer .= '</center>';

	$footer .= '</td><td>';
	$footer .= 'Submitted by:<br><br>';
	$footer .= '<center>';
	$teacherid = $class['teacher_id'];
	$footer .= mkstr_capitalize( lookup_teacher_name($teacherid) ) . '<br>';
	$footer .= 'Instructor/Professor<br>';
	$footer .= '</center>';

	$footer .= '</td></tr><tr><td>';
	$footer .= 'Received by:<br><br>';
	$teacherid = lookup_teacherpos(TEACHERPOS_REGISTRAR);
	$footer .= '<center>';
	$footer .= mkstr_capitalize( lookup_teacher_name($teacherid) ) . '<br>';
	$footer .= lookup_teacher_position($teacherid) . '<br>';
	$footer .= '</center>';

	$footer .= '</td><td>';
	$footer .= 'Approved:<br><br>';
	$teacherid = lookup_teacherpos(TEACHERPOS_VPACADEMIC);
	$footer .= '<center>';
	$footer .= mkstr_capitalize( lookup_teacher_name($teacherid) ) . '<br>';
	$footer .= lookup_teacher_position($teacherid) . '<br>';
	$footer .= '</center>';

	$footer .= '</td></tr>';
	$footer .= '</table>';

	$list = new model_regist_class;
	$list->connect();
	$list->get_student_list_official($sy_id,$class_id);

	$no = 0;
	$total = $list->get_numrows();
	
	while( $no < $total ) {
		if( $no>0 ) echo '<div style="page-break-after:always"></div>';
		echo $header;

		if( $per_page<=0 ) {
			$max = $total;
		} else {
			$max = $no + $per_page;
			if( $max > $total ) $max = $total;
		}
		
		echo "<table border=\"0\" cellspacing=\"0\" style=\"width:$size_x\" align=\"center\">";
		echo "<tr><td>" . ($no+1) . " - $max of $total</td></tr>";
		echo "</table>";

		echo "<table border=\"1\" cellspacing=\"0\" style=\"width:$size_x\" align=\"center\">";
		echo "<tr>";
		echo "<th>No</th><th style=\"width:40mm\">STUDENT ID</th><th>NAME</th><th style=\"width:20mm\">MIDTERM</th><th style=\"width:20mm\">FINAL</th><th>REMARK</th>";
		echo "</tr>";
		for( ; $no<$max; $no++ ) {
			$dat = $list->get_fetch_assoc($no);
			if( ! isset($course_cache[$dat["course_id"]]) ) {
				list($dep,$course,$major,$minor) = get_short_names_from_course_id($dat["course_id"]);
				$course_cache[$dat["course_id"]] = $course . ' ' . $major . ' ' . $minor;
			}
			printf( "<tr>" );
			printf( "<td align=\"center\">%d</td> <td align=\"center\">%s</td> <td>%s</td> <td align=\"center\">%s</td> <td align=\"center\">%s</td>",
				$no+1,
				mkstr_student_id($dat["student_id"]),
				mkstr_name_lfm($dat["first_name"],$dat["middle_name"],$dat["last_name"]),
				mkstr_neat( mkstr_grade($dat["grade_midterm"]) ),
				mkstr_neat( mkstr_grade($dat["grade_final"]) )
			);
			echo '<td align="center">';
			if( $dat["grade_remark"]>0 ) {
				echo mkstr_neat( $graderemark_array[$dat["grade_remark"]] );
			} else {
				if( $dat["grade_final"]>0 ) {
					if( $dat["grade_final"] <= GRADE_PASS ) {
						echo 'Passed';
					} else {
						echo 'Failed';
					}
				} else {
					echo '&nbsp;';
				}
			}
			echo '</td>';
			printf( "</tr>" );
		}
		echo "</table>";

		echo $footer;
	}
?>

</body>

</html>
