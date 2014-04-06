<?php

require_once("syinfo.inc");
require_once("feerate.inc");
require_once("miscfee.inc");
require_once("gradfee.inc");
require_once("additionalfee.inc");
require_once("optionalfee.inc");
require_once("class.inc");
require_once("enrol_student.inc");
require_once("enrol_class.inc");
require_once("feeelement.inc");
require_once("scholarship.inc");
require_once("payment.inc");



function parse_refund( $desc, $amount, $rate )
{
	if( $rate > 0 ) {
		$desc .= ' (' . (100-$rate) . '%)';
		$amount = $amount * (100-$rate) / 100;
	}
	return array( $desc,$amount );
}



function calc_assessment( $sy_id, $student_id )
{
	// Lets make these value static so that it will help continuous calculation
	static $feeelement_array = null;
	static $student = null;
	static $enrol_student = null;
	static $feeratelist = null;
	static $feeratetitle = null;
	static $obj_scholar = null;
	static $classlist = null;
	static $gradlist = null;
	static $misclist = null;
	static $additionallist = null;
	static $payment = null;

	static $department_cache = array();
	static $school_id_cache = array();
	static $feerate_cache = array();
	static $syinfo_cache = array();
	static $year_cache = array();
	static $semester_cache = array();
	static $gradlist_cache = array();
	static $misclist_cache = array();

	// Initialization only in the first call
	if( $feeelement_array==null ) {
		$feeelement_array = get_feeelement_array(null,0,true);
		$student = new model_student;
		$student->connect();
		$enrol_student = new model_enrol_student;
		$enrol_student->connect();
		$feeratelist = new model_feerate;
		$feeratelist->connect();
		$feeratetitle = get_feeratetitle_array(true);
		$obj_scholar = new model_scholarship;
		$obj_scholar->connect();
		$classlist = new model_regist_class;
		$classlist->connect();
		$gradlist = new model_gradfee;
		$gradlist->connect();
		$misclist = new model_miscfee;
		$misclist->connect();
		$additionallist = new model_additionalfee;
		$additionallist->connect();
		$payment = new model_payment;
		$payment->connect();
	}

	// get student information
	$student->get_by_id( $student_id );
	$student_dat = $student->get_fetch_assoc(0);

	$enrol_student->get_by_id( $sy_id,$student_id );
	//if( $enrol_student->get_numrows()==0 ) return array();
	if( $enrol_student->get_numrows()==0 ) {
		$enrol_student_dat = array(
			'sy_id' => $sy_id,
			'sy_id_end' => $sy_id,
			'feebase_sy' => $student_dat['feebase_sy'],
			'course_id' => $student_dat['course_id']
		);
	} else {
		$enrol_student_dat = $enrol_student->get_fetch_assoc(0);
		$last_sy_id = $enrol_student->get_last_sy_id($sy_id,$student_id);
	}
	$sy_id = $enrol_student_dat["sy_id"];
	$feebase_year = get_year_from_schoolyear( $enrol_student_dat["feebase_sy"] );
	$course_id = $enrol_student_dat["course_id"];
	if( ! isset($department_cache[$course_id]) ) {
		$department_cache[$course_id] = get_department_id_from_course_id( $course_id );
	}
	$department_id = $department_cache[$course_id];
	if( ! isset($school_id_cache[$course_id]) ) {
		$school_id_cache[$course_id] = get_school_id_from_course_id( $course_id );
	}
	$school_id = $school_id_cache[$course_id];
	$year_level = $enrol_student_dat["year_level"];
	$new_student = ($student_dat["enter_sy"]==$sy_id) ? true : false;
	$outside_student = ($enrol_student_dat["campus_flag"] & CAMPUSFLAG_OUTSIDE) ? true : false;
	$whole_year_student = ($enrol_student_dat["sy_id"]!=$enrol_student_dat["sy_id_end"]) ? true : false;
	if( $enrol_student_dat["date_dropped"]!=0 ) {
		$refund_rate = $enrol_student_dat["refund_rate"];
		$payment->get_payment_list_of( $sy_id,$student_id,$enrol_student_dat["date_dropped"] );
		for( $i=0; $i<$payment->get_numrows(); $i++ ) {
			$dat = $payment->get_fetch_assoc($i);
			$paiddata[$dat["feeelement_id"]] = $dat["payment"];
		}
	}
	$onceinayear = ( (($sy_id%10)==1) || (intval($sy_id/10) != intval($last_sy_id/10)) ) ? true : false;

	// retrieve feerate
	$id = sprintf( '%d:%d:%d:%d',$feebase_year,$course_id,$year_level,$sy_id );
	if( ! isset($feerate_cache[$id]) ) {
		$feerate_cache[$id] = $feeratelist->get($feebase_year,$course_id,$year_level,$sy_id);
	}
	$feerate = $feerate_cache[$id];

	// retrieve scholarship info
	$obj_scholar->get( $sy_id,$student_id );
	if( $obj_scholar->get_numrows()>0 ) {
		$dat = $obj_scholar->get_fetch_assoc(0);
		$obj_scholartype = new model_scholartype;
		$obj_scholartype->connect();
		$obj_scholartype->get_by_id( $dat["scholartype_id"] );
		$scholar = $obj_scholartype->get_fetch_assoc(0);
	}

	// retrieve semester info
	$id = sprintf( '%d:%d:%d',$sy_id,$course_id,$year_level );
	if( ! isset($syinfo_cache[$id]) ) {
		$syinfo_db = new model_syinfo;
		$syinfo_db->connect();
		$syinfo_db->get($sy_id,$course_id,$year_level);
		$syinfo_cache[$id] = $syinfo_db->get_fetch_assoc(0);
	}
	$syinfo = $syinfo_cache[$id];

	// retrieve year, semester
	if( ! isset($year_cache[$sy_id]) ) {
		$year_cache[$sy_id] = get_year_from_schoolyear($sy_id);
		$semester_cache[$sy_id] = get_semester_from_schoolyear($sy_id);
	}
	$year = $year_cache[$sy_id];
	$semester = $semester_cache[$sy_id];

	// Penalties
	$late_enrol_day = 0;
	if( isset($syinfo["lastday_of_enrol"]) ) {
		if( isset( $enrol_student_dat["date_officially"] ) ) {
			$time_enrol = strtotime( $enrol_student_dat["date_officially"] );
		} else {
			$time_enrol = strtotime( date('Y-m-j') );
		}
		$time_lastday = strtotime( $syinfo["lastday_of_enrol"] );
		if( $time_enrol > $time_lastday ) {
			$late_enrol_day = (int)(($time_enrol - $time_lastday) / (60*60*24));
		}
	}

	// Calculate fee rates
	list($total_subjects,$total_units,$total_units_tuition,$total_per_classflag,$spec_fees) = $classlist->get_total_info($sy_id,$student_id);

	foreach( $feeratetitle as $feeratetitle_id=>$val ) {
		list($rate,$feeelement_id) = $feerate[$feeratetitle_id];
		$desc = null;
		$amount = 0;
		$feeratetype = $val["feeratetype"];
		switch( $feeratetype ) {
			case FEERATETYPE_TUITION:
				$amount = $total_units_tuition * $rate;
				if( $amount > 0 ) {
					$opt_desc = '';
					if( isset($scholar) ) {
						if( $scholar["tuition_deduction_rate"]!=0 ) {
							$amount = $amount * (100-$scholar["tuition_deduction_rate"]) / 100;
							$opt_desc = sprintf( ' * %d%%', (100-$scholar["tuition_deduction_rate"]) );
						}
						if( $scholar["tuition_deduction_amount"]!=0 ) {
							$amount = $amount - $scholar["tuition_deduction_amount"];
							//if( $amount < 0 ) $amount = 0;
							$opt_desc .= ' - ' . mkstr_peso($scholar["tuition_deduction_amount"]);
						}
					}
					$desc = sprintf( "%d units * %s%s", $total_units_tuition, mkstr_peso($rate), $opt_desc );
				}
				break;
			case FEERATETYPE_ADDING:
				$addlist = new model_add_class;
				$addlist->connect();
				$total_addsubjects = $addlist->get_total_subjects($sy_id,$student_id);
				$amount = $total_addsubjects * $rate;
				if( $amount>0 ) {
					$desc = sprintf( "add %s subject%s * %s", $total_addsubjects, $total_addsubjects>1 ? "s" : "", mkstr_peso($rate) );
				}
				break;
			case FEERATETYPE_CHANGING:
				$chglist = new model_change_class;
				$chglist->connect();
				$total_chgsubjects = $chglist->get_total_subjects($sy_id,$student_id);
				$amount = $total_chgsubjects * $rate;
				if( $amount>0 ) {
					$desc = sprintf( "change %s subject%s * %s", $total_chgsubjects, $total_chgsubjects>1 ? "s" : "", mkstr_peso($rate) );
				}
				break;
			case FEERATETYPE_DROPPING:
				$droplist = new model_drop_class;
				$droplist->connect();
				$total_dropsubjects = $droplist->get_total_subjects($sy_id,$student_id);
				$amount = $total_dropsubjects * $rate;
				if( $amount>0 ) {
					$desc = sprintf( "drop %s subject%s * %s", $total_dropsubjects, $total_dropsubjects>1 ? "s" : "", mkstr_peso($rate) );
				}
				break;
			case FEERATETYPE_LATE1ST:
				if( $late_enrol_day==1 ) {
					$amount = $rate;
					if( $amount>0 ) {
						$desc = "Late Enrollment 1 day";
					}
				}
				break;
			case FEERATETYPE_LATE2ND:
				if( $late_enrol_day==2 ) {
					$amount = $rate;
					if( $amount>0 ) {
						$desc = "Late Enrollment 2 days";
					}
				}
				break;
			case FEERATETYPE_LATE3RD:
				if( $late_enrol_day>=3 ) {
					$amount = $rate;
					if( $amount>0 ) {
						$desc = sprintf( "Late Enrollment %d days", $late_enrol_day );
					}
				}
				break;
			case FEERATETYPE_PERUNIT:
				$total_unit = $total_per_classflag[$feeratetitle_id];
				$amount = $total_unit * $rate;
				if( $amount>0 ) {
					$desc = sprintf( "%d %s unit%s * %s", $total_unit, $val["short_name"], $total_unit>1 ? "s" : "", mkstr_peso($rate) );
				}
				break;
			case FEERATETYPE_PERSUBJECT:
				$total_subject = $total_per_classflag[$feeratetitle_id];
				$amount = $total_subject * $rate;
				if( $amount>0 ) {
					$desc = sprintf( "%d %s subject%s * %s", $total_subject, $val["short_name"], $total_subject>1 ? "s" : "", mkstr_peso($rate) );
				}
				break;
			case FEERATETYPE_PERSEMESTER:
				$amount = $total_per_classflag[$feeratetitle_id] * $rate;
				if( $amount > 0 ) {
					$desc = $val["title"];
				}
				break;
		}
		if( $scholar["flag"] & SCHOLARFLAG_TOTALLYFREE ) {
			if( $feeratetype!=FEERATETYPE_LATE1ST && $feeratetype!=FEERATETYPE_LATE2ND && $feeratetype!=FEERATETYPE_LATE3RD ) {
				$amount = 0;
				$desc = null;
			}
		}
		if( $refund_rate > 0 ) {
			if( $feeratetype==FEERATETYPE_TUITION ) {
				$amount = $paiddata[$feeelement_id];
				if( $amount > 0 ) {
					$desc = $feeelement_array[$feeelement_id]["title"];
				} else {
					$desc = null;
				}
			} else if( $feeratetype!=FEERATETYPE_DROPPING ) {
				if( $paiddata[$feeelement_id] > 0 ) {
					if( $desc==null ) $desc = $feeelement_array[$feeelement_id]["title"];
					list( $desc,$amount ) = parse_refund( $desc,$paiddata[$feeelement_id],$refund_rate );
				} else {
					$amount = 0;
					$desc = null;
				}
			}
		}
		if( $amount>0 || $desc!=null ) {
			$detail[$feeelement_id][] = array(
				'desc' => $desc,
				'amount' => $amount
			);
			$total_fee[$feeelement_id] += $amount;
		}
	}

	// Graduation Fees
	if( $student_dat["graduate_sy"]==$sy_id ) {
		$id = sprintf( '%d:%d', $year,$course_id );
		if( ! isset($gradlist_cache[$id]) ) {
			$gradlist_cache[$id] = array();
			$gradlist->get_list2($year,$course_id);
			for( $i=0; $i<$gradlist->get_numrows(); $i++ ) {
				$gradlist_cache[$id][] = $gradlist->get_fetch_assoc($i);
			}
		}
		foreach( $gradlist_cache[$id] as $ar ) {
			$feeelement_id = $ar["feeelement_id"];
			$detail[$feeelement_id][] = array(
				'desc' => $ar["title"],
				'amount' => $ar["amount"]
			);
			$total_fee[$feeelement_id] += $ar["amount"];
		}
	}

	// Miscellaneous Fee
	if( ! ($scholar["flag"] & SCHOLARFLAG_TOTALLYFREE) ) {
		$id = sprintf( '%d:%d:%d:%d', $feebase_year,$course_id,$year_level,$year );
		if( ! isset($misclist_cache[$id]) ) {
			$misclist_cache[$id] = array();
			$misclist->get_list2($feebase_year,$course_id,$year_level,$year);
			for( $i=0; $i<$misclist->get_numrows(); $i++ ) {
				$misclist_cache[$id][] = $misclist->get_fetch_assoc($i);
			}
		}
		foreach( $misclist_cache[$id] as $ar ) {
			if( ( ($ar["semester_flag"] & (0x01<<$semester)) || (($ar["semester_flag"] & MISCFLAG_ONCEINAYEAR) && $onceinayear) )
				&& ($ar["amount"]>0)
				&& (!($ar["semester_flag"] & MISCFLAG_NEWSTUDENT) || ($new_student==true))
				&& (!($ar["semester_flag"] & MISCFLAG_NOTFOROUTSIDE) || ($outside_student==false)) ) {
				$feeelement_id = $ar["feeelement_id"];
				if( $refund_rate>0 ) {
					if( $feeelement_array[$feeelement_id]["feecategory_id"]==FEECATEGORY_REGISTRATION ) {
						list( $desc,$amount ) = array( $ar["title"],$paiddata[$feeelement_id] );
					} else {
						list( $desc,$amount ) = parse_refund( $ar["title"],$paiddata[$feeelement_id],$refund_rate );
					}
				} else {
					list( $desc,$amount ) = array( $ar["title"],$ar["amount"] );
					if( ($ar["semester_flag"] & MISCFLAG_DOUBLEFORAYEAR) && ($whole_year_student==true) ) $amount *= 2;
				}
				$detail[$feeelement_id][] = array(
					'desc' => $desc,
					'amount' => $amount
				);
				$total_fee[$feeelement_id] += $amount;
			}
		}
	}

	// Class specific fees
	if( is_array($spec_fees) ) {
		foreach( $spec_fees as $ar ) {
			$feeelement_id = $ar["feeelement_id"];
			$detail[$feeelement_id][] = array(
				'desc' => $feeelement_array[$feeelement_id]["title"] . ' (' . $ar["subject_code"] . ')',
				'amount' => $ar["fee_amount"]
			);
			$total_fee[$feeelement_id] += $ar["fee_amount"];
		}
	}
	
	// Additional Fees
	$additionallist->get_list($sy_id,$student_id);
	for( $i=0; $i<$additionallist->get_numrows(); $i++ ) {
		$ar = $additionallist->get_fetch_assoc($i);
		$feeelement_id = $ar["feeelement_id"];
		$detail[$feeelement_id][] = array(
			'desc' => $ar["title"],
			'amount' => $ar["amount"]
		);
		$total_fee[$feeelement_id] += $ar["amount"];
	}

	// Optional fees
	$payment->get_list_optional($sy_id,$student_id);
	for( $i=0; $i<$payment->get_numrows(); $i++ ) {
		$dat = $payment->get_fetch_assoc($i);
		$detail[$dat["feeelement_id"]][] = array(
			'desc' => $dat["title"],
			'amount' => $dat["payment"]
		);
		$total_fee[$dat["feeelement_id"]] += $dat["payment"];
	}

	// result
	$result = array();
	foreach( $feeelement_array as $i=>$v ) {
		if( ($total_fee[$i] > 0) || (count($detail[$i])>0) ) {
			$result[$i] = array(
				"title" => $v["title"],
				"feecategory_id" => $v["feecategory_id"],
				"detail" => $detail[$i],
				"amount" => $total_fee[$i]
			);
		}
	}

	return $result;
}



function calc_assessment_optional( $sy_id, $student_id )
{
	// get student information
	$student = new model_student;
	$student->connect();
	$student->get_by_id( $student_id );
	$student_dat = $student->get_fetch_assoc(0);

	$enrol_student = new model_enrol_student;
	$enrol_student->connect();
	$enrol_student->get_by_id( $sy_id,$student_id );
	if( $enrol_student->get_numrows()==0 ) {
		$enrol_student_dat = array(
			'sy_id' => $sy_id,
			'sy_id_end' => $sy_id,
			'feebase_sy' => $student_dat['feebase_sy'],
			'course_id' => $student_dat['course_id']
		);
	} else {
		$enrol_student_dat = $enrol_student->get_fetch_assoc(0);
	}
	$feebase_year = get_year_from_schoolyear( $enrol_student_dat["feebase_sy"] );
	$course_id = $enrol_student_dat["course_id"];
	$school_id = get_school_id_from_course_id( $course_id );
	$year_level = $enrol_student_dat["year_level"];
	
	// Optional Fee
	$year = get_year_from_schoolyear($sy_id);
	$semester = get_semester_from_schoolyear($sy_id);
	$optlist = new model_optionalfee;
	$optlist->connect();
	$optlist->get_list($sy_id,$school_id);

	$n = $optlist->get_numrows();
	for( $i=0; $i<$n; $i++ ) {
		$ar = $optlist->get_fetch_assoc($i);
		$feeelement_id = $ar["feeelement_id"];
		$detail[$feeelement_id][] = array(
			'desc' => $ar["title"],
			'amount' => $ar["amount"]
		);
		$total_fee[$feeelement_id] += $ar["amount"];
	}

	$feeelement_array = get_feeelement_array(null,0,true);

	$result = array();
	foreach( $feeelement_array as $i=>$v ) {
		if( ($total_fee[$i] > 0) || (count($detail[$i])>0) ) {
			//$description[$i] .= '</table>';
			$result[$i] = array(
				"title" => $v["title"],
				"feecategory_id" => $v["feecategory_id"],
				"detail" => $detail[$i],
				"amount" => $total_fee[$i]
			);
		}
	}

	return $result;
}



?>
