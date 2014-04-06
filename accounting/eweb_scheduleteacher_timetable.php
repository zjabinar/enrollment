<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/course.inc");
	require_once("../include/teacher.inc");
	require_once("../include/class.inc");
	require_once("../include/classschedule.inc");
	require_once("../include/yearlevel.inc");
	require_once("../include/view_schedule.inc");
	//auth_check( $_SESSION["office"] );
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="base.css" />
<title> Class list of teachers </title>
<style type="text/css">
<!--
	caption{ font-weight:bold; text-align:left; }
-->
</style>
</head>

<body>

<?php
	//$sy_id = $_SESSION["sy_id"];
	$sy_id = $_REQUEST["sy_id"];
	$str_schoolyear = lookup_schoolyear($sy_id);
	if( isset($_SESSION["department_id"]) ) {
		$department_id = $_SESSION["department_id"];
	} else {
	}
	
	//print_title( get_office_name($_SESSION["office"]), "Schedule of teachers", $str_schoolyear );

	echo '<form method="POST">';

	$teacher_id = $_REQUEST["teacher_id"];
	//$teacher_id = $_SESSION["teacher_id"];
	//echo '<div class="prompt">Select a teacher</div>';
	//$teacher_array[0] = ' - no teacher - ';
	//$teacher_array += get_teacher_array($department_id,$sy_id);
	//echo mkhtml_select( "teacher_id", $teacher_array, $teacher_id );
	//echo '<input type="submit" value="View"><br>';

	if( isset($_REQUEST["teacher_id"]) ) {
		$list = new model_class;
		$list->connect();
		if( $teacher_id==0 ) {
			// If no teacher, lets get the list only from the department
			$list->get_list_by_teacher( $sy_id,$teacher_id,$department_id );
		} else {
			$list->get_list_by_teacher( $sy_id,$teacher_id );
		}
		
		$list_schedule = new model_classschedule;
		$list_schedule->connect();
		$list_schedule->get_list_of_teacher($sy_id,$teacher_id);
		list($total_reg,$total_ireg) = calc_classschedule_total_time( $list_schedule );
		//echo 'Total regular load ' . ($total_reg/60) . ' hours.<br>Total extra load ' . ($total_ireg/60) . ' hours.<br>';

		//echo '<br>';
		print_classschedule_table( $list_schedule, "Time table" );

		//if( $department_id>0 ) {
		//	echo "<input type=\"button\" value=\"Faculty Load Slip (" . get_department_from_department_id($department_id) . ")\" onclick=\"window.open('slip_facultyload.php?sy_id=$sy_id&department_id=$department_id&teacher_id=$teacher_id','_blank');\"><br>";
		//}
		//echo "<input type=\"button\" value=\"Faculty Load Slip\" onclick=\"window.open('slip_facultyload.php?sy_id=$sy_id&teacher_id=$teacher_id','_blank');\">";
	}

	echo '</form>';

	//print_footer();

	//echo '<form action="index.php" method="POST" id="goback">';
	//echo '<input type="submit" value="Go back">';
	//echo '<input type="hidden" name="sy_id" value="' . $sy_id . '">';
	//echo '</form>';
?>

</body>

</html>
