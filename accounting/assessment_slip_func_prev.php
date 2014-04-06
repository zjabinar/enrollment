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


function get_previous_instance($sy)
{
	$val_sem = substr($sy_id, -1); 
	$val_year = substr($sy_id,0,4);
	$temp_sem1 = 0;
	$temp_sem2 = 0;
	$temp_sem3 = 0;
	$temp_year1 = 0;
	$temp_year2 = 0;
	$temp_year3 = 0;	


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

	
	$sy1 = $temp_year1 . $temp_sem1;
	$sy2 = $temp_year2 . $temp_sem2;
	$sy3 = $temp_year3 . $temp_sem3;
	$sy4 = $temp_year4 . $temp_sem4;
	$sy5 = $temp_year5 . $temp_sem5;
	$sy6 = $temp_year6 . $temp_sem6;


	$val1 = get_previous_balance($student_id, $sy1);
	$val2 = get_previous_balance($student_id, $sy2);
	$val3 = get_previous_balance($student_id, $sy3);
	$val4 = get_previous_balance($student_id, $sy4);
	$val5 = get_previous_balance($student_id, $sy5);
	$val6 = get_previous_balance($student_id, $sy6);

	$val_total = 0;
	$val_total = $val1 + $val2 + $val3 + $val4 + $val5 + $val6;
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
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td align="right">' . $temp_year6 . " - " . chk_sem($temp_sem6) . '</td><td class="peso">&nbsp;' . $val4 . '</td></tr>';
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
