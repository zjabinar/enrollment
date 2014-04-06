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
	require_once("../include/guarantor.inc");
	auth_check( $_SESSION["office"] );
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title> Enrollment Form </title>
<style type="text/css">
<!--
	body{ font-family:'courier'; font-size:3mm; white-space:pre }
	th{ font-weight:normal; }
	caption{ font-weight:bold; }
	td{ font-family:'courier'; font-size:3mm }
	th{ font-family:'courier'; font-size:3mm; border-style:solid; border-width:0.2mm 0.2mm 0.2mm 0 }
	th.box_left{ border-style:solid; border-width:0.2mm 0.2mm 0.2mm 0.2mm };
	td.box{ border-style:solid; border-width:0 0.2mm 0.2mm 0 };
	td.box_left{ border-style:solid; border-width:0 0.2mm 0.2mm 0.2mm };
	td.box_span{ border-style:solid; border-width:0 0.2mm 0 0 };
	td.box_left_span{ border-style:solid; border-width:0 0.2mm 0 0.2mm };
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


	$feecategory_array = get_feecategory_array();   
    $assessment = calc_assessment($sy_id,$student_id);

	foreach( $feecategory_array as $category_id=>$category_title ) {
	    foreach( $assessment as $ar ) {
			if( $ar["feecategory_id"]==$category_id ) {
				for( $i=0; $i<count($ar["detail"]); $i++ ) {
					if ($ar["feecategory_id"]==2) {
						$val_tuition_fee_peso .= mkstr_peso($ar["detail"][$i]["amount"]);
						$val_tuition_fee .= $ar["detail"][$i]["amount"];
					}
				}
			}
		} 
	}
	echo $val_tuition_fee;
	echo '<br>' . $sy_id;
	echo '<br>' . $student_id;
	$size_x = '7.4in';

	$output = '';
	
	$output .= '<div style="position:absolute; top:0.8cm; left:0cm; width:' . $size_x . '; text-align:center">' . get_department_from_department_id($department_id) . '</div>';

	$output .= '<div style="position:absolute; top:2.0cm; left:1.7cm">' . mkstr_student_id($student_id) . '</div>';
	$output .= '<div style="position:absolute; top:2.0cm; left:5.0cm">' . mkstr_name_lfm($dat["first_name"],$dat["middle_name"],$dat["last_name"]) . '</div>';
	$output .= '<div style="position:absolute; top:2.0cm; left:0cm; width:' . $size_x . '; text-align:right">' . lookup_schoolyear($sy_id). ' semester</div>';
	$output .= '<div style="position:absolute; top:2.5cm; left:1.7cm">' . get_short_course_from_course_id($dat["course_id"]) . '</div>';
	$output .= '<div style="position:absolute; top:2.5cm; left:12.4cm">' . lookup_yearlevel($dat["year_level"]) . ' ' . lookup_section($dat["section"]) . '</div>';
	$output .= '<div style="position:absolute; top:2.5cm; left:0cm; width:' . $size_x . '; text-align:right">' . date("M j, Y") . '</div>';

	$list = new model_regist_class;
	$list->connect();
	$list->get_class_list($sy_id,$student_id);

	$obj_schedule = new model_classschedule;
	$obj_schedule->connect();

	$y = 39;		// 39mm
	$height = 3;	// 3mm
	$total_units = 0;
	for( $i=0; $i<$list->get_numrows(); $i++ ) {
		$dat = $list->get_fetch_assoc($i);
		$total_units += $dat['unit'];
		$schedule_ar = $obj_schedule->get_schedule_array($dat["class_id"]);
		if( count($schedule_ar)==0 ) $schedule_ar = array( array('&nbsp;','&nbsp;','') );
		$schedule_count = count($schedule_ar);
		for( $count=0; $count<$schedule_count; $count++ ) {
			$output .= '<div style="position:absolute; top:' . $y . 'mm; left:1mm">' . mkstr_neat($schedule_ar[$count][1]) . '</div>';
			$output .= '<div style="position:absolute; top:' . $y . 'mm; left:21mm">' . mkstr_neat($schedule_ar[$count][2]) . '</div>';
			$output .= '<div style="position:absolute; top:' . $y . 'mm; left:61mm">' . mkstr_neat($schedule_ar[$count][0]) . '</div>';
			if( $count==0 ) {
				$tmp_y = $y + $height * ($schedule_count-1) / 2;
				$output .= '<div style="position:absolute; top:' . $tmp_y . 'mm; left:85mm">' . $dat["subject_code"] . '</div>';
				$output .= '<div style="position:absolute; top:' . $tmp_y . 'mm; left:125mm">' . mkstr_neat($dat["unit"]) . '</div>';
				list($t,$f,$m,$l) = lookup_teacher_name_array($dat["teacher_id"]);
				$output .= '<div style="position:absolute; top:' . $tmp_y . 'mm; left:133mm">' . mkstr_neat( mkstr_name_fimil($f,$m,$l) ) . '</div>';
			}
			$y += $height;
		}
		$y += 1;
	}
	$output .= '<div style="position:absolute; top:' . $y . 'mm; left:125mm; text-decoration:overline">' . mkstr_neat($total_units) . '</div>';

	$assessment = calc_assessment($sy_id,$student_id);
	foreach( $assessment as $idx=>$val ) {
		$total += $val["amount"];
	}
	
	$payment = new model_payment;
	$payment->connect();
	$total_paid = $payment->get_payment_of($sy_id,$student_id);

	$scholartype_id = get_scholar( $student_id,$sy_id );
	if( $scholartype_id>0 ) $desc .= ' Scholarship : ' . lookup_scholartype( $scholartype_id );
	$guarantor_id = get_guarantor($sy_id,$student_id);
	if( $guarantor_id>0 ) $desc .= ' Guarantor : ' . lookup_teacher_name($guarantor_id);
	
	$output .= '<div style="position:absolute; top:10.6cm; left:1.5cm">P' . mkstr_peso($total) . $desc . '</div>';
	$output .= '<div style="position:absolute; top:10.6cm; left:4.4cm">' . 'note: * All miscellaneous should be paid upon enrollment.' .'</div>';
	$output .= '<div style="position:absolute; top:10.9cm; left:4.4cm">' . '      * Tuition fees may be paid on installment basis.' .'</div>';
	$val_misc = $total - $val_tuition_fee;
	$output .= '<div style="position:absolute; top:9.5cm; left:0.0cm">' . 'Miscellaneous: ' . mkstr_peso($val_misc) . '</div>';
	$output .= '<div style="position:absolute; top:9.8cm; left:0.0cm">' . 'Tuition Fee:   ' . mkstr_peso($val_tuition_fee) .'</div>';
	
	$output .= '<div style="position:absolute; top:10.6cm; left:16.2cm">P' . mkstr_peso($total_paid) . '</div>';
	
	$output .= '<div style="position:absolute; top:11.7cm; left:1.8cm">' . mkstr_capitalize( auth_get_fullname() ) . '</div>';	

	$dean_id = get_dean_id_from_department_id($department_id);
	$dean = lookup_teacher_name($dean_id);
	$dean_pos = lookup_teacher_position($dean_id);
	$output .= '<div style="position:absolute; top:11.7cm; left:0cm; width:' . $size_x . '; text-align:center">' . mkstr_capitalize($dean) . '<br>' . $dean_pos . '</div>';

	$registrar_id = lookup_teacherpos(TEACHERPOS_REGISTRAR);
	$registrar = lookup_teacher_name($registrar_id);
	$registrar_pos = lookup_teacher_position($registrar_id);
	
	$output .= '<div style="position:absolute; top:11.7cm; left:5.2in; width:2.5in;">';
	$output .= '<center>' . mkstr_capitalize($registrar) . '<br>' . $registrar_pos . '</center>';
	$output .= '</div>';
	
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
