<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/school.inc");
	require_once("../include/course.inc");
	require_once("../include/yearlevel.inc");
	require_once("../include/feerate.inc");
	auth_check( AUTH_ACCOUNTING );
	if( isset($_REQUEST["enter_year"]) ) $_SESSION["enter_year"] = $_REQUEST["enter_year"];
	if( isset($_REQUEST["school_id"]) ) $_SESSION["school_id"] = $_REQUEST["school_id"];
?>

<html>

<head>
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="base.css" />
<title> Fee rate </title>
</head>

<body>
<?php
	print_heading();
?>
<?php
	$sy_id = $_SESSION["sy_id"];
	$str_schoolyear = lookup_schoolyear($sy_id);
	print_title( get_office_name($_SESSION["office"]), "Fee Rate", $str_schoolyear );

	$enter_year = $_REQUEST["enter_year"];
	$school_id = $_REQUEST["school_id"];

	if( isset($_REQUEST["add"]) ) {
		if( ! isset( $_REQUEST["exec"]) ) {
			echo '<form method="POST">';
			echo '<div class="prompt">Enter new data</div>';
			echo "For students entered in <b>$enter_year</b><br>";
			echo '<b>' . lookup_schoolid($school_id) . "</b><br>";
			echo '<table border="0">';
			echo '<tr><td>FeeRate</td><td>' . mkhtml_select( "feeratetitle_id", get_feeratetitle_array(), MKHTML_SELECT_FIRST ) . '</td></tr>';
			$ar = get_department_array( $school_id );
			$ar[0] = " - all - ";
			echo '<tr><td>Department</td><td>' . mkhtml_select( "department_id", $ar, 0 ) . '</td></tr>';
			$ar = get_course_array( $school_id );
			$ar[0] = " - all - ";
			echo '<tr><td>Course</td><td>' . mkhtml_select( "course_id", $ar, 0 ) . '</td></tr>';
			$ar = get_yearlevel_array();
			$ar[0] = " - all - ";
			echo '<tr><td>YearLevel</td><td>' . mkhtml_select( "year_level", $ar, 0 ) . '</td></tr>';
			echo '<tr><td>Amount</td><td><input type="text" name="amount"></td></tr>';
			echo '</table>';
			echo '<input type="submit" name="exec" value="add">';
			echo '<input type="hidden" name="add">';
			echo "<input type=\"hidden\" name=\"enter_year\" value=\"" . $enter_year . "\">";
			echo "<input type=\"hidden\" name=\"school_id\" value=\"" . $school_id . "\">";
			echo '</form>';
		} else {
			if( $_REQUEST["course_id"]>0 ) {
				$_REQUEST["department_id"] = get_department_id_from_course_id( $_REQUEST["course_id"] );
			}
			$list = new model_feerate;
			$list->connect( auth_get_writeable() );
			$dat["id"] = $_REQUEST["feeratetitle_id"]*100*1000*10 + $_REQUEST["department_id"]*1000*10 + $_REQUEST["course_id"]*10 + $_REQUEST["year_level"];
			$dat["amount"] = retrieve_peso($_REQUEST["amount"]);
			if( $list->set( $enter_year,$school_id,$sy_id,$dat )==false ) {
				echo '<div class="error">' . $list->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Successfully updated</div>';
			}
		}
	} else if( isset($_REQUEST["del"]) ) {
		if( ! isset( $_REQUEST["exec"]) ) {
			$feeratetitle = get_feeratetitle_array();
			$feeratetitle_id = intval( $_REQUEST["id"] / (100*1000*10) );
			$department_id = intval( ($_REQUEST["id"] / (1000*10)) % 100 );
			$course_id = intval( ($_REQUEST["id"]/10) % 1000 );
			$year_level = intval( $_REQUEST["id"] % 10 );
			echo '<div class="prompt">Delete following data?</div>';
			echo $feeratetitle[$feeratetitle_id] . '<br>';
			if( $department_id > 0 ) echo get_department_from_department_id( $department_id );
			if( $course_id > 0 ) echo ' ' . get_course_from_course_id( $course_id );
			if( $year_level > 0 ) echo ' ' . lookup_yearlevel( $year_level );
			echo '<form method="POST">';
			echo '<input type="hidden" name="id" value="' . $_REQUEST["id"] . '">';
			echo '<input type="submit" name="exec" value="Delete">';
			echo '<input type="hidden" name="del">';
			echo "<input type=\"hidden\" name=\"enter_year\" value=\"" . $enter_year . "\">";
			echo "<input type=\"hidden\" name=\"school_id\" value=\"" . $school_id . "\">";
			echo '</form>';
		} else {
			$list = new model_feerate;
			$list->connect( auth_get_writeable() );
			$_REQUEST["amount"] = 'NULL';
			if( $list->set( $enter_year,$school_id,$sy_id,$_REQUEST )==false ) {
				echo '<div class="error">' . $list->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Successfully updated</div>';
			}
		}
	} else if( isset($_REQUEST["update"]) ) {
		$list = new model_feerate;
		$list->connect( auth_get_writeable() );

		$list->get_list( $enter_year,$school_id,$sy_id );
		for( $n=0; $n<$list->get_numrows(); $n++ ) {
			$tmp = $list->get_fetch_assoc($n);
			$prev[$tmp["id"]] = $tmp["amount"];
		}

		$result = true;
		foreach( $_REQUEST["feearray"] as $id => $v ) {
			$dat["id"] = $v;
			$dat["amount"] = sprintf( "%d", retrieve_peso($_REQUEST[$v]) );
			if( intval($dat["amount"])!=intval($prev[$dat["id"]]) ) {
				if( $list->set( $enter_year,$school_id,$sy_id,$dat )==false ) {
					$result = false;
					break;
				}
			}
		}
		
		if( $result==false ) {
			echo '<div class="error">' . $list->get_errormsg() . '</div>';
		} else {
			echo '<div class="message">Successfully updated</div>';
		}
	} else {
		echo "<form method=\"POST\">";
		echo '<div class="prompt">Select Student\'s Enterance Year and School Type</div>';
		$ea = get_enteredyear_array();
		echo mkhtml_select( "enter_year", $ea, $enter_year==0 ? MKHTML_SELECT_LAST : $enter_year );
		$ea = get_schoolid_array();
		echo mkhtml_select( "school_id", $ea, $school_id==0 ? MKHTML_SELECT_NONE : $school_id );
		echo "<input type=\"submit\" value=\"go\">";
		echo "</form>";

		if( ($enter_year!=0) && ($school_id!=0) ) {
			echo "<form method=\"POST\">";
			$list = new model_feerate;
			$list->connect();

			$list->get_list( $enter_year,$school_id,$sy_id );

			echo '<div class="prompt">For students entered in year ' . $enter_year . '<br>';
			echo lookup_schoolid($school_id) . "</div>";

			$feeratetitle_array = get_feeratetitle_array(true);

			echo "<table border=\"1\">";
			echo '<tr><th>&nbsp;</th><th>Title</th><th>Amount</th><th>Dep/Course exception</th></tr>';
			for( $i=0; $i<$list->get_numrows(); $i++ ) {
				$dat = $list->get_fetch_assoc($i);
				echo "<tr>";
				echo '<td>';
				if( $dat["course_id"]>0 || $dat["department_id"]>0 || $dat["year_level"]>0 ) {
					echo '<input type="radio" name="id" value="' . $dat["id"] . '">';
				} else {
					echo '&nbsp;';
				}
				echo '</td>';
				echo "<td>" . $feeratetitle_array[$dat["feeratetitle_id"]]["title"] . "</td>";
				echo "<td>";
				echo "<input type=\"hidden\" name=\"feearray[]\" value=\"" . $dat["id"] . "\">";
				echo "<input type=\"text\" name=\"" . $dat["id"] . "\" value=\"" . mkstr_peso($dat["amount"]) . "\" class=\"peso\">";
				echo "</td>";
				echo "<td>";
				if( $dat["department_id"]>0 ) {
					echo get_short_department_from_department_id( $dat["department_id"] );
					if( $dat["course_id"]>0 ) {
						echo ' ' . get_short_course_from_course_id( $dat["course_id"] );
					}
				} else {
					echo "&nbsp;";
				}
				if( $dat["year_level"]>0 ) {
					echo ' ' . lookup_yearlevel( $dat["year_level"] );
				}
				echo "</td>";
				echo "</tr>";
			}
			echo "</table>";
			echo "<input type=\"submit\" name=\"update\" value=\"update all\"><br>";
			echo "<input type=\"submit\" name=\"add\" value=\"add department/course exception\"><br>";
			echo "<input type=\"submit\" name=\"del\" value=\"delete department/course exception\"><br>";
			echo "<input type=\"hidden\" name=\"enter_year\" value=\"" . $enter_year . "\">";
			echo "<input type=\"hidden\" name=\"school_id\" value=\"" . $school_id . "\">";
			echo "</form>";

			echo '<form action="feeratetitle.php" method="POST">';
			echo '<input type="submit" value="Edit class flags">';
			$_SESSION["goback_feeratetitle"] = array(
				'page' => 'feerate.php',
				'param' => array( 'enter_year'=>$enter_year, 'school_id'=>$school_id )
			);
			echo '</form>';
		}
	}

	print_footer();

	if( isset($_REQUEST["update"]) || isset($_REQUEST["add"]) || isset($_REQUEST["del"]) ) {
		echo "<form method=\"POST\" id=\"goback\">";
		echo "<input type=\"submit\" value=\"Go back\">";
		echo "<input type=\"hidden\" name=\"enter_year\" value=\"" . $enter_year . "\">";
		echo "<input type=\"hidden\" name=\"school_id\" value=\"" . $school_id . "\">";
		echo "</form>";
	} else {
		echo "<form action=\"index.php\" method=\"POST\" id=\"goback\">";
		echo "<input type=\"submit\" value=\"Go back\">";
		echo "<input type=\"hidden\" name=\"sy_id\" value=\"$sy_id\">";
		echo "</form>";
	}
?>

</body>

</html>
