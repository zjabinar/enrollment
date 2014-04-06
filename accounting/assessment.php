<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/class.inc");
	require_once("../include/semester.inc");
	require_once("../include/course.inc");
	require_once("../include/student.inc");
	require_once("../include/teacher.inc");
	require_once("../include/payment.inc");
	require_once("../include/assessment.inc");
	require_once("../include/guarantor.inc");
	require_once("../include/additionalfee.inc");
	require_once("../include/blockstudent.inc");
	auth_check( $_SESSION["office"] );

define( 'MODE_ASSESSMENT', 1 );
define( 'MODE_PAYMENTRECORD', 2 );
define( 'MODE_COMPULSORY', 3 );
define( 'MODE_OPTIONAL', 4 );
define( 'MODE_GUARANTOR', 5 );
define( 'MODE_ADDITIONAL', 6 );
$modelist[MODE_ASSESSMENT] = 'Assessment';
$modelist[MODE_PAYMENTRECORD] = 'PaymentRecord';
if( $_SESSION["office"]==AUTH_CASHIER ) {
	$modelist[MODE_COMPULSORY] = 'CompulsoryPayment';
	$modelist[MODE_OPTIONAL] = 'OptionalPayment';
	$modelist[MODE_GUARANTOR] = 'Guarantor';
} else if( $_SESSION["office"]==AUTH_ACCOUNTING ) {
	$modelist[MODE_ADDITIONAL] = 'AdditionalFee';
}

function print_tab( $mode )
{
	global $modelist;
	foreach( $modelist as $val ) {
		if( $val==$mode ) {
			printf( '<div style="border-style:inset;float:left"><input type="button" value="%s"></div>', $val );
		} else {
			printf( '<div style="border-style:outset;float:left;font-weight:lighter"><input type="submit" name="mode_chg" value="%s"></div>', $val );
		}
	}
	echo '<div style="clear:left"></div>';
}
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<script type="text/JavaScript" src="../option/option.js"></script>
<?php
if( $_SESSION["office"]==AUTH_CASHIER ) {
	echo '<script type="text/JavaScript" src="cashier.js"></script>';
}
?>
<link rel="stylesheet" type="text/css" href="base.css" />
<title> Assessment </title>
<style type="text/css">
th{margin:0; padding:0}
td{margin:0; padding:0}
</style>
</head>

<body onLoad="optionOnLoad()" onResize="optionOnResize()">
<?php
	print_heading();
?>
<?php
	$sy_id = $_SESSION["sy_id"];
	$str_schoolyear = lookup_schoolyear($sy_id);

	print_title( get_office_name($_SESSION["office"]), ($_SESSION["office"]==AUTH_ACCOUNTING ? "Assessment" : "Payment"), $str_schoolyear );

	echo '<form method="POST" name="mainform">';
	
	if( (! isset($_REQUEST['student_id'])) || (isset($_REQUEST['search'])) ) {
		if( isset($_REQUEST["search_str"]) ) {
			$search_str = $_REQUEST['search_str'];
			$enrol_student_only = $_REQUEST["enrol_student_only"];
		} else if( $_SESSION["office"]==AUTH_CASHIER ) {
			$enrol_student_only = false;
		} else {
			$enrol_student_only = true;
		}
		
		echo '<div class="prompt">Enter student ID or last name to search</div>';
		echo "<input type=\"text\" name=\"search_str\" value=\"$search_str\">";
		echo " <input type=\"submit\" name=\"search\" value=\"Search\"><br>";
		echo "<input type=\"checkbox\" name=\"enrol_student_only\" value=\"1\"" . ($enrol_student_only ? " checked" : "") . ">Enrolled students only<br>";
		echo '<script type="text/javascript">document.mainform.search_str.focus();</script>';
		echo '<br>';

		$result = false;
		if( $enrol_student_only ) {
			$list = new model_enrol_student;
		} else {
			$list = new model_student;
		}
		if( is_numeric($search_str) ) {
			$list->connect();
			$result = $list->search_by_id( $search_str,$sy_id );
		} else if( isset($search_str) ) {
			$list->connect();
			$result = $list->search_by_lastname( $search_str,$sy_id );
		}
		if( $result != false ) {
			$guarantor = new model_guarantor();
			$guarantor->connect();
			echo '<div' . ($list->get_numrows()>2 ? ' id="scrolllist"' : '') . '>';
			echo "<table border=\"1\" cellpadding=\"3\">";
			echo "<tr>";
			echo "<th>&nbsp;</th>";
			echo "<td>StudentID</td><th>Name</th><th>Department</th><th>Course</th><th>Guarantor</th>";
			echo "</tr>\n";
			for( $i=0; $i<$list->get_numrows(); $i++ ) {
				$dat = $list->get_fetch_assoc($i);
				if( isset($course_cache[$dat["course_id"]]) ) {
					list( $dep,$course,$major ) = $course_cache[$dat["course_id"]];
				} else {
					$course_cache[$dat["course_id"]] = list($dep,$course,$major) = get_short_names_from_course_id($dat["course_id"]);
				}
				$guarantor->get_by_id( (isset($dat["sy_id"]) ? $dat["sy_id"] : $sy_id),$dat["student_id"] );
				$guarantor_dat = $guarantor->get_fetch_assoc(0);
				printf( "<tr>" );
				printf( "<td><input type=\"radio\" name=\"student_id\" value=\"%s\"%s></td>", $dat["student_id"], ($i==0 && ($list->get_numrows()==1)) ? " checked" : "" );
				printf( "<td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td>\n",
					mkstr_student_id($dat["student_id"]),
					mkstr_name_lfm($dat["first_name"],$dat["middle_name"],$dat["last_name"]),
					mkstr_neat( $dep ),
					mkstr_neat( $course ) . ' ' . mkstr_neat( $major ),
					($guarantor_dat["teacher_id"]>0 ? lookup_teacher_name($guarantor_dat["teacher_id"]) : '&nbsp;')
				);
				printf( "</tr>\n" );
			}
			echo "</table>";
			echo '</div>';
			if( $_SESSION["office"]==AUTH_CASHIER ) {
				echo "<br><input type=\"submit\" name=\"addpayment\" value=\"Add Payment\">";
				echo "<input type=\"submit\" name=\"guarantor\" value=\"Edit Guarantor\">";
			} else {
				echo "<input type=\"submit\" name=\"assessment\" value=\"Assessment\">";
			}
		}
		$list->close();
	} else {
		print_hidden( $_POST, array('search_str','enrol_student_only') );
		
		$student_id = $_REQUEST["student_id"];
		echo '<input type="hidden" name="student_id" value="' . $student_id . '">';
		
		echo '<div style="border-style:solid;border-width:thin;padding:5px;">';
		$student_dat = print_studentinfo_simple( $student_id,$sy_id,false,true );
		$sy_id_st = $student_dat["sy_id"];
		if( $sy_id_st > 0 ) {
			$scholarstr = lookup_scholartype( get_scholar($student_id,$sy_id_st) );
			if( $scholarstr!='' ) echo 'Scholarship : ' . $scholarstr . '<br>';
			$guarantor_id = get_guarantor($sy_id_st,$student_id);
			if( $guarantor_id>0 ) echo 'Guarantor : ' . lookup_teacher_name($guarantor_id) . '<br>';
		} else {
			$sy_id_st = sy_id;
			echo "(Not enrolled in $str_schoolyear)<br>";
		}
		echo '</div><br>';
		if( $student_dat["date_dropped"] ) {
			$refund_rate = $student_dat["refund_rate"];
		}
		
		if( isset($_REQUEST["mode_chg"]) ) $_REQUEST["mode"] = $_REQUEST["mode_chg"];
		if( ! isset($_REQUEST["mode"]) ) {
			if( isset($_REQUEST["addpayment"]) ) {
				$_REQUEST["mode"] = $modelist[MODE_COMPULSORY];
			} else if( isset($_REQUEST["guarantor"]) ) {
				$_REQUEST["mode"] = $modelist[MODE_GUARANTOR];
			} else {
				$_REQUEST["mode"] = $modelist[MODE_ASSESSMENT];
			}
		}
		$mode = $_REQUEST["mode"];
		print_hidden( array('mode'=>$mode) );

		if( ! isset($student_dat['sy_id']) ) {
			$sy_id_st = $sy_id;
			if( ! isset($_REQUEST['assume_enrol']) ) {
				echo '<div class="prompt">The student is not enrolled in ' . $str_schoolyear . '.<br>';
				echo 'System will show the assessment as if the student is enrolled.</div>';
				echo '<input type="submit" name="assume_enrol" value="Continue">';
				$mode = -1;
			} else {
				print_hidden( array('assume_enrol'=>1) );
				print_tab( $mode );
			}
		} else {
			print_tab( $mode );
		}

		if( $mode==$modelist[MODE_COMPULSORY] ) {
			$blockdat = lookup_blockstudent($student_id);
			if( $blockdat!=null ) {
				print_blockstudent_info($blockdat);
			} else if( ! isset($_REQUEST["exec"]) ) {
				$feecategory_array = get_feecategory_array();

				echo '<div class="prompt">Enter payment data</div>';

				$obj_guarantor = new model_guarantor;
				$obj_guarantor->connect();
				$obj_guarantor->get_by_id($sy_id_st,$student_id);
				if( $obj_guarantor->get_numrows()>0 ) {
					$guarantor = $obj_guarantor->get_fetch_assoc(0);
				}

				$obj_payment = new model_payment;
				$obj_payment->connect();
				$obj_payment->get_list( $sy_id_st,$student_id );
				for( $i=0; $i<$obj_payment->get_numrows(); $i++ ) {
					$dat = $obj_payment->get_fetch_assoc($i);
					$paymentlist[$dat["feeelement_id"]] += $dat["payment"];
				}

				$assessment = calc_assessment($sy_id,$student_id);
				$total = 0;
				$total_paid = 0;
				$total_balance = 0;
				$total_topay = 0;
				echo "<table border=\"1\" cellpadding=\"3\">";
				echo '<tr><th>Title</th><th>Amount</th><th>Amount Paid</th><th>Balance</th><th>Amount to pay this time</th></tr>';
				foreach( $feecategory_array as $category_id=>$category_title ) {
					$element_array = get_feeelement_array( array($category_id),0,true );
					$subtotal = 0;
					$subpaid = 0;
					foreach( $element_array as $feeelement_id=>$val ) {
						if( ! ($val["fee_flag"] & FEEFLAG_OPTIONALFEE) ) {
							$subtotal += $assessment[$feeelement_id]["amount"];
							$subpaid += $paymentlist[$feeelement_id];
						}
					}
					$subbalance = $subtotal - $subpaid;
					if( $subtotal!=0 || $subpaid!=0 ) {
						echo '<tr>';
						echo '<td>' . $category_title . '</td>';
						echo '<td class="peso">' . mkstr_peso($subtotal) . '</td>';
						echo '<td class="peso">' . mkstr_peso($subpaid) . '</td>';
						echo '<td class="peso"' . ($subbalance!=0 ? ' style="color:red"' : '') . '>'. mkstr_peso($subbalance);
						echo '  <input type="hidden" name="B' . $category_id . '" value="'. mkstr_peso($subbalance) . '">';
						echo '</td>';
						$name = 'T' . $category_id;
						if( isset($_REQUEST[$name]) ) {
							$defaultval = $_REQUEST[$name];
						} else {
							$defaultval = mkstr_peso($subbalance);
						}
						echo '<td>';
						$unchecked = ($subbalance==0);
						if( isset($_REQUEST[$name]) && !isset($_REQUEST["C$name"]) ) $unchecked = true;
						echo '<input type="checkbox" name="C' . $name . '" onClick="funcOnCheck(\'mainform\',\'' . $name . '\')"' . ($unchecked ? "" : " checked") . '>';
						echo '<input class="peso" type="text" name="' . $name . '" value="'. $defaultval . '" onKeyUp="funcRecalc(\'mainform\')"' . ($unchecked ? " style=\"visibility:hidden\"" : "") . '>';
						echo '</td>';
						echo '</tr>';
						$total += $subtotal;
						$total_paid += $subpaid;
						$total_balance += $subbalance;
						$total_topay += retrieve_peso($defaultval);
					}
				}
				echo '<tr><td align="right">Total</td>';
				echo ' <td class="peso">' . mkstr_peso($total) . '</td>';
				echo ' <td class="peso">' . mkstr_peso($total_paid) . '</td>';
				echo ' <td class="peso"' . ($total_balance!=0 ? ' style="color:red"' : '') . '>'. mkstr_peso($total_balance) . '</td>';
				echo ' <td class="peso">'
					. '<input type="button" value="Auto" onClick="funcAutoPaymentDistribution(\'mainform\')">'
					. '<input type="button" value="Change" onClick="funcCalcChange(\'mainform\')" align="left">'
					//. '<input type="button" value="ReCal" onClick="funcRecalc(\'mainform\')">'
					. '<input type="text" name="total" value="'. mkstr_peso($total_topay) . '" readonly size="8" style="border:none;text-align:right;background-color:transparent;font-weight:bold"></td>';
				echo '</tr>';
				echo '<tr><td align="right">OR no.</td>';
				echo ' <td colspan="3" align="right"><input type="checkbox" name="disable_orno" onclick="funcOnDisableORNO();">Ignore ORNO</td>';
				echo ' <td><input type="text" name="orno" value="' . $_REQUEST["orno"] . '"><input type="button" value="->" onClick="funcOnIncrementORNO()"></td>';
				echo '</tr>';
				if( isset($_REQUEST["payor"]) ) {
					$payor = $_REQUEST["payor"];
					$visible = $_REQUEST["enablepayor"];
				} else if( isset($guarantor) ) {
					$payor = lookup_teacher_name( $guarantor["teacher_id"] );
					$visible = 0;
				} else {
					$payor = '';
					$visible = 0;
				}
				echo '<tr><td align="right">Payer</td><td colspan="3" align="right"><input type="checkbox" name="enablepayor" value="1" onclick="funcOnPayer();"' . ($visible ? " checked" : "") . '>Different payor</td>';
				echo '<td><input type="text" name="payor" value="' . $payor . '" style="visibility:' . ($visible ? "visible":"hidden") . '"></td></tr>';
				echo "</table>";
				echo "<input type=\"submit\" name=\"exec\" value=\"Add this payment\" onClick=\"funcOnPaymentAdd()\">";
				echo "<br><input type=\"button\" value=\"Assessment Slip\" onclick=\"window.open('assessment_slip.php?sy_id=$sy_id&student_id=$student_id','_blank')\">";
			} else {	// execute payment
				$obj_payment = new model_payment;
				$obj_payment->connect( auth_get_writeable() );
				$obj_payment->get_list( $sy_id_st,$student_id );
				for( $i=0; $i<$obj_payment->get_numrows(); $i++ ) {
					$dat = $obj_payment->get_fetch_assoc($i);
					$paymentlist[$dat["feeelement_id"]] += $dat["payment"];
				}
				
				$assessment = calc_assessment($sy_id,$student_id);
				$feecategory_array = get_feecategory_array();
				foreach( $feecategory_array as $category_id=>$ar ) {
					$stock[$category_id] = retrieve_peso($_REQUEST['T'.$category_id]);
					$subtotal[$category_id] = $stock[$category_id];
				}
				$element_array = get_feeelement_array(null,0,true);
				// first, process the minus balance
				foreach( $element_array as $feeelement_id=>$ar ) {
					$category_id = $ar["feecategory_id"];
					$balance = $assessment[$feeelement_id]["amount"] - $paymentlist[$feeelement_id];
					if( $balance < 0 ) {
						$topay[$feeelement_id] = $balance;
					}
					$stock[$category_id] -= $topay[$feeelement_id];
				}
				// next, the positive balance
				foreach( $element_array as $feeelement_id=>$ar ) {
					if( $topay[$feeelement_id]==0 ) {
						$category_id = $ar["feecategory_id"];
						$balance = $assessment[$feeelement_id]["amount"] - $paymentlist[$feeelement_id];
						if( $balance > 0 ) {
							if( $stock[$category_id] > $balance ) {
								$topay[$feeelement_id] = $balance;
							} else {
								$topay[$feeelement_id] = $stock[$category_id];
							}
							$stock[$category_id] -= $topay[$feeelement_id];
						}
					}
				}
				// check the new balance
				foreach( $feecategory_array as $category_id=>$ar ) {
					if( $stock[$category_id] != 0 ) {
						$err_msg = 'Amount exceeds on ' . $feecategory_array[$category_id];
						break;
					}
				}
				if( ($_REQUEST["orno"]=='') && (!isset($_REQUEST["disable_orno"])) ) {
					$err_msg = 'No ORNO';
				}
	
				if( ! isset($err_msg) ) {
					unset( $pay_ar );
					unset( $dat );
					$dat["sy_id"] = $sy_id_st;
					$dat["student_id"] = $student_id;
					if( !isset($_REQUEST["disable_orno"]) ) $dat["orno"] = $_REQUEST["orno"];
					$dat["user_id"] = auth_get_userid();
					$dat["date"] = date("Y-m-d H:i:s");
					foreach( $topay as $feeelement_id=>$amount ) {
						if( $amount!=0 ) {
							$dat["feeelement_id"] = $feeelement_id;
							$dat["payment"] = $amount;
							$pay_ar[] = $dat;
						}
					}
					
					$obj_payment->begin_transaction();
					if( $obj_payment->add_array( $pay_ar )==false ) {
						$err_msg = $obj_payment->get_errormsg();
					} else if( $_REQUEST["enablepayor"]==1 ) {
						$payment_extrainfo = new model_payment_extrainfo;
						$payment_extrainfo->connect( auth_get_writeable() );
						if( $payment_extrainfo->add( array('orno'=>$_REQUEST["orno"],'payor'=>$_REQUEST["payor"]) )==false ) {
							$err_msg = $payment_extrainfo->get_errormsg();
						}
					}
					if( ! isset($err_msg) ) {
						$obj_enrol = new model_enrol_student;
						$obj_enrol->connect( auth_get_writeable() );
						$obj_enrol->get_by_id($sy_id,$student_id);
						if( $obj_enrol->get_numrows()>0 ) {
							$dat_enrol = $obj_enrol->get_fetch_assoc(0);
							if( ! isset($dat_enrol["date_officially"]) ) {
								$dat_enrol["date_officially"] = date( "Y-m-d" );
								if( $obj_enrol->update( $dat_enrol )==false ) {
									$err_msg = $obj_enrol->get_errormsg();
								}
							}
						}
					}
					if( isset($err_msg) ) {
						$obj_payment->rollback();
					} else {
						$obj_payment->end_transaction();
					}
				}

				if( isset($err_msg) ) {
					echo '<div class="error">'  . $err_msg . '</div>';
				} else {
					echo '<div class="message">Added successfully</div>';
					echo '<table border="0" cellpadding=3>';
					$total = 0;
					foreach( $feecategory_array as $category_id=>$ar ) {
						if( $subtotal[$category_id]!=0 ) {
							echo '<tr><td>' . $ar . '</td><td class="peso">' . mkstr_peso($subtotal[$category_id]) . '</td></tr>';
							$total += $subtotal[$category_id];
						}
					}
					echo '<tr><td align="right">Total</td><td class="peso">' . mkstr_peso($total) . '</td></tr>';
					echo '</table>';
					echo "<input type=\"button\" value=\"Receipt\" onClick=\"window.open('receipt.php?sy_id=$sy_id&student_id=$student_id&date=${dat['date']}&orno=${dat['orno']}','_blank')\">";
				}
			}
		} else if( $mode==$modelist[MODE_OPTIONAL] ) {
			if( ! isset($_REQUEST["exec"]) ) {
				echo '<div class="prompt">Enter payment data</div>';
				$assessment = calc_assessment_optional($sy_id,$student_id);
				$obj_guarantor = new model_guarantor;
				$obj_guarantor->connect();
				$obj_guarantor->get_by_id($sy_id_st,$student_id);
				if( $obj_guarantor->get_numrows()>0 ) {
					$guarantor = $obj_guarantor->get_fetch_assoc(0);
				}
				$obj_payment = new model_payment;
				$obj_payment->connect();
				$obj_payment->get_list( $sy_id_st,$student_id );
				for( $i=0; $i<$obj_payment->get_numrows(); $i++ ) {
					$dat = $obj_payment->get_fetch_assoc($i);
					$paymentlist[$dat["feeelement_id"]] += $dat["payment"];
				}
				$total_paid = 0;
				$total_topay = 0;
				echo "<table border=\"1\" cellpadding=\"3\">";
				echo '<tr><th>Title</th><th>Detail</th><th>Amount Paid</th><th>Amount to pay this time</th></tr>';
				$feecategory_array = get_feecategory_array();
				foreach( $feecategory_array as $category_id=>$category_title ) {
					$element_array = get_feeelement_array( array($category_id),0 );
					$subcount = 0;
					foreach( $assessment as $feeelement_id=>$dat ) {
						if( $dat["feecategory_id"]==$category_id ) $subcount++;
					}
					if( $subcount>0 ) {
						$n = 0;
						foreach( $assessment as $feeelement_id=>$dat ) {
							if( $dat["feecategory_id"]==$category_id ) {
								$subtotal = $dat["amount"];
								$subpaid = $paymentlist[$feeelement_id];
								$name = 'E' . $feeelement_id;
								if( isset($_REQUEST[$name]) ) {
									$defaultval = $_REQUEST[$name];
								} else {
									$defaultval = mkstr_peso(max(0,$subtotal - $subpaid));
								}
								echo '<tr>';
								if( $n==0 ) echo '<td rowspan="' . $subcount . '">' . $category_title . '</td>';
								echo '<td>' . $dat["title"] . '</td>';
								echo '<td class="peso">' . mkstr_peso($subpaid) . '</td>';
								echo '<td>';
								$unchecked = isset($_REQUEST[$name]) && !isset($_REQUEST["C$name"]);
								echo '<input type="checkbox" name="C' . $name . '" onClick="funcOnCheck(\'mainform\',\'' . $name . '\')"' . ($unchecked ? "" : " checked") . '>';
								echo '<input class="peso" type="text" name="' . $name . '" value="'. $defaultval . '" onKeyUp="funcRecalc(\'mainform\')"' . ($unchecked ? " style=\"visibility:hidden\"" : "") . '>';
								echo '</td>';
								echo '</tr>';
								$total_paid += $subpaid;
								$total_topay += retrieve_peso($defaultval);
								$n++;
							}
						}
					}
				}
				echo '<tr><td align="right">Total</td><td>&nbsp;</td>';
				echo ' <td class="peso">' . mkstr_peso($total_paid) . '</td>';
				echo ' <td class="peso">'
					. '<input type="button" value="Change" onClick="funcCalcChange(\'mainform\')" align="left">'
					//. '<input type="button" value="ReCalc" onClick="funcRecalc(\'mainform\')">&nbsp;'
					. '<input type="text" name="total" value="'. mkstr_peso($total_topay) . '" readonly size="8" style="border:none;text-align:right;background-color:transparent;font-weight:bold"></td>';
				echo '</tr>';
				echo '<tr>';
				echo ' <td align="right">OR no.</td>';
				echo ' <td colspan="2" align="right"><input type="checkbox" name="disable_orno" onclick="funcOnDisableORNO();">Ignore ORNO</td>';
				echo ' <td><input type="text" name="orno" value="' . $_REQUEST["orno"] . '"><input type="button" value="->" onClick="funcOnIncrementORNO()"></td>';
				echo '</tr>';
				if( isset($_REQUEST["payor"]) ) {
					$payor = $_REQUEST["payor"];
					$visible = $_REQUEST["enablepayor"];
				} else if( isset($guarantor) ) {
					$payor = lookup_teacher_name( $guarantor["teacher_id"] );
					$visible = 0;
				}
				echo '<tr><td align="right">Payer</td><td colspan="2" align="right"><input type="checkbox" name="enablepayor" value="1" onclick="funcOnPayer();"' . ($visible ? " checked" : "") . '>Different payor</td>';
				echo '<td><input type="text" name="payor" value="' . $payor . '" style="visibility:' . ($visible ? "visible":"hidden") . '"></td></tr>';
				echo "</table>";
				echo "<input type=\"submit\" name=\"exec\" value=\"Add this payment\" onClick=\"funcOnPaymentAdd()\">";
				echo "<br><input type=\"button\" value=\"Assessment Slip\" onclick=\"window.open('assessment_slip.php?sy_id=$sy_id&student_id=$student_id','_blank')\">";
			} else {	// execute optional payment
				$obj_payment = new model_payment;
				$obj_payment->connect( auth_get_writeable() );
				$obj_payment->get_list( $sy_id_st,$student_id );
				for( $i=0; $i<$obj_payment->get_numrows(); $i++ ) {
					$dat = $obj_payment->get_fetch_assoc($i);
					$paymentlist[$dat["feeelement_id"]] += $dat["payment"];
				}
				
				$assessment = calc_assessment_optional($sy_id,$student_id);
				$feecategory_array = get_feecategory_array();
				$element_array = get_feeelement_array(null,0,true);
				foreach( $element_array as $feeelement_id=>$ar ) {
					$topay[$feeelement_id] = retrieve_peso($_REQUEST['E'.$feeelement_id]);
					$subtotal[$ar["feecategory_id"]] += $topay[$feeelement_id];
				}
				if( ($_REQUEST["orno"]=='') && (!isset($_REQUEST["disable_orno"])) ) {
					$err_msg = 'No ORNO';
				}

				if( ! isset($err_msg) ) {
					unset( $pay_ar );
					unset( $dat );
					$dat["sy_id"] = $sy_id_st;
					$dat["student_id"] = $student_id;
					if( !isset($_REQUEST["disable_orno"]) ) $dat["orno"] = $_REQUEST["orno"];
					$dat["user_id"] = auth_get_userid();
					$dat["date"] = date("Y-m-d H:i:s");
					foreach( $topay as $feeelement_id=>$amount ) {
						if( $amount!=0 ) {
							$dat["feeelement_id"] = $feeelement_id;
							$dat["payment"] = $amount;
							$pay_ar[] = $dat;
						}
					}
					if( $obj_payment->add_array( $pay_ar )==false ) {
						$err_msg = $obj_payment->get_errormsg();
					} else if( $_REQUEST["enablepayor"]==1 ) {
						$payment_extrainfo = new model_payment_extrainfo;
						$payment_extrainfo->connect( auth_get_writeable() );
						if( $payment_extrainfo->add( array('orno'=>$_REQUEST["orno"],'payor'=>$_REQUEST["payor"]) )==false ) {
							$err_msg = $payment_extrainfo->get_errormsg();
						}
					}
				}

				if( isset($err_msg) ) {
					echo '<div class="error">'  . $err_msg . '</div>';
				} else {
					echo '<div class="message">Added successfully</div>';
					echo '<table border="0" cellpadding=3>';
					$total = 0;
					foreach( $feecategory_array as $category_id=>$ar ) {
						if( $subtotal[$category_id]!=0 ) {
							echo '<tr><td>' . $ar . '</td><td class="peso">' . mkstr_peso($subtotal[$category_id]) . '</td></tr>';
							$total += $subtotal[$category_id];
						}
					}
					echo '<tr><td align="right">Total</td><td class="peso">' . mkstr_peso($total) . '</td></tr>';
					echo '</table>';
					echo "<input type=\"button\" value=\"Receipt\" onClick=\"window.open('receipt.php?sy_id=$sy_id&student_id=$student_id&date=${dat['date']}&orno=${dat['orno']}','_blank')\">";
				}
			}
		} else if( $mode==$modelist[MODE_GUARANTOR] ) {
			$guarantor = new model_guarantor;
			$guarantor->connect( auth_get_writeable() );
			if( ! isset($_REQUEST["exec"]) ) {
				$guarantor->get_by_id( $sy_id_st,$student_id );
				$dat = $guarantor->get_fetch_assoc(0);
				echo '<div class="prompt">Select guarantor</div>';
				$teacher_array[0] = ' - none - ';
				$teacher_array += get_teacher_array();
				echo '<table border="1" cellpadding=3>';
				echo '<tr><td>Guarantor</td><td>' . mkhtml_select( "teacher_id",$teacher_array,$dat["teacher_id"] ) . '</td></tr>';
				echo '<tr><td>Due date</td><td><input type="text" name="due_date" value="' . mkstr_date($dat["due_date"]) . '">(MM/DD/YYYY)</td></tr>';
				echo '<tr><td>remark</td><td><input type="text" name="remark" value="' . $dat["remark"] . '"></td></tr>';
				echo '</table>';
				if( isset($dat["guarantor_id"]) ) print_hidden( array('guarantor_id'=>$dat["guarantor_id"]) );
				echo '<input type="submit" name="exec" value="update">';
				print_hidden( array('guarantor'=>1) );
			} else {
				$_REQUEST["due_date"] = retrieve_date($_REQUEST["due_date"]);
				$_REQUEST["sy_id"] = $sy_id_st;
				if( isset($_REQUEST["guarantor_id"]) ) {
					if( $_REQUEST["teacher_id"]==0 ) {
						$result = $guarantor->begin_transaction();
						if( $result ) $result = $guarantor->del( $_REQUEST["guarantor_id"] );
						if( $result ) {
							if( check_officially_enrolled_direct($sy_id_st,$student_id)==false ) {
								$obj_enrol = new model_enrol_student;
								$obj_enrol->connect( auth_get_writeable() );
								$result = $obj_enrol->cancel_official_enrol($sy_id_st,$student_id);
								if( $result==false ) $guarantor->set_error( $obj_enrol->get_errormsg() );
							}
						}
						if( $result ) {
							$guarantor->end_transaction();
						} else {
							$guarantor->rollback();
						}
					} else {
						$result = $guarantor->update( $_REQUEST );
					}
					if( $result==false ) {
						$err_msg = $guarantor->get_errormsg();
					}
				} else if( $_REQUEST["teacher_id"]>0 ) {
					$guarantor->begin_transaction();
					if( $guarantor->add( $_REQUEST )==false ) {
						$err_msg = $guarantor->get_errormsg();
					} else {
						$obj_enrol = new model_enrol_student;
						$obj_enrol->connect( auth_get_writeable() );
						$obj_enrol->get_by_id($sy_id,$student_id);
						$dat_enrol = $obj_enrol->get_fetch_assoc(0);
						if( ! isset($dat_enrol["date_officially"]) ) {
							$dat_enrol["date_officially"] = date( "Y-m-d" );
							if( $obj_enrol->update( $dat_enrol )==false ) {
								$err_msg = $obj_enrol->get_errormsg();
							}
						}
					}
					if( isset($err_msg) ) {
						$guarantor->rollback();
					} else {
						$guarantor->end_transaction();
					}
				}
				if( isset($err_msg) ) {
					echo '<div class="error">' . $err_msg . '</div>';
				} else {
					echo '<div class="message">Updated successfully</div>';
				}
			}
		} else if( $mode==$modelist[MODE_ADDITIONAL] ) {
			$obj_additional = new model_additionalfee;
			$obj_additional->connect( auth_get_writeable() );
			if( isset($_REQUEST["add"]) ) {
				if( ! isset($_REQUEST["exec"]) ) {
					$_SESSION["goback"] = array(
						'page' => 'assessment.php',
						'param' => array( 'mode'=>$_REQUEST["mode"], 'student_id'=>$student_id, 'add'=>1 )
					);
					echo '<div class="prompt">Enter Additional Fee data</div>';
					echo '<table border="1" cellpadding=3>';
					echo '<tr><td>Title</td><td>'
						. mkhtml_select( "feeelement_id",get_feeelement_array(null,FEEFLAG_ADDITIONALFEE|FEEFLAG_FEERATE|FEEFLAG_MISCFEE|FEEFLAG_GRADFEE),MKHTML_SELECT_NONE)
						. "<input type=\"button\" value=\"new\" onClick=\"window.open('feeelement.php?add=1&fee_flag=" . FEEFLAG_ADDITIONALFEE . "','_self');\">"
						. "<input type=\"button\" value=\"edit\" onClick=\"window.open('feeelement.php?edit=1&feeelement_id='+document.mainform.feeelement_id.value,'_self');\">"
						. "<input type=\"button\" value=\"del\" onClick=\"window.open('feeelement.php?del=1&feeelement_id='+document.mainform.feeelement_id.value,'_self');\">"
						. '</td></tr>';
					echo '<tr><td>Amount</td><td><input type="text" class="peso" name="amount"></td></tr>'; 
					echo '</table>';
					echo '<input type="submit" name="exec" value="Add">';
					print_hidden( array('add'=>1) );
				} else {
					$dat = array(
						'sy_id' => $sy_id_st,
						'student_id' => $student_id,
						'feeelement_id' => $_REQUEST["feeelement_id"],
						'amount' => retrieve_peso($_REQUEST["amount"])
					);
					if( $obj_additional->add( $dat )==false ) {
						echo '<div class="error">' . $obj_additional->get_errormsg() . '</div>';
					} else {
						echo '<div class="message">Successfully added</div>';
					}
				}
			} else if( isset($_REQUEST["edit"]) ) {
				$additionalfee_id = $_REQUEST["additionalfee_id"];
				if( $additionalfee_id==0 ) {
					echo '<div class="error">Not selected</div>';
				} else if( ! isset($_REQUEST["exec"]) ) {
					$dat = $obj_additional->get($additionalfee_id);
					echo '<div class="prompt">Edit Additional Fee data</div>';
					echo '<table border="1" cellpadding=3>';
					echo '<tr><td>Title</td><td>'
						. mkhtml_select( "feeelement_id",get_feeelement_array(null,FEEFLAG_ADDITIONALFEE|FEEFLAG_FEERATE|FEEFLAG_MISCFEE|FEEFLAG_GRADFEE),$dat["feeelement_id"])
						. '</td></tr>';
					echo '<tr><td>Amount</td><td><input type="text" class="peso" name="amount" value="' . mkstr_peso($dat["amount"]) . '"></td></tr>'; 
					echo '</table>';
					echo '<input type="submit" name="exec" value="Update">';
					print_hidden( array('additionalfee_id'=>$additionalfee_id,'edit'=>1) );
				} else {
					$dat = array(
						'additionalfee_id' => $additionalfee_id,
						'feeelement_id' => $_REQUEST["feeelement_id"],
						'amount' => retrieve_peso($_REQUEST["amount"])
					);
					if( $obj_additional->update( $dat )==false ) {
						echo '<div class="error">' . $obj_additional->get_errormsg() . '</div>';
					} else {
						echo '<div class="message">Successfully updated</div>';
					}
				}
			} else if( isset($_REQUEST["del"]) ) {
				$additionalfee_id = $_REQUEST["additionalfee_id"];
				if( $additionalfee_id==0 ) {
					echo '<div class="error">Not selected</div>';
				} else if( ! isset($_REQUEST["exec"]) ) {
					$dat = $obj_additional->get($additionalfee_id);
					echo '<div class="prompt">Delete following data?</div>';
					echo '<table border="1" cellpadding=3>';
					$feeelement_array = get_feeelement_array();
					echo '<tr><td>Title</td><td>' . $feeelement_array[$dat["feeelement_id"]] . '</td></tr>';
					echo '<tr><td>Amount</td><td>' . mkstr_peso($dat["amount"]) . '</td></tr>'; 
					echo '</table>';
					echo '<input type="submit" name="exec" value="Delete">';
					print_hidden( array('additionalfee_id'=>$additionalfee_id,'del'=>1) );
				} else {
					if( $obj_additional->del( $additionalfee_id )==false ) {
						echo '<div class="error">' . $obj_additional->get_errormsg() . '</div>';
					} else {
						echo '<div class="message">Successfully deleted</div>';
					}
				}
			} else {
				$feecategory_array = get_feecategory_array();
				$obj_additional->get_list( $sy_id_st,$student_id );
				echo '<table border="1" cellpadding=3>';
				echo '<tr><th>&nbsp;</th><th>Title</th><th>Amount</th><th>Category</th></tr>';
				$n = $obj_additional->get_numrows();
				for( $i=0; $i<$n; $i++ ) {
					$dat = $obj_additional->get_fetch_assoc($i);
					echo '<tr>';
					echo '<td><input type="radio" name="additionalfee_id" value="' . $dat["additionalfee_id"] . '"></td>';
					echo '<td>' . $dat["title"] . '</td>';
					echo '<td class="peso">' . mkstr_peso($dat["amount"]) . '</td>';
					echo '<td>' . $feecategory_array[$dat["feecategory_id"]] . '</td>';
					echo '</tr>';
				}
				echo '</table>';
				if( $n>0 ) {
					echo '<input type="submit" name="edit" value="Edit">';
					echo '<input type="submit" name="del" value="Delete">';
				}
				echo '<input type="submit" name="add" value="Add">';
			}
			echo "<br><input type=\"button\" value=\"Assessment Slip\" onclick=\"window.open('assessment_slip.php?sy_id=$sy_id&student_id=$student_id','_blank')\">";
		} else if( $mode==$modelist[MODE_PAYMENTRECORD] ) {
			if( $_REQUEST["edit"] ) {
				$date = strtok( $_REQUEST["id"], "," );
				$orno = strtok( "" );
				if( $date=='' ) {
					echo '<div class="error">Not Selected</div>';
				} else if( ! isset($_REQUEST["exec"]) ) {
					echo '<div class="message">Enter new ORNO</div>';
					echo '<table border="1" cellpadding=3>';
					echo '<tr><td>Date</td><td><input type="text" name="new_date" value="' . mkstr_date($date) . '"></td></tr>';
					echo '<tr><td>ORNO</td><td><input type="text" name="new_orno" value="' . $orno . '"></td></tr>';
					echo '</table>';
					echo '<input type="submit" name="exec" value="update">';
					print_hidden( array('id'=>$_REQUEST["id"], 'edit'=>1) );
				} else {
					$new_date = retrieve_date($_REQUEST["new_date"]);
					$obj = new model_payment;
					$obj->connect( auth_get_writeable() );
					$result = $obj->begin_transaction();
					if( $result ) {
						$obj_enrol = new model_enrol_student;
						$obj_enrol->connect( auth_get_writeable() );
						$obj_enrol->get_by_id( $sy_id_st,$student_id );
						$dat = $obj_enrol->get_fetch_assoc(0);
						if( strtotime($dat["date_officially"]) > strtotime($new_date) ) {
							$dat["date_officially"] = $new_date;
							$result = $obj_enrol->update( $dat );
							if( $result==false ) $obj->set_error( $obj_enrol->get_errormsg() );
						}
					}
					if( $result ) $result = $obj->change_date_orno( $sy_id_st,$student_id,$date,$orno,$new_date,$_REQUEST["new_orno"] );
					if( $result ) $result = $obj->end_transaction();
					if( $result==false ) {
						$obj->rollback();
						$err_msg = $obj->get_errormsg();
						echo '<div class="error">' . $err_msg . '</div>';
					} else {
						echo '<div class="message">Updated successfully</div>';
					}
				}
			} else if( $_REQUEST["del"] ) {
				$date = strtok( $_REQUEST["id"], "," );
				$orno = strtok( "" );
				$obj_payment = new model_payment;
				$obj_payment->connect( auth_get_writeable() );
				if( $date==0 ) {
					echo '<div class="error">Data not selected</div>';
				} else if( ! isset($_REQUEST["exec"]) ) {
					echo '<div class="warning"><b>Are you sure you want to delete following payment!?</div>';
					echo '<table border="1" cellpadding=3>';
					echo '<tr><td>Date</td><td>' . mkstr_date($date) . '</td></tr>';
					echo '<tr><td>ORNO</td><td>' . $orno . '</td></tr>';
					echo '<tr><td>&nbsp;</td><td>';
					$feecategory_array = get_feecategory_array();
					$obj_payment->get_list_group_by_category( $sy_id_st,$student_id,$date,$orno );
					$total = 0;
					echo '<table border="0" cellpadding=3>';
					for( $i=0; $i<$obj_payment->get_numrows(); $i++ ) {
						$dat = $obj_payment->get_fetch_assoc($i);
						echo '<tr><td>' . $feecategory_array[$dat["feecategory_id"]] . ' :</td><td>' . mkstr_peso($dat["payment"]) . '</td></tr>';
						$total += $dat["payment"];
					}
					echo '<tr><td>Total</td><td>' . mkstr_peso($total) . '</td></tr>';
					echo '</table>';
					echo '</td></tr>';
					echo '</table>';
					echo '<input type="submit" name="exec" value="Delete">';
					print_hidden( array('id'=>$_REQUEST["id"], 'del'=>1) );
				} else {
					$result = $obj_payment->begin_transaction();
					if( $result ) $result = $obj_payment->del($sy_id_st,$student_id,$date,$orno);
					if( $result ) {
						if( check_officially_enrolled_direct($sy_id_st,$student_id)==false ) {
							$obj_enrol = new model_enrol_student;
							$obj_enrol->connect( auth_get_writeable() );
							$result = $obj_enrol->cancel_official_enrol($sy_id_st,$student_id);
							if( $result==false ) $obj_payment->set_error( $obj_enrol->get_errormsg() );
						}
					}
					if( $result ) {
						$obj_payment->end_transaction();
					} else {
						$obj_payment->rollback();
					}
					if( $result==false ) {
						echo '<div class="error">' . $obj_payment->get_errormsg() . '</div>';
					} else {
						echo '<div class="message">Deleted successfully</div>';
					}
				}
			} else {
				$assessment = calc_assessment($sy_id,$student_id);
				$total = 0;
				foreach( $assessment as $ar ) $total += $ar["amount"];
				$feecategory_array = get_feecategory_array();
				
				// payment
				$obj_payment = new model_payment;
				$obj_payment->connect();
				$obj_payment->get_list_group_by_category($sy_id_st,$student_id);
			
				$total_payment = 0;

				// if cashier office, 'edit' button appears.
				if( $_SESSION["office"]==AUTH_CASHIER ) $enable_edit = true;
	
				//echo "<h2>Payment Record</h2>";
				echo '<table border="1" cellpadding=3>';
				echo '<tr>' . ($enable_edit ? "<th></th>" : "") . '<th>Date</th><th>O.R. no.</th><th>detail</th><th>Amount</th>';
				if( $_SESSION["office"]==AUTH_CASHIER ) echo '<th>Reciept</th>';
				echo '</tr>';
	
				for( $i=0; $i<$obj_payment->get_numrows(); $i++ ) {
					$ar = $obj_payment->get_fetch_assoc($i);
					$id = $ar["date"] . "," . $ar["orno"];
					$paymentlist[$id]["date"] = $ar["date"];
					$paymentlist[$id]["orno"] = $ar["orno"];
					$paymentlist[$id]["detail"] .= $feecategory_array[$ar["feecategory_id"]] . ' : ' . mkstr_peso($ar["payment"]) . '<br>';
					$paymentlist[$id]["payment"] += $ar["payment"];
					$total_payment += $ar["payment"];
				}
				if( count($paymentlist)>0 ) {
					foreach( $paymentlist as $id=>$ar ) {
						printf( '<tr>%s<td>%s</td><td>%s</td><td>%s</td><td class="peso">%s</td>%s</tr>',
							($enable_edit ? "<td><input type=\"radio\" name=\"id\" value=\"" . $id . "\"></td>" : ""),
							mkstr_date($ar["date"]),
							$ar["orno"],
							$ar["detail"],
							mkstr_peso($ar["payment"]),
							($_SESSION["office"]==AUTH_CASHIER ? "<td><input type=\"button\" value=\"Receipt\"" .
								" onClick=\"window.open('receipt.php?sy_id=$sy_id_st&student_id=$student_id&date=${ar['date']}&orno=${ar['orno']}','_blank')\"></td>" : ""
							)
						);
					}
				}

				$balance = ($total - $total_payment);
				printf( "<tr><td></td><td></td><td></td><td></td>%s%s</tr>",
					($_SESSION["office"]==AUTH_CASHIER ? "<td></td>" : ""),
					($enable_edit ? "<td></td>" : "") );
				printf( '<tr>%s<td>&nbsp;</td><td>&nbsp;</td><td align="right">Total</td><td class="peso">%s</td>%s</tr>',
					($enable_edit ? "<td>&nbsp;</td>" : ""),
					mkstr_peso($total_payment),
					($_SESSION["office"]==AUTH_CASHIER ? "<td>&nbsp;</td>" : "") );
				printf( '<tr>%s<td>&nbsp;</td><td>&nbsp;</td><td align="right">Balance</td><td class="peso"'
					. ($balance ? 'style="color:red"' : '')
					. '>%s</td>%s</tr>',
					($enable_edit ? "<td>&nbsp;</td>" : ""),
					mkstr_peso($balance),
					($_SESSION["office"]==AUTH_CASHIER ? "<td>&nbsp;</td>" : "") );
				echo "</table>";
				if( count($paymentlist)>0 ) {
					if( $enable_edit ) {
						echo '<input type="submit" name="edit" value="Edit ORNO/Date">';
						echo '<input type="submit" name="del" value="Delete payment"><br>';
					}
				}
				echo "<input type=\"button\" value=\"Assessment Slip\" onclick=\"window.open('assessment_slip.php?sy_id=$sy_id&student_id=$student_id','_blank')\">";
			}
		} else if( $mode==$modelist[MODE_ASSESSMENT] ) {	 // assessment
			$assessment = calc_assessment($sy_id,$student_id);
			$total = 0;

			$feecategory_array = get_feecategory_array();
			
			//echo "<h2>Assessment</h2>";
			echo "<table border=\"1\" cellpadding=\"3\">";
			echo '<tr><th>Title</th><th>Detail</th><th>Amount</th></tr>';
			foreach( $feecategory_array as $category_id=>$category_title ) {
				$detail = '<table border="0" width="100%" cellpadding=3>';
				$subtotal = 0;
				$subcount = 0;
				foreach( $assessment as $ar ) {
					if( $ar["feecategory_id"]==$category_id ) {
						for( $i=0; $i<count($ar["detail"]); $i++ ) {
							$detail .= '<tr><td>' . $ar["detail"][$i]["desc"] . ' :</td><td align="right">' . mkstr_peso($ar["detail"][$i]["amount"]) . '</td>';
						}
						$subtotal += $ar["amount"];
						$subcount++;
					}
				}
				$detail .= '</table>';
				if( $subcount > 0 ) {
					echo '<tr>';
					echo '<td>' . $category_title . '</td>';
					echo '<td>' . $detail . '</td>';
					echo '<td class="peso">' . mkstr_peso($subtotal) . '</td>';
					echo '</tr>';
					$total += $subtotal;
				}
			}
			
	$link = mysql_connect( $g_dbhostname, $g_dbusername,base64_decode($g_dbpassword) );
	if (!$link) {
		die('Could not connect: ' . mysql_error());
	}
	$db_selected = mysql_select_db($g_dbname, $link);
	
	$sql_remarks = 'select assessment_remark from tblstudentsenrolled  where student_id=' . $student_id . ' and sy_id=' . $sy_id;
	$result_remark = mysql_query($sql_remarks);
	while ($row = mysql_fetch_assoc($result_remark)) {
		$curr_remarks =  $row["assessment_remark"];
	}
			
		
			printf( "<tr><td></td><td></td><td></td></tr>" );
			printf( '<tr><td>&nbsp;</td><td align="right">Total</td><td class="peso">%s</td></tr>', mkstr_peso($total) );
			if ($curr_remarks<>"") {
				echo '<tr><td colspan="3">Remarks: &nbsp;&nbsp;' . $curr_remarks . '</td></tr>';
			}
			echo "</table>";
		
	
			echo "<input type=\"button\" value=\"Subject list\" onclick=\"window.open('list_subject.php?sy_id=$sy_id&student_id=$student_id','_blank')\">";
			echo "<input type=\"button\" value=\"Assessment Slip\" onclick=\"window.open('assessment_slip.php?sy_id=$sy_id&student_id=$student_id','_blank')\">";
			echo "<input type=\"button\" value=\"Assessment Remark\" onclick=\"window.open('assessment_remark.php?sy_id=$sy_id&student_id=$student_id','_blank')\">";
		}
	}
	echo "</form>";

	if( isset($_REQUEST['exec']) && !isset($err_msg) ) {
		echo '<form method="POST">';
		echo '<input type="submit" value="OK">';
		print_hidden( $_POST, array('mode','student_id','search_str','enrol_student_only') );
		echo '</form>';
	}

	print_footer();

	if( (! isset($_REQUEST['student_id'])) || isset($_REQUEST['search']) ) {
		echo '<form action="index.php" method="POST" id="goback">';
		echo '<input type="submit" value="Go back">';
		echo '<input type="hidden" name="sy_id" value="' . $sy_id . '">';
		echo '</form>';
	} else if( isset($_REQUEST["exec"]) ) {
		if( isset($err_msg) ) {
			echo '<form method="POST" id="goback">';
			echo '<input type="submit" value="Go back">';
			unset( $_POST["exec"] );
			print_hidden( $_POST );
			echo '</form>';
		} else {
			echo '<form method="POST" id="goback">';
			echo '<input type="submit" value="Go back">';
			echo '</form>';
		}
	} else if( isset($_REQUEST["edit"]) || isset($_REQUEST["del"]) ) {
		echo '<form method="POST" id="goback">';
		echo '<input type="submit" value="Go back">';
		print_hidden( $_POST, array('mode','student_id','search_str','enrol_student_only') );
		echo '</form>';
	} else {
		echo '<form method="POST" id="goback">';
		echo '<input type="submit" value="Go back">';
		print_hidden( $_REQUEST,array('search_str','enrol_student_only') );
		echo '</form>';
	}
?>

</body>

</html>
