<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/course.inc");
	require_once("../include/semester.inc");
	require_once("../include/student.inc");
	require_once("../include/enrol_student.inc");
	require_once("../include/assessment.inc");
	require_once("../include/payment.inc");
	require_once("../include/guarantor.inc");
	require_once("../include/teacher.inc");
	
	require_once("assessment_slip_prevsem.php");

	if( !isset($_REQUEST["noauth"]) ) {	// for e-web
		auth_check( $_SESSION["office"] );
	}
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title> Assessment Slip </title>
<style type="text/css">
<!--
<?php if( ! isset($_REQUEST["noauth"]) ) { // not e-web ?>
	body{ font-family:'arial'; font-size:9pt; line-height:1; }
	th{ font-family:'Arial'; font-size:9pt; font-weight:normal; }
	caption{ font-family:'Arial'; font-size:10pt; font-weight:bold; }
	td{ font-family:'Arial'; font-size:9pt; line-height:1; }
	td.peso{ text-align:right; }
	td.orno{ text-align:right; }
<?php } else { // for e-web ?>
	body{ font-family:'arial'; font-size:9pt; line-height:1; color:black }
	th{ font-family:'Arial'; font-size:9pt; font-weight:normal; color:black; border-color:#669999 }
	caption{ font-family:'Arial'; font-size:10pt; font-weight:bold; color:black }
	td{ font-family:'Arial'; font-size:9pt; line-height:1; color:black; border-color:#669999 }
	td.peso{ text-align:right; }
	td.orno{ text-align:right; }
	table.top {}
	table.assessment { border-color:#669999; border-style:solid; border-collapse:collapse; line-height:1 }
	table.payment { border-color:#669999; border-style:solid; border-collapse:collapse; line-height:1 }
<?php } ?>
-->
</style>
</head>

<body>

<?php
	$size_x = '4.8in';

	$sy_id = $_REQUEST["sy_id"];
	$student_id = $_REQUEST["student_id"];

	$feecategory_array = get_feecategory_array();

	$student = new model_enrol_student;
	$student->connect();
	$student->get_info($sy_id,$student_id);
	if( $student->get_numrows()==0 ) {
		echo 'Not enrolled in ' . lookup_schoolyear($sy_id) . ' semester';
		exit(0);
	}
	$dat = $student->get_fetch_assoc(0);

	echo '<div style="text-align:center">' . mkstr_capitalize($g_schoolname) . '</div>';
	echo '<div style="font-size:10pt;text-align:center;font-weight:bold">STUDENT\'S ASSESSMENT SLIP</div>';

	echo '<table border="0" align="center" style="width:' . $size_x . '" cellspacing="0" cellpadding="0" class="top">';
	echo '<tr>';
	echo '<td>' . mkstr_student_id($student_id) . '</td>';
	echo '<td>' . mkstr_name_lfm($dat["first_name"],$dat["middle_name"],$dat["last_name"]) . '</td>';
	echo '<td align="right">' . lookup_schoolyear($dat["sy_id"]) . ($dat["sy_id"]!=$dat["sy_id_end"] ? " - " . lookup_schoolyear($dat["sy_id_end"]) : "") . ' semester</td>';        echo '</tr><tr>';
	echo '<td colspan="2">' . get_department_from_course_id($dat["course_id"]) . '</td>';
	echo '<td align="right">' . date("M j, Y") . '</td>';
	echo '</tr><tr>';
	echo '<td colspan="2">' . get_short_course_from_course_id($dat["course_id"]) . ' ' . ($dat["year_level"] > 0 ? lookup_yearlevel($dat["year_level"]) : "") . ' ' . lookup_section($dat["section"]) . '</td>';
	echo '<td align="right">Fees base on ' . get_year_from_schoolyear($dat["feebase_sy"]) . '</td>';
	echo '</tr>';
	$scholartype_id = get_scholar( $student_id,$dat["sy_id"] );
	if( $scholartype_id>0 ) echo '<tr><td colspan="3">Scholarship : ' . lookup_scholartype( $scholartype_id ) . '</td></tr>';
	$guarantor_id = get_guarantor($dat["sy_id"],$student_id);
	if( $guarantor_id>0 ) echo '<tr><td colspan="3">Guarantor : ' . lookup_teacher_name($guarantor_id) . '</td></tr>';
	if( $dat["date_dropped"] ) {
		$refund_rate = $dat["refund_rate"];
		echo '<tr><td colspan="3">Officially dropped on ' . mkstr_date($dat["date_dropped"]) . ' : Refund rate ' . $refund_rate . '%</td></tr>';
	}
	echo '</table>';

	$assessment = calc_assessment($sy_id,$student_id);
	$total = 0;
	
	echo '<table border="1" cellspacing="0" cellpadding="0" align="center" style="width:' . $size_x . '" class="assessment">';
	echo "<caption>Assessment</caption>";
	echo '<tr><th>Title</th><th>Detail</th><th>Amount</th></tr>';
	foreach( $feecategory_array as $category_id=>$category_title ) {
		$detail = '<table border="0" width="100%" cellspacing="0" cellpadding="0">';
		$subtotal = 0;
		$subcount = 0;
		foreach( $assessment as $ar ) {
			if( $ar["feecategory_id"]==$category_id ) {
				for( $i=0; $i<count($ar["detail"]); $i++ ) {
					$detail .= '<tr><td>&nbsp;' . $ar["detail"][$i]["desc"] . ' :</td><td align="right">' . mkstr_peso($ar["detail"][$i]["amount"]) . '&nbsp;</td></tr>';
					if ($ar["feecategory_id"]==2) {
						//$val_tuition_fee=$i["detail"];
						//$detail .= '<tr><td> ' . $ar ["feecategory_id"] . mkstr_peso($ar["detail"][$i]["amount"]). '</td></tr>';
						$val_tuition_fee .= mkstr_peso($ar["detail"][$i]["amount"]);
					}
				}
				$subtotal += $ar["amount"];
				$subcount++;
			}
		}
		$detail .= '</table>';
		if( $subcount > 0 ) {
			echo '<tr>';
			echo '<td>&nbsp;' . $category_title . '</td>';
			echo '<td>' . $detail . '</td>';
			echo '<td class="peso">' . mkstr_peso($subtotal) . '&nbsp;</td>';
			echo '</tr>';
			$total += $subtotal;
		}
	}
	printf( "<tr><td></td><td></td><td></td></tr>" );
	printf( '<tr><td>&nbsp;</td><td align="right">Total&nbsp;</td><td class="peso">%s&nbsp;</td></tr>', mkstr_peso($total) );
	echo "</table>";

	//echo $val_tuition_fee ;

	$obj_payment = new model_payment;
	$obj_payment->connect();
	$obj_payment->get_list_group_by_category($dat["sy_id"],$student_id);
	
	$total_payment = 0;
	$total_payment_misc = 0;
	
	echo '<br>';
	echo '<table border="1" cellspacing="0" cellpadding="0" align="center" style="width:' . $size_x . '" class="payment">';
	echo '<caption>Payment Record</caption>';
	printf( '<tr><th>Date</th><th>O.R. no.</th><th>detail</th><th>Amount</th></tr>' );
	for( $i=0; $i<$obj_payment->get_numrows(); $i++ ) {
		$ar = $obj_payment->get_fetch_assoc($i);
		$id = $ar["date"] . $ar["orno"];
		$paymentlist[$id]["date"] = $ar["date"];
		$paymentlist[$id]["orno"] = $ar["orno"];
		$paymentlist[$id]["detail"] .= '&nbsp;' . $feecategory_array[$ar["feecategory_id"]] . ' : ' . mkstr_peso($ar["payment"]) . '<br>';
		$paymentlist[$id]["payment"] += $ar["payment"];
		$total_payment += $ar["payment"];
	}
	if( count($paymentlist)>0 ) {
		foreach( $paymentlist as $ar ) {
			printf( '<tr><td>&nbsp;%s</td><td>&nbsp;%s</td><td>%s</td><td class="peso">%s&nbsp;</td></tr>',
				mkstr_date($ar["date"]),
				$ar["orno"],
				$ar["detail"],
				mkstr_peso($ar["payment"])
			);
		}
	}

	$balance = ($total - $total_payment);
	printf( "<tr><td></td><td></td><td></td><td></td></tr>" );
	printf( '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">Total&nbsp;</td><td class="peso">%s&nbsp;</td></tr>', mkstr_peso($total_payment) );
	printf( '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">Balance&nbsp;</td><td class="peso">%s&nbsp;</td></tr>', mkstr_peso($balance) );
 	//printf( '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">&nbsp;</td><td class="peso">&nbsp;</td></tr>');
	//printf( '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">Previous Balance</td><td class="peso">&nbsp;</td></tr>');
	
	$val_sem = substr($sy_id, -1); 
	$val_year = substr($sy_id,0,4);
	$temp_sem1 = 0;
	$temp_sem2 = 0;
	$temp_sem3 = 0;
	$temp_sem4 = 0;
	$temp_sem5 = 0;
	$temp_sem6 = 0;
	$temp_sem7 = 0;
	$temp_sem8 = 0;
	$temp_sem9 = 0;
	$temp_sem10 = 0;
	$temp_sem11= 0;
	$temp_sem12= 0;
	$temp_sem13 = 0;
	$temp_sem14= 0;
	$temp_sem15= 0;
	$temp_sem16 = 0;
	$temp_sem17= 0;
	$temp_sem18= 0;
	
	$temp_year1 = 0;
	$temp_year2 = 0;
	$temp_year3 = 0;	
	$temp_year4 = 0;
	$temp_year5 = 0;
	$temp_year6 = 0;
	$temp_year7 = 0;
	$temp_year8 = 0;
	$temp_year9 = 0;
	$temp_year10 = 0;
	$temp_year11 = 0;
	$temp_year12 = 0;
	$temp_year13 = 0;
	$temp_year14 = 0;
	$temp_year15 = 0;
	$temp_year16 = 0;
	$temp_year17 = 0;
	$temp_year18 = 0;


	//less 1 sem
	if ($val_sem==3) {
		$temp_sem1 = 2;
		$temp_year1 = $val_year;
	}elseif ($val_sem==2) {
		$temp_sem1 = 1;
		$temp_year1 = $val_year;
	}elseif ($val_sem ==1) {
		$temp_sem1 = 3;
		$temp_year1 = $val_year - 1;
	}

	//lsess 2 sem
	if ($val_sem==3) {
                $temp_sem2 = 1;
                $temp_year2 = $val_year;
        }elseif ($val_sem==2) {
                $temp_sem2 = 3;
                $temp_year2 = $val_year - 1;
        }elseif ($val_sem ==1) {
                $temp_sem2 = 2;
                $temp_year2 = $val_year - 1;
        }


	//less 3 sem
	if ($val_sem==3) {
	        $temp_sem3 = 3;
                $temp_year3 = $val_year - 1;
        }elseif ($val_sem==2) {
                $temp_sem3 = 2;
                $temp_year3 = $val_year - 1;
        }elseif ($val_sem ==1) {
                $temp_sem3 = 1;
                $temp_year3 = $val_year - 1;
        }


	//less 4 sem
        if ($val_sem==3) {
                $temp_sem4 = 2;
                $temp_year4 = $val_year - 1;
        }elseif ($val_sem==2) {
                $temp_sem4 = 1;
                $temp_year4 = $val_year - 1;
        }elseif ($val_sem ==1) {
                $temp_sem4 = 3;
                $temp_year4 = $val_year - 2;
        }


	//less 5 sem
        if ($val_sem==3) {
                $temp_sem5 = 1;
                $temp_year5 = $val_year - 1;
        }elseif ($val_sem==2) {
                $temp_sem5 = 3;
                $temp_year5 = $val_year - 2;
        }elseif ($val_sem ==1) {
                $temp_sem5 = 2;
                $temp_year5 = $val_year - 2;
        }


	//less 6 sem
        if ($val_sem==3) {
                $temp_sem6 = 3;
                $temp_year6 = $val_year - 2;
        }elseif ($val_sem==2) {
                $temp_sem6 = 2;
                $temp_year6 = $val_year - 2;
        }elseif ($val_sem ==1) {
                $temp_sem6 = 1;
                $temp_year6 = $val_year - 2;
        }
        
        
        //less 7 sem
        if ($val_sem==3) {
                $temp_sem7 = 2;
                $temp_year7 = $val_year - 2;
        }elseif ($val_sem==2) {
                $temp_sem7 = 1;
                $temp_year7 = $val_year - 2;
        }elseif ($val_sem ==1) {
                $temp_sem7 = 3;
                $temp_year7 = $val_year - 3;
        }


	//less 8 sem
        if ($val_sem==3) {
                $temp_sem8 = 1;
                $temp_year8 = $val_year - 2;
        }elseif ($val_sem==2) {
                $temp_sem8 = 3;
                $temp_year8 = $val_year - 3;
        }elseif ($val_sem ==1) {
                $temp_sem8 = 2;
                $temp_year8 = $val_year - 3;
        }


	//less 9 sem
        if ($val_sem==3) {
                $temp_sem9 = 3;
                $temp_year9 = $val_year - 3;
        }elseif ($val_sem==2) {
                $temp_sem9 = 2;
                $temp_year9 = $val_year - 3;
        }elseif ($val_sem ==1) {
                $temp_sem9 = 1;
                $temp_year9 = $val_year - 3;
        }
        
        //less 10 sem
        if ($val_sem==3) {
                $temp_sem10 = 2;
                $temp_year10 = $val_year - 3;
        }elseif ($val_sem==2) {
                $temp_sem10 = 1;
                $temp_year10 = $val_year - 3;
        }elseif ($val_sem ==1) {
                $temp_sem10 = 3;
                $temp_year10 = $val_year - 4;
        }


	//less 11 sem
        if ($val_sem==3) {
                $temp_sem11 = 1;
                $temp_year11 = $val_year - 3;
        }elseif ($val_sem==2) {
                $temp_sem11 = 3;
                $temp_year11 = $val_year - 4;
        }elseif ($val_sem ==1) {
                $temp_sem11 = 2;
                $temp_year11 = $val_year - 4;
        }


	//less 12 sem
        if ($val_sem==3) {
                $temp_sem12 = 3;
                $temp_year12 = $val_year - 4;
        }elseif ($val_sem==2) {
                $temp_sem12 = 2;
                $temp_year12 = $val_year - 4;
        }elseif ($val_sem ==1) {
                $temp_sem12 = 1;
                $temp_year12 = $val_year - 4;
        }
        
        //less 13 sem
        if ($val_sem==3) {
                $temp_sem13 = 2;
                $temp_year13 = $val_year - 4;
        }elseif ($val_sem==2) {
                $temp_sem13 = 1;
                $temp_year13 = $val_year - 4;
        }elseif ($val_sem ==1) {
                $temp_sem13 = 3;
                $temp_year13 = $val_year - 5;
        }


	//less 14 sem
        if ($val_sem==3) {
                $temp_sem14 = 1;
                $temp_year14 = $val_year - 4;
        }elseif ($val_sem==2) {
                $temp_sem14 = 3;
                $temp_year14 = $val_year - 5;
        }elseif ($val_sem ==1) {
                $temp_sem14 = 2;
                $temp_year14 = $val_year - 5;
        }


	//less 15 sem
        if ($val_sem==3) {
                $temp_sem15 = 3;
                $temp_year15 = $val_year - 5;
        }elseif ($val_sem==2) {
                $temp_sem15 = 2;
                $temp_year15 = $val_year - 5;
        }elseif ($val_sem ==1) {
                $temp_sem15 = 1;
                $temp_year15 = $val_year - 5;
        }
        
	//less 16 sem
        if ($val_sem==3) {
                $temp_sem16 = 2;
                $temp_year16 = $val_year - 5;
        }elseif ($val_sem==2) {
                $temp_sem16 = 1;
                $temp_year16 = $val_year - 5;
        }elseif ($val_sem ==1) {
                $temp_sem16 = 3;
                $temp_year16 = $val_year - 6;
        }


	//less 17 sem
        if ($val_sem==3) {
                $temp_sem17 = 1;
                $temp_year17 = $val_year - 5;
        }elseif ($val_sem==2) {
                $temp_sem17 = 3;
                $temp_year17 = $val_year - 6;
        }elseif ($val_sem ==1) {
                $temp_sem17 = 2;
                $temp_year17 = $val_year - 6;
        }


	//less 18 sem
        if ($val_sem==3) {
                $temp_sem18 = 3;
                $temp_year18 = $val_year - 6;
        }elseif ($val_sem==2) {
                $temp_sem18 = 2;
                $temp_year18 = $val_year - 6;
        }elseif ($val_sem ==1) {
                $temp_sem18 = 1;
                $temp_year18 = $val_year - 6;
        }
	
	$sy1 = $temp_year1 . $temp_sem1;
	$sy2 = $temp_year2 . $temp_sem2;
	$sy3 = $temp_year3 . $temp_sem3;
	$sy4 = $temp_year4 . $temp_sem4;
	$sy5 = $temp_year5 . $temp_sem5;
	$sy6 = $temp_year6 . $temp_sem6;
	
	$sy7 = $temp_year7 . $temp_sem7;
	$sy8 = $temp_year8 . $temp_sem8;
	$sy9 = $temp_year9 . $temp_sem9;
	$sy10 = $temp_year10 . $temp_sem10;
	$sy11 = $temp_year11 . $temp_sem11;
	$sy12 = $temp_year12 . $temp_sem12;
	
	$sy13 = $temp_year13 . $temp_sem13;
	$sy14 = $temp_year14 . $temp_sem14;
	$sy15 = $temp_year15 . $temp_sem15;
	$sy16 = $temp_year16 . $temp_sem16;
	$sy17 = $temp_year17 . $temp_sem17;
	$sy18 = $temp_year18 . $temp_sem18;


	$val1 = get_previous_balance($student_id, $sy1);
	$val2 = get_previous_balance($student_id, $sy2);
	$val3 = get_previous_balance($student_id, $sy3);
	$val4 = get_previous_balance($student_id, $sy4);
	$val5 = get_previous_balance($student_id, $sy5);
	$val6 = get_previous_balance($student_id, $sy6);
	$val7 = get_previous_balance($student_id, $sy7);
	$val8 = get_previous_balance($student_id, $sy8);
	$val9 = get_previous_balance($student_id, $sy9);
	$val10 = get_previous_balance($student_id, $sy10);
	$val11 = get_previous_balance($student_id, $sy11);
	$val12 = get_previous_balance($student_id, $sy12);
	$val13 = get_previous_balance($student_id, $sy13);
	$val14 = get_previous_balance($student_id, $sy14);
	$val15 = get_previous_balance($student_id, $sy15);
	$val16 = get_previous_balance($student_id, $sy16);
	$val17 = get_previous_balance($student_id, $sy17);
	$val18 = get_previous_balance($student_id, $sy18);

	$val_total = 0;
	$val_total = $val1 + $val2 + $val3 + $val4 + $val5 + $val6 + $val7 + $val8 + $val9 + $val10 + $val11 + $val12 + $val13 + $val14 + $val15 + $val16 + $val17 + $val18;
	if ($val_total>0) {
		printf( '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">&nbsp;</td><td class="peso">&nbsp;</td></tr>');
		printf( '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">Previous Balance</td><td class="peso">&nbsp;</td></tr>');
	}	

	if ($val1<>"0.00" or $val1<>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year1 . " - " . chk_sem($temp_sem1) . '</td><td class="peso">&nbsp;' . $val1 . '</td></tr>';
	}elseif ($val1>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year1 . " - " . chk_sem($temp_sem1) . '</td><td class="peso">&nbsp;' . $val1 . '</td></tr>';
	}
	if ($val2<>"0.00" or $val2<>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year2 . " - " . chk_sem($temp_sem2) . '</td><td class="peso">&nbsp;' . $val2 . '</td></tr>';
	}elseif ($val2>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year2 . " - " . chk_sem($temp_sem2) . '</td><td class="peso">&nbsp;' . $val2 . '</td></tr>';
	}
	if ($val3<>"0.00" or $val3<>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year3 . " - " . chk_sem($temp_sem3) . '</td><td class="peso">&nbsp;' . $val3 . '</td></tr>';
	}elseif ($val3>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year3 . " - " .  chk_sem($temp_sem3) . '</td><td class="peso">&nbsp;' . $val3 . '</td></tr>';
	}
	
	if ($val4<>"0.00" or $val4<>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year4 . " - " . chk_sem($temp_sem4) . '</td><td class="peso">&nbsp;' . $val4 . '</td></tr>';
	}elseif ($val4>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year4 . " - " . chk_sem($temp_sem4) . '</td><td class="peso">&nbsp;' . $val4 . '</td></tr>';
	}

	if ($val5<>"0.00" or $val5<>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year5 . " - " . chk_sem($temp_sem5) . '</td><td class="peso">&nbsp;' . $val5 . '</td></tr>';
	}elseif ($val5>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year5 . " - " . chk_sem($temp_sem5) . '</td><td class="peso">&nbsp;' . $val5 . '</td></tr>';
	}

	if ($val6>"0.00" or $val6>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year6 . " - " . chk_sem($temp_sem6) . '</td><td class="peso">&nbsp;' . $val6 . '</td></tr>';
	}elseif ($val6>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year6 . " - " . chk_sem($temp_sem6) . '</td><td class="peso">&nbsp;' . $val6 . '</td></tr>';
	}
	
	
		if ($val7<>"0.00" or $val7<>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year7 . " - " . chk_sem($temp_sem7) . '</td><td class="peso">&nbsp;' . $val7 . '</td></tr>';
	}elseif ($val7>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year7 . " - " . chk_sem($temp_sem7) . '</td><td class="peso">&nbsp;' . $val7 . '</td></tr>';
	}
	if ($val8<>"0.00" or $val8<>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year8 . " - " . chk_sem($temp_sem8) . '</td><td class="peso">&nbsp;' . $val8 . '</td></tr>';
	}elseif ($val8>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year8 . " - " . chk_sem($temp_sem8) . '</td><td class="peso">&nbsp;' . $val8 . '</td></tr>';
	}
	if ($val9<>"0.00" or $val9<>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year9 . " - " . chk_sem($temp_sem9) . '</td><td class="peso">&nbsp;' . $val9 . '</td></tr>';
	}elseif ($val9>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year9 . " - " .  chk_sem($temp_sem9) . '</td><td class="peso">&nbsp;' . $val9 . '</td></tr>';
	}
	
	if ($val10<>"0.00" or $val10<>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year10 . " - " . chk_sem($temp_sem10) . '</td><td class="peso">&nbsp;' . $val10 . '</td></tr>';
	}elseif ($val10>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year10 . " - " . chk_sem($temp_sem10) . '</td><td class="peso">&nbsp;' . $val10 . '</td></tr>';
	}

	if ($val11<>"0.00" or $val11<>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year11 . " - " . chk_sem($temp_sem11) . '</td><td class="peso">&nbsp;' . $val11 . '</td></tr>';
	}elseif ($val11>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year11 . " - " . chk_sem($temp_sem11) . '</td><td class="peso">&nbsp;' . $val11 . '</td></tr>';
	}

	if ($val12>"0.00" or $val12>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year12 . " - " . chk_sem($temp_sem12) . '</td><td class="peso">&nbsp;' . $val12 . '</td></tr>';
	}elseif ($val12>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year12 . " - " . chk_sem($temp_sem12) . '</td><td class="peso">&nbsp;' . $val12 . '</td></tr>';
	}
	
	
	
	if ($val13<>"0.00" or $val13<>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year13 . " - " . chk_sem($temp_sem13) . '</td><td class="peso">&nbsp;' . $val13 . '</td></tr>';
	}elseif ($val13>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year13 . " - " . chk_sem($temp_sem13) . '</td><td class="peso">&nbsp;' . $val13 . '</td></tr>';
	}
	if ($val14<>"0.00" or $val14<>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year14 . " - " . chk_sem($temp_sem14) . '</td><td class="peso">&nbsp;' . $val14 . '</td></tr>';
	}elseif ($val14>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year14 . " - " . chk_sem($temp_sem14) . '</td><td class="peso">&nbsp;' . $val14 . '</td></tr>';
	}
	if ($val15<>"0.00" or $val15<>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year15 . " - " . chk_sem($temp_sem15) . '</td><td class="peso">&nbsp;' . $val15 . '</td></tr>';
	}elseif ($val15>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year15 . " - " .  chk_sem($temp_sem15) . '</td><td class="peso">&nbsp;' . $val15 . '</td></tr>';
	}
	
	if ($val16<>"0.00" or $val16<>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year16 . " - " . chk_sem($temp_sem16) . '</td><td class="peso">&nbsp;' . $val16 . '</td></tr>';
	}elseif ($val16>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year16 . " - " . chk_sem($temp_sem16) . '</td><td class="peso">&nbsp;' . $val16 . '</td></tr>';
	}

	if ($val17<>"0.00" or $val17<>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year17 . " - " . chk_sem($temp_sem17) . '</td><td class="peso">&nbsp;' . $val17 . '</td></tr>';
	}elseif ($val17>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year17 . " - " . chk_sem($temp_sem17) . '</td><td class="peso">&nbsp;' . $val17 . '</td></tr>';
	}

	if ($val18>"0.00" or $val18>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year18 . " - " . chk_sem($temp_sem18) . '</td><td class="peso">&nbsp;' . $val18 . '</td></tr>';
	}elseif ($val18>0) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year18 . " - " . chk_sem($temp_sem18) . '</td><td class="peso">&nbsp;' . $val18 . '</td></tr>';
	}
	
	echo "</table>";

	function chk_sem($sem){
		if ($sem==1) {
			return "1st Sem. ";
		}elseif ($sem==2) {
			return "2nd Sem. ";
		}elseif ($sem==3) {
			return "Summer ";
		}else{
			return $sem;
		}
	}
	

?>

</body>

</html>
