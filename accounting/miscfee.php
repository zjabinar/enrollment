<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/school.inc");
	require_once("../include/course.inc");
	require_once("../include/yearlevel.inc");
	require_once("../include/feeelement.inc");
	require_once("../include/miscfee.inc");
	auth_check( AUTH_ACCOUNTING );
	if( isset($_REQUEST["enter_year"]) ) $_SESSION["enter_year"] = $_REQUEST["enter_year"];
	if( isset($_REQUEST["school_id"]) ) $_SESSION["school_id"] = $_REQUEST["school_id"];
?>

<html>

<head>
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="base.css" />
<title> Miscellaneous Fee </title>
<script type="text/javascript">
<!--
function funcOnOnceInAYear() {
	var elem = document.mainform.elements['onceinayear'];
	if( elem ) {
		if( elem.checked ) {
<?php
			foreach( get_semester_array() as $idx=>$val ) {
				echo " document.mainform.elements['$val'].checked = false;\n";
			}
?>
		}
	}
}
//-->
</script>
</head>

<body>
<?php
	print_heading();
?>
<?php
	$sy_id = $_SESSION["sy_id"];
	$str_schoolyear = lookup_schoolyear($sy_id);
	print_title( get_office_name($_SESSION["office"]), "Miscellaneous Fee", $str_schoolyear );

	$enter_year = $_REQUEST["enter_year"];
	$school_id = $_REQUEST["school_id"];

	echo '<form method="POST" name="mainform">';
	if( isset($_REQUEST["add"]) ) {
		if( ! isset($_REQUEST["exec"]) ) {
			$_SESSION["goback"] = array(
				'page' => 'miscfee.php',
				'param' => array( 'enter_year'=>$enter_year, 'school_id'=>$school_id, 'add'=>1 )
			);
			echo '<div class="prompt">Enter new data</div>';
			echo "For students entered in <b>$enter_year</b><br>";
			echo '<b>' . lookup_schoolid($school_id) . "</b><br>";

			echo '<table border="1">';
			$ar = get_feeelement_array( null,FEEFLAG_MISCFEE );
			echo "<tr><td>Title</td><td>"
				. @mkhtml_select("feeelement_id",$ar,$_REQUEST["feeelement_id"])
				. "<input type=\"button\" value=\"new\" onClick=\"window.open('feeelement.php?add=1&fee_flag=" . FEEFLAG_MISCFEE . "','_self');\">"
				. "<input type=\"button\" value=\"edit\" onClick=\"window.open('feeelement.php?edit=1&feeelement_id='+document.mainform.feeelement_id.value,'_self');\">"
				. "<input type=\"button\" value=\"del\" onClick=\"window.open('feeelement.php?del=1&feeelement_id='+document.mainform.feeelement_id.value,'_self');\">"
				. "</td></tr>";
			echo "<tr><td>Amount</td><td><input type=\"text\" name=\"amount\"></td></tr>";
			$ar = get_department_array($school_id);
			$ar[0] = " - all - ";
			echo "<tr><td>Department</td><td>" . mkhtml_select("department_id",$ar,0) . "</td></tr>";
			$ar = get_course_array($school_id);
			$ar[0] = " - all - ";
			echo "<tr><td>Course</td><td>" . mkhtml_select("course_id",$ar,0) . "</td></tr>";
			$ar = get_yearlevel_array();
			$ar[0] = " - all - ";
			echo "<tr><td>YearLevel</td><td>" . mkhtml_select("year_level",$ar,0) . "</td></tr>";
			echo "<tr><td>Semester</td><td>";
			foreach( get_semester_array() as $idx => $value ) {
				echo '<input type="checkbox" name="' . $value . '" checked>' . $value;
			}
			echo ' <input type="checkbox" name="onceinayear" onClick="funcOnOnceInAYear()">Once in a year<br>';
			echo "</td></tr>";
			echo '<tr><td>Option</td><td>';
			echo ' <input type="checkbox" name="flag[]" value="' . MISCFLAG_NEWSTUDENT . '">New student only<br>';
			echo ' <input type="checkbox" name="flag[]" value="' . MISCFLAG_DOUBLEFORAYEAR . '">Doubled amount for those who are enrolled for the whole year<br>';
			echo ' <input type="checkbox" name="flag[]" value="' . MISCFLAG_NOTFOROUTSIDE . '">Exempt for those who are enrolled in outside of campus<br>';
			echo '</td></tr>';
			echo "</table>";
			echo "<input type=\"submit\" name=\"exec\" value=\"add\">";
			echo "<input type=\"hidden\" name=\"add\">";
			echo "<input type=\"hidden\" name=\"enter_year\" value=\"" . $enter_year . "\">";
			echo "<input type=\"hidden\" name=\"school_id\" value=\"" . $school_id . "\">";
		} else {
			if( $_REQUEST["course_id"]>0 ) $_REQUEST["department_id"] = get_department_id_from_course_id( $_REQUEST["course_id"] );
			$list = new model_miscfee;
			$list->connect( auth_get_writeable() );
			$_REQUEST["amount"] = sprintf( "%d", retrieve_peso($_REQUEST["amount"]) );
			$_REQUEST["effective_year"] = get_year_from_schoolyear($sy_id);
			$_REQUEST["semester_flag"] = 0;
			foreach( get_semester_array() as $idx => $value ) {
				if( isset($_REQUEST[$value]) ) $_REQUEST["semester_flag"] |= 0x01<<$idx;
			}
			if( isset($_REQUEST['onceinayear']) ) $_REQUEST['semester_flag'] = MISCFLAG_ONCEINAYEAR;
			if( count($_REQUEST["flag"]) > 0 ) {
				foreach( $_REQUEST["flag"] as $idx => $value ) $_REQUEST["semester_flag"] |= $value;
			}
			if( $list->set( $_REQUEST )==false ) {
				echo '<div class="error">' . $list->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Added successfully</div>';
			}
		}
	} else if( isset($_REQUEST["edit"]) ) {
		if( ! isset($_REQUEST['miscfee_id']) ) {
			echo '<div class="error">Not selected</div>';
		} else if( ! isset($_REQUEST["exec"]) ) {
			$_SESSION["goback"] = array(
				'page' => 'miscfee.php',
				'param' => array( 'miscfee_id'=>$_REQUEST["miscfee_id"], 'enter_year'=>$enter_year, 'school_id'=>$school_id, 'edit'=>1 )
			);
			echo '<div class="prompt">Edit the data</div>';
			echo $enter_year . '<br>';
			echo lookup_schoolid($school_id). '<br>';

			$list = new model_miscfee;
			$list->connect();
			$dat = $list->get( $_REQUEST["miscfee_id"] );

			echo '<table border="1">';
			$ar = get_feeelement_array();
			echo "<tr><td>Title</td><td>" . $ar[$dat["feeelement_id"]]
				. "<input type=\"hidden\" name=\"feeelement_id\" value=\"" . $dat["feeelement_id"] . "\">"
				. "<input type=\"button\" value=\"edit\" onClick=\"window.open('feeelement.php?edit=1&feeelement_id='+document.mainform.feeelement_id.value,'_self');\">"
				. "</td></tr>";
			echo "<tr><td>Amount</td><td><input type=\"text\" name=\"amount\" value=\"" . mkstr_peso($dat["amount"]) . "\"></td></tr>";
			echo "<tr><td>Semester</td><td>";
			foreach( get_semester_array() as $idx => $value ) {
				echo '<input type="checkbox" name="' . $value . '"' . ($dat["semester_flag"] & (0x01<<$idx) ? "checked" : "") . '>' . $value;
			}
			echo ' <input type="checkbox" name="onceinayear"' . ($dat['semester_flag'] & MISCFLAG_ONCEINAYEAR ? ' checked' : '') . ' onClick="funcOnOnceInAYear()">Once in a year<br>';
			echo "</td></tr>";
			echo '<tr><td>Option</td><td>';
			echo ' <input type="checkbox" name="flag[]" value="' . MISCFLAG_NEWSTUDENT . '"' . ($dat["semester_flag"] & MISCFLAG_NEWSTUDENT ? " checked" : "") . '>New student only<br>';
			echo ' <input type="checkbox" name="flag[]" value="' . MISCFLAG_DOUBLEFORAYEAR . '"' . ($dat["semester_flag"] & MISCFLAG_DOUBLEFORAYEAR ? " checked" : "") . '>Doubled amount for those who are enrolled for the whole year<br>';
			echo ' <input type="checkbox" name="flag[]" value="' . MISCFLAG_NOTFOROUTSIDE . '"' . ($dat["semester_flag"] & MISCFLAG_NOTFOROUTSIDE ? " checked" : "") . '>Exempt for those who are enrolled in outside of campus<br>';
			echo '</td></tr>';
			echo "<tr><td>Dep/Course exception</td><td>";
			if( $dat["department_id"]>0 ) {
				echo get_short_department_from_department_id( $dat["department_id"] );
				if( $dat["course_id"]>0 ) {
					echo ' ' . get_short_course_from_course_id( $dat["course_id"] );
				}
			} else {
				echo "&nbsp;";
			}
			if( $dat["year_level"] ) {
				echo ' ' . lookup_yearlevel( $dat["year_level"] );
			}
			echo "</td></tr>";
			echo "</table>";
			echo "<input type=\"submit\" name=\"exec\" value=\"Update\">";
			echo "<input type=\"hidden\" name=\"edit\">";
			echo "<input type=\"hidden\" name=\"miscfee_id\" value=\"" . $_REQUEST["miscfee_id"] . "\">";
			echo "<input type=\"hidden\" name=\"enter_year\" value=\"" . $enter_year . "\">";
			echo "<input type=\"hidden\" name=\"school_id\" value=\"" . $school_id . "\">";
		} else {
			$list = new model_miscfee;
			$list->connect( auth_get_writeable() );

			$year = get_year_from_schoolyear($sy_id);
			$dat = $list->get( $_REQUEST["miscfee_id"] );
			$dat["feeelement_id"] = $_REQUEST["feeelement_id"];
			$dat["amount"] = sprintf( "%d", retrieve_peso($_REQUEST["amount"]) );
			$dat["semester_flag"] = 0;
			foreach( get_semester_array() as $idx => $value ) {
				if( isset($_REQUEST[$value]) ) $dat["semester_flag"] |= 0x01<<$idx;
			}
			if( isset($_REQUEST['onceinayear']) ) $dat['semester_flag'] = MISCFLAG_ONCEINAYEAR;
			if( count($_REQUEST["flag"]) > 0 ) {
				foreach( $_REQUEST["flag"] as $idx => $value ) $dat["semester_flag"] |= $value;
			}
			$dat["effective_year"] = $year;
			$result = $list->set( $dat );
			if( $result==false ) {
				echo '<div class="error">' . $list->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Updated successfully</div>';
			}
		}
	} else if( isset($_REQUEST["del"]) ) {
		if( ! isset($_REQUEST['miscfee_id']) ) {
			echo '<div class="error">Not selected</div>';
		} else if( ! isset($_REQUEST["exec"]) ) {
			$feecategory_array = get_feecategory_array();

			echo '<div class="prompt">Delete following data?</div>';
			echo $enter_year . '<br>';
			echo lookup_schoolid($school_id) . "<br>";

			$list = new model_miscfee;
			$list->connect();
			$dat = $list->get( $_REQUEST["miscfee_id"] );

			echo "<table border=\"1\">";
			echo "<tr><th>Title</th><th>Amount</th><th>Semester</th><th>Category</th><th>Dep/Course exception</th></tr>";
			echo "<tr><td>" . $dat["title"] . "</td><td>" . mkstr_peso($dat["amount"]) . "</td>";
			echo "<td>";
			$j = 0;
			foreach( get_semester_array() as $idx => $value ) {
				if( $j>0 ) echo '/';
				if( $dat["semester_flag"] & (0x01<<$idx) ) {
					echo $value;
				} else {
					echo '-';
				}
				$j++;
			}
			if( $dat["semester_flag"] & MISCFLAG_ONCEINAYEAR ) echo " Once in a year";
			echo "</td>";
			echo "<td>" . $feecategory_array[$dat["feecategory_id"]] . "</td>";
			echo "<td>";
			if( $dat["semester_flag"] & MISCFLAG_NEWSTUDENT ) echo "New student only ";
			if( $dat["semester_flag"] & MISCFLAG_DOUBLEFORAYEAR ) echo "Double for whole year ";
			if( $dat["semester_flag"] & MISCFLAG_NOTFOROUTSIDE ) echo "Exempt for outside ";
			if( $dat["department_id"]>0 ) {
				echo get_short_department_from_department_id( $dat["department_id"] );
				if( $dat["course_id"]>0 ) {
					echo " " . get_short_course_from_course_id( $dat["course_id"] );
				}
			} else {
				echo "&nbsp;";
			}
			if( $dat["year_level"] ) {
				echo ' ' . lookup_yearlevel( $dat["year_level"] );
			}
			echo "</td></tr>";
			echo "</table>";
			echo "<input type=\"submit\" name=\"del\" value=\"Delete\">";
			echo "<input type=\"hidden\" name=\"exec\">";
			echo "<input type=\"hidden\" name=\"miscfee_id\" value=\"" . $_REQUEST["miscfee_id"] . "\">";
			echo "<input type=\"hidden\" name=\"enter_year\" value=\"" . $enter_year . "\">";
			echo "<input type=\"hidden\" name=\"school_id\" value=\"" . $school_id . "\">";
		} else {
			$list = new model_miscfee;
			$list->connect( auth_get_writeable() );
			
			$year = get_year_from_schoolyear($sy_id);
			$dat = $list->get( $_REQUEST["miscfee_id"] );
			$dat["effective_year"] = $year;
			$dat["amount"] = 'NULL';
			$result = $list->set( $dat );
			if( $result==false ) {
				echo '<div class="error">' . $list->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Deleted successfully</div>';
			}
		}
	} else {
		echo '<div class="prompt">Select Student\'s Enterance Year and School Type</div>';
		$ea = get_enteredyear_array();
		echo mkhtml_select( "enter_year", $ea, $enter_year==0 ? MKHTML_SELECT_LAST : $enter_year );
		$ea = get_schoolid_array();
		echo mkhtml_select( "school_id", $ea, $school_id==0 ? MKHTML_SELECT_NONE : $school_id );
		echo "<input type=\"submit\" value=\"go\">";

		if( ($enter_year!=0) && ($school_id!=0) ) {
			$feecategory_array = get_feecategory_array();

			$list = new model_miscfee;
			$list->connect();

			$list->get_list( $enter_year,$school_id,get_year_from_schoolyear($sy_id) );

			echo '<div class="prompt">For students entered in year ' . $enter_year . '<br>';
			echo lookup_schoolid($school_id) . "</div>";

			echo "<table border=\"1\">";
			echo "<tr><th></th><th>Title</th><th>Amount</th><th>Semester</th><th>Category</th><th>Dep/Course exception</th></tr>";
			for( $i=0; $i<$list->get_numrows(); $i++ ) {
				$dat = $list->get_fetch_assoc($i);
				echo "<tr>";
				printf( "<td><input type=\"radio\" name=\"miscfee_id\" value=\"%s\"></td>", $dat["miscfee_id"] );
				printf( "<td>%s</td>", $dat["title"] );
				printf( "<td class=\"peso\">%s</td>", mkstr_peso($dat["amount"]) );
				printf( "<td>" );
				$j = 0;
				foreach( get_semester_array() as $idx => $value ) {
					if( $j>0 ) echo '/';
					if( $dat["semester_flag"] & (0x01<<$idx) ) {
						echo $value;
					} else {
						echo '-';
					}
					$j++;
				}
				if( $dat["semester_flag"] & MISCFLAG_ONCEINAYEAR ) echo " Once in a year";
				echo "</td>";
				echo "<td>" . $feecategory_array[$dat["feecategory_id"]] . "</td>";
				echo "<td>";
				if( $dat["semester_flag"] & MISCFLAG_NEWSTUDENT ) echo "New student only ";
				if( $dat["semester_flag"] & MISCFLAG_DOUBLEFORAYEAR ) echo "Double for whole year ";
				if( $dat["semester_flag"] & MISCFLAG_NOTFOROUTSIDE ) echo "Exempt for outside ";
				if( $dat["department_id"]>0 ) {
					echo get_short_department_from_department_id( $dat["department_id"] );
					if( $dat["course_id"]>0 ) {
						echo ' ' . get_short_course_from_course_id( $dat["course_id"] );
					}
				} else {
					echo "&nbsp;";
				}
				if( $dat["year_level"] ) {
					echo ' ' . lookup_yearlevel( $dat["year_level"] );
				}
				echo "</td>";
				echo "</tr>\n";
			}
			echo "</table>";
			echo "<input type=\"submit\" name=\"edit\" value=\"Edit\">";
			echo "<input type=\"submit\" name=\"del\" value=\"Delete\">";
			echo "<input type=\"submit\" name=\"add\" value=\"Add\">";
		}
	}
	echo '</form>';

	print_footer();

	if( isset($_REQUEST["add"]) || isset($_REQUEST["edit"]) || isset($_REQUEST["del"]) ) {
		echo '<form method="POST" id="goback">';
		echo '<input type="submit" value="go back">';
		echo "<input type=\"hidden\" name=\"enter_year\" value=\"" . $enter_year . "\">";
		echo "<input type=\"hidden\" name=\"school_id\" value=\"" . $school_id . "\">";
		echo '</form>';
	} else {
		echo "<form action=\"index.php\" method=\"POST\" id=\"goback\">";
		echo "<input type=\"submit\" value=\"go back\">";
		echo "<input type=\"hidden\" name=\"sy_id\" value=\"$sy_id\">";
		echo "</form>";
	}
?>

</body>

</html>
