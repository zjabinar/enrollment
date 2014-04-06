<?php
	session_start();
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/course.inc");
	require_once("../include/yearlevel.inc");
	require_once("../include/section.inc");
	require_once("../include/student.inc");
	require_once("../include/enrol_student.inc");

//	$size_x = '7.2in';
	$size_x = '100%';
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title> STUDENTS' OFFICIAL LIST </title>
<style type="text/css">
<!--
	body{ font-family:times; font-size:9pt }
	th{ font-family:'Courier New'; font-size:9pt; font-weight:normal }
	td{ font-family:'Courier New'; font-size:9pt }

	div.RP{ font-size:16pt; line-height:16pt; text-align:center; width:<?php echo $size_x; ?>; margin:0 auto }
	div.school{ font-size:20pt; line-height:20pt; font-weight:bold; text-align:center; width:<?php echo $size_x; ?>; margin:0 auto }
	div.addr{ font-size:16pt; line-height:16pt; font-style:italic; text-align:center; width:<?php echo $size_x; ?>; margin:0 auto }

	div.dep{ text-align:center; font-size:14pt; font-weight:bold; page-break-before:always; page-break-after:avoid; margin:0 auto 1em auto }
	table.course{ text-align:center; border-style:double; border-width:medium; border-color:black; border-collapse:collapse; page-break-before:avoid; page-break-after:avoid; margin:0 auto 1em auto }
	td.course{ font-family:times; font-size:12pt; font-weight:bold }
	div.year_level{ text-align:center; font-size:12pt; font-weight:bold; page-break-before:avoid; page-break-after:avoid }
	table.student_list{ border-style:groove; border-width:thin; border-color:black; border-collapse:collapse; page-break-before:avoid }
	th.student_list{ border-style:groove; border-width:thin; background-color:#d0d0d0 }
	td.student_list{ border-style:none groove; border-width:thin; padding-left:0.4mm; padding-right:0.4mm }
	#gender{ font-style:italic }
	#major{ font-style:italic; text-decoration:underline }
-->
</style>
</head>
</head>

<body>

<?php
	$sy_id = $_REQUEST["sy_id"];
	$str_schoolyear = lookup_schoolyear($sy_id) . ' Semester';

	// Title page
	echo '<div style="height:9in; page-break-after:always; page-break-inside:avoid">';
	echo '  <div style="text-align:right; font-size:16pt">';
	echo date("M j, Y");
	echo '  </div>';
	echo '    <div class="RP">Republic of the Philippines</div>';
	echo '    <div class="school">' . $g_schoolname . '</div>';
	echo '    <div class="addr">' . $g_schooladdress . '</div>';
	echo '  <div style="position:relative; top:3in">';
	echo '    <div style="text-align:center; margin:0 auto; font-size:64pt">';
	echo 'STUDENTS\'<br>OFFICIAL LIST';
	echo '    </div>';
	echo '    <div style="text-align:center; margin:0 auto; font-size:32pt; border-style:double">';
	echo $str_schoolyear;
	echo '    </div>';
	if( $_REQUEST["department_id"]>0 ) {
		echo '<div style="text-align:center; margin:0 auto; font-size:16pt">';
		echo get_department_from_department_id($_REQUEST["department_id"]);
		echo '</div>';
	}
	echo '  </div>';
	echo '</div>';

	$course_array = get_course_array(0,0,true);
	$course_long_name = array();

	$datlist = new model_enrol_student;
	$datlist->connect();

	// Summary page
	$datlist->get_summary($sy_id,$_REQUEST["department_id"]);
	$datas = array();
	for( $i=0; $i<$datlist->get_numrows(); $i++ ) {
		$dat = $datlist->get_fetch_assoc($i);
		$department_id = $dat["department_id"];
		$course_name = $dat["short_name"];
		if( ! isset($course_long_name[$course_name]) ) $course_long_name[$course_name] = $course_array[$dat["course_id"]]["long_name"];
		if( $dat["major"]=='' ) {
			$major = '-';
		} else {
			$major = $dat["major"];
			if( $dat["minor"]!='' ) $major .= ',' . $dat["minor"];
		}
		$year_level = $dat["year_level"];
		$gender = $dat["gender"];
		$datas[$department_id][$course_name][$year_level][$major][$gender] += $dat["total"];
	}
	$total_m = 0;
	$total_f = 0;
	$total = 0;

	echo '<table border="0" align="center" style="width:' . $size_x . '">';
	echo '<tr><td colspan="5" style="background-color:#d0d0d0; text-align:center; font-family:times; font-weight:bold">';
	echo '  <span style="font-size:14pt; text-decoration:underline">SUMMARY OF Enrollment</span><br>';
	echo '  <span style="font-size:14pt; font-style:italic">' . $str_schoolyear . '</span>';
	echo '</td></tr>';
	echo '<tr><th></th><th></th><th style="text-decoration:underline">Male</th><th style="text-decoration:underline">Female</th><th style="text-decoration:underline">Total</th></tr>';
	foreach( $datas as $dep_id=>$dep_ar ) {
		foreach( $dep_ar as $course_name=>$course_ar ) {
			echo '<tr><td colspan="5"><br><b>' . $course_long_name[$course_name] . ' (' . $course_name . ')</b></td></tr>';
			foreach( $course_ar as $year_level=>$year_level_ar ) {
				$total_yl = 0;
				$total_yl_m = 0;
				$total_yl_f = 0;
				$rowcount = 0;
				foreach( $year_level_ar as $major=>$major_ar ) {
					$mftotal = 0;
					foreach( $major_ar as $val ) $mftotal += $val;
					echo '<tr>';
					if( $rowcount==0 ) {
						echo '<td style="padding-left:1em">' . ($year_level==0 ? '-' : lookup_yearlevel($year_level)) . '</td>';
					} else {
						echo '<td></td>';
					}
					echo '<td>' . $major . '</td>';
					echo '<td style="text-align:right">' . intval($major_ar["M"]) . '</td>';
					echo '<td style="text-align:right">' . intval($major_ar["F"]) . '</td>';
					echo '<td style="text-align:right">' . intval($mftotal) . '</td>';
					echo '</tr>';
					$total_yl += $mftotal;
					$total_yl_m += $major_ar["M"];
					$total_yl_f += $major_ar["F"];
					$rowcount++;
				}
				if( $rowcount>1 ) {
					echo '<tr>'
						. '<td></td>'
						. '<td align="right"><b>SUB-TOTAL&nbsp;</b></td>'
						. '<td style="text-align:right"><b>' . intval($total_yl_m) . '</b></td>'
						. '<td style="text-align:right"><b>' . intval($total_yl_f) . '</b></td>'
						. '<td style="text-align:right"><b>' . intval($total_yl) . '</b></td>'
						. '</tr>';
				}
				$total_m += $total_yl_m;
				$total_f += $total_yl_f;
				$total += $total_yl;
			}
		}
	}
	echo '<tr>'
		. '<td align="right" colspan="2"><b>GRAND-TOTAL&nbsp;</b></td>'
		. '<td style="text-align:right"><b>' . intval($total_m) . '</b></td>'
		. '<td style="text-align:right"><b>' . intval($total_f) . '</b></td>'
		. '<td style="text-align:right"><b>' . intval($total) . '</b></td>'
		. '</tr>';
	echo '</table>';

	// Student Lists
	$datlist->get_list_officially_enrolled2($sy_id,$_REQUEST["department_id"]);
	$department_id = -1;
	$course_name = -1;
	$in_table = false;
	for( $i=0; $i<$datlist->get_numrows(); $i++ ) {
		$dat = $datlist->get_fetch_assoc($i);
		if( $dat["department_id"]!=$department_id ) {
			$department_id = $dat["department_id"];
			if( $in_table ) {
				echo '</table>';
				output_gender_count($gender_count);
				$in_table = false;
			}
			echo '<div class="dep">' . get_department_from_department_id($department_id) . '</div>' . "\n";
		}
		if( $dat["short_name"]!=$course_name ) {
			$course_name = $dat["short_name"];
			if( $in_table ) {
				echo '</table>';
				output_gender_count($gender_count);
				$in_table = false;
			}
			echo '<table class="course" align="center" style="width:' . $size_x . '"><tr><td class="course">' . $course_array[$dat["course_id"]]["long_name"] . ' (' . $course_array[$dat["course_id"]]["short_name"] . ')</td></tr></table>' . "\n";
			$year_level = -1;
		}
		if( $dat["year_level"]!=$year_level ) {
			$year_level = $dat["year_level"];
			if( $in_table ) {
				output_gender_count($gender_count);
				echo '</table>';
			}
			if( $year_level>0 ) {
				echo '<div class="year_level">' . lookup_yearlevel($year_level) . '</div>' . "\n";
			}
			echo '<table class="student_list" align="center" style="width:' . $size_x . '" cellspacing="0" cellpadding="0">';
			echo '<tr><th class="student_list">ID</th><th class="student_list">LAST NAME</th><th class="student_list">FIRST NAME</th><th class="student_list">M.I.</th><th class="student_list">DATE OF BIRTH</th><th class="student_list">HOME ADDRESS</th></tr>' . "\n";
			$in_table = true;
			$gender = -1;
			$gender_count = array();
		}
		if( $dat["gender"]!=$gender ) {
			$gender = $dat["gender"];
			echo '<tr><td class="student_list" colspan="6" id="gender">' . ($gender=="M" ? "Male" : "Female") . ':</td></tr>' . "\n";
			$major = -1;
			$minor = -1;
		}
		if( ($dat["major"]!=$major) || ($dat["minor"]!=$minor) ) {
			$major = $dat["major"];
			$minor = $dat["minor"];
			if( $major!='' ) {
				echo '<tr><td class="student_list" colspan="6" id="major">Major: ' . $major;
				if( $minor!='' ) echo ' , Minor: ' . $minor;
				echo '</td></tr>' . "\n";
			}
		}
		echo '<tr>';
		echo '<td class="student_list">' . mkstr_student_id($dat["student_id"]) . '</td>';
		echo '<td class="student_list" nowrap="nowrap">' . mkstr_neat( $dat["last_name"] ) . '</td>';
		echo '<td class="student_list" nowrap="nowrap">' . mkstr_neat( $dat["first_name"] ) . '</td>';
		echo '<td class="student_list">' . mkstr_neat( get_initial($dat["middle_name"]) ) . '</td>';
		echo '<td class="student_list">' . mkstr_date( $dat["date_of_birth"] ) . '</td>';
		echo '<td class="student_list">' . mkstr_neat( $dat["home_address"] ) . '</td>';
		echo '</tr>';
		$gender_count[$gender]++;
	}
	if( $in_table ) {
		output_gender_count($gender_count);
		echo '</table>';
	}

function output_gender_count( $gender_count )
{
	echo '<table border="0" align="center" cellspacing="0" cellpadding="0" style="margin:0 auto 1em auto">';
	echo '<tr><td id="gender"> Male </td><td>&nbsp;&nbsp;-&nbsp;&nbsp;</td><td style="text-align:right">' . intval($gender_count["M"]) . '</td></tr>';
	echo '<tr><td id="gender"> Female </td><td>&nbsp;&nbsp;-&nbsp;&nbsp;</td><td style="text-align:right; text-decoration:underline">' . intval($gender_count["F"]) . '</td></tr>';
	echo '<tr><td></td><td></td><td>' . intval($gender_count["M"]+$gender_count["F"]) . '</td></tr>';
	echo '</table>';
}

?>

</body>

</html>
