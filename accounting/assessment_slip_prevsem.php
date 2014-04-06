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
	if( !isset($_REQUEST["noauth"]) ) {	// for e-web
		auth_check( $_SESSION["office"] );
	}


function hello($id,$sy)
{
	echo " asdf ;j asdfhjhlsda'f 'kjadsfjj ;ads ";
	echo $id . '<br />';
	echo $sy . '<br />';
}

function get_previous_balance($id, $sy)
{


	//$sy_id = $_REQUEST["sy_id"];
	$sy_id = $sy;
	//$student_id = $_REQUEST["student_id"];
	$student_id = $id;

	$feecategory_array = get_feecategory_array();

	$student = new model_enrol_student;
	$student->connect();
	$student->get_info($sy_id,$student_id);
	if( $student->get_numrows()==0 ) {
		//echo 'Not enrolled in ' . lookup_schoolyear($sy_id) . ' semester';
		return "0.00";
		//exit(0);
	}
	$dat = $student->get_fetch_assoc(0);

	//echo '<div style="text-align:center">' . mkstr_capitalize($g_schoolname) . '</div>';
	//echo '<div style="font-size:10pt;text-align:center;font-weight:bold">STUDENT\'S ASSESSMENT SLIP</div>';

	//echo '<table border="0" align="center" style="width:' . $size_x . '" cellspacing="0" cellpadding="0" class="top">';
	//echo '<tr>';
	//echo '<td>' . mkstr_student_id($student_id) . '</td>';
	//echo '<td>' . mkstr_name_lfm($dat["first_name"],$dat["middle_name"],$dat["last_name"]) . '</td>';
	//echo '<td align="right">' . lookup_schoolyear($dat["sy_id"]) . ($dat["sy_id"]!=$dat["sy_id_end"] ? " - " . lookup_schoolyear($dat["sy_id_end"]) : "") . ' semester</td>';        echo '</tr><tr>';
	//echo '<td colspan="2">' . get_department_from_course_id($dat["course_id"]) . '</td>';
	//echo '<td align="right">' . date("M j, Y") . '</td>';
	//echo '</tr><tr>';
	//echo '<td colspan="2">' . get_short_course_from_course_id($dat["course_id"]) . ' ' . ($dat["year_level"] > 0 ? lookup_yearlevel($dat["year_level"]) : "") . ' ' . lookup_section($dat["section"]) . '</td>';
	//echo '<td align="right">Fees base on ' . get_year_from_schoolyear($dat["feebase_sy"]) . '</td>';
	//echo '</tr>';
	$scholartype_id = get_scholar( $student_id,$dat["sy_id"] );
	if( $scholartype_id>0 ) //echo '<tr><td colspan="3">Scholarship : ' . lookup_scholartype( $scholartype_id ) . '</td></tr>';
	$guarantor_id = get_guarantor($dat["sy_id"],$student_id);
	if( $guarantor_id>0 ) //echo '<tr><td colspan="3">Guarantor : ' . lookup_teacher_name($guarantor_id) . '</td></tr>';
	if( $dat["date_dropped"] ) {
		$refund_rate = $dat["refund_rate"];
		//echo '<tr><td colspan="3">Officially dropped on ' . mkstr_date($dat["date_dropped"]) . ' : Refund rate ' . $refund_rate . '%</td></tr>';
	}
	//echo '</table>';

	$assessment = calc_assessment($sy_id,$student_id);
	$total = 0;
	
	//echo '<table border="1" cellspacing="0" cellpadding="0" align="center" style="width:' . $size_x . '" class="assessment">';
	//echo "<caption>Assessment</caption>";
	//echo '<tr><th>Title</th><th>Detail</th><th>Amount</th></tr>';
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
			//echo '<tr>';
			//echo '<td>&nbsp;' . $category_title . '</td>';
			//echo '<td>' . $detail . '</td>';
			//echo '<td class="peso">' . mkstr_peso($subtotal) . '&nbsp;</td>';
			//echo '</tr>';
			$total += $subtotal;
		}
	}
	//printf( "<tr><td></td><td></td><td></td></tr>" );
	//printf( '<tr><td>&nbsp;</td><td align="right">Total&nbsp;</td><td class="peso">%s&nbsp;</td></tr>', mkstr_peso($total) );
	//echo "</table>";

	//echo $val_tuition_fee ;

	$obj_payment = new model_payment;
	$obj_payment->connect();
	$obj_payment->get_list_group_by_category($dat["sy_id"],$student_id);
	
	$total_payment = 0;
	$total_payment_misc = 0;
	
	//echo '<br>';
	//echo '<table border="1" cellspacing="0" cellpadding="0" align="center" style="width:' . $size_x . '" class="payment">';
	//echo '<caption>Payment Record</caption>';
	//printf( '<tr><th>Date</th><th>O.R. no.</th><th>detail</th><th>Amount</th></tr>' );
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
			//printf( '<tr><td>&nbsp;%s</td><td>&nbsp;%s</td><td>%s</td><td class="peso">%s&nbsp;</td></tr>',
			//	mkstr_date($ar["date"]),
			//	$ar["orno"],
			//	$ar["detail"],
			//	mkstr_peso($ar["payment"])
			//);
		}
	}

	$balance = ($total - $total_payment);
	//printf( "<tr><td></td><td></td><td></td><td></td></tr>" );
	//printf( '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">Total&nbsp;</td><td class="peso">%s&nbsp;</td></tr>', mkstr_peso($total_payment) );
	//printf( '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">Balance&nbsp;</td><td class="peso">%s&nbsp;</td></tr>', mkstr_peso($balance) );
	//echo "hello, how are you!<br />";
	return mkstr_peso($balance);
	//echo "</table>";
	
}
?>

