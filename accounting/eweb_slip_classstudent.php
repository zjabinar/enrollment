<?php
session_start();
require_once("../include/auth.inc");
require_once("../include/auth_model.inc"); 
require_once("../include/util.inc");
require_once("../include/semester.inc");
require_once("../include/course.inc");
require_once("../include/student.inc");
require_once("../include/teacher.inc");
require_once("../include/class.inc");
require_once("../include/enrol_student.inc");
require_once("../include/enrol_class.inc");
require_once("../include/classschedule.inc");
//auth_check( $_SESSION["office"] );

$class_id = $_REQUEST["class_id"];

$sched=new model_classschedule;
$sched->connect();
$schedule_array = $sched->get_schedule_array($class_id);
//foreach( $schedule_array as $schedule ) {
//	printf( "Room:%s, Weekday:%s, Time:%s\n", $schedule[0],$schedule[1],$schedule[2] );
//}

?>

<html>

<head>
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title> Subject List </title>

<style type="text/css">
 	/* table { border-style:solid; border-width:thin;}*/
	
	body{ font-family:'times'; font-size:9pt }
	th{ font-family:'Courier New'; font-size:9pt; font-weight:normal }
	td{ font-family:'Courier New'; font-size:9pt }
</style>

</head>

<body>

<?php
	//$size_x = '7.4in';
	$size_x = '100%';
	//$sy_id = $_SESSION["sy_id"];
	$sy_id = $_REQUEST["sy_id"];
	$obj_class = new model_class;
	$obj_class->connect();
	$obj_class->get_by_id( $class_id );
	$dat_class = $obj_class->get_fetch_assoc(0);
	$department_id = $dat_class["department_id"];

	echo '<div style="text-align:center; width:' . $size_x . '">';
	echo $g_schoolname . '<br>';
	echo get_department_from_department_id($department_id) . '<br>';
	echo '<span style="font-weight:bold"> STUDENT LIST </span>';
	echo '</div>';
	
	echo '<table align="center" border="0" style="width:' . $size_x . '" cellpadding=\"0\" cellspacing=\"0\">';
	echo '<tr>';
	echo '<td>' . $dat_class["subject_code"] . ' ' . $dat_class["subject"] . '</td>';
	echo '<td align="right">' . lookup_schoolyear($sy_id) . '</td>';
	echo '</tr>';
	echo '<tr>';
	echo '<td>' . lookup_teacher_name($dat_class["teacher_id"]) . '</td>';
	echo '<td align="right">' . date('M j,Y') . '</td>';
	echo '</tr>';
	$x=0;
	print("<tr><td align=\"left\">");
	foreach( $schedule_array as $schedule ) {
        $x++;
		if ($x==1){
    	    printf( "%s, %s, %s\n", $schedule[1],$schedule[0],$schedule[2] );
   		}else{
			print("&nbsp;&nbsp/&nbsp;&nbsp;");
			printf("%s, %s, %s\n", $schedule[1],$schedule[0],$schedule[2]);
		} 
	}
	print("</td></tr>");
	echo '</table>';

	$list = new model_regist_class;
	$list->connect();
	$list->get_student_list_official($sy_id,$class_id);

	$total_reg = 0;
	$total_nreg = 0;
	

	echo "<table border=\"1\" align=\"center\" style=\"width:$size_x; border-style:solid; border-width:thin; border-collapse:collapse\" cellpadding=\"0\" cellspacing=\"0\">";
	echo "<tr>";
	echo "<th>StudentID</th><th>Name</th></th><th>Course</th><th>YearLevel</th><th>Gender</th>";
	echo "</tr>";
	for( $i=0; $i<$list->get_numrows(); $i++ ) {
		$dat = $list->get_fetch_assoc($i);
		if( $dat["regist_flag"] & REGISTFLAG_REGULAR ) {
			$total_reg++;
		} else {
			$total_nreg++;
		}
		if( ! isset($course_cache[$dat["course_id"]]) ) {
			list($dep,$course,$major,$minor) = get_short_names_from_course_id($dat["course_id"]);
			$course_cache[$dat["course_id"]] = $course . ' ' . $major . ' ' . $minor;
		}
		if(mkstr_neat($dat["gender"])=="M") {
			$gender_m++;
		}elseif(mkstr_neat($dat["gender"])=="F") {
			$gender_f++;
		}
		printf( "<tr>" );
		printf( "<td>". "&nbsp;" ."%s</td> <td>" ."&nbsp;" . "%s</td> <td>" . "&nbsp;". "%s</td> <td align=\"center\">%s</td> <td align=\"center\">%s</td> \n",
			mkstr_student_id($dat["student_id"]),
			mkstr_name_lfm($dat["first_name"],$dat["middle_name"],$dat["last_name"]),
			mkstr_neat( $course_cache[$dat["course_id"]] ),
			lookup_yearlevel( $dat["year_level"] ),
			mkstr_neat($dat["gender"] )
		);
		printf( "</tr>" );
	}
	
	echo "</table>";
	$total = $total_reg + $total_nreg;
	
?>
	<table border=0 align="center" style="width:<?php echo $size_x; ?>">
	<tr><td><?php echo "$total students: " .'(' . "$gender_m M, $gender_f F" . ')' ; ?></td></tr>
	</table>
	<?php $user=auth_get_fullname();?>
		
	<?php
	$teacherid = get_dean_id_from_department_id($department_id);
	$dean_name = lookup_teacher_name($teacherid);
	$dean_position = lookup_teacher_position($teacherid);
	?>
	<br>
	<table cellspacing="0" cellpadding="0" border="0" align="center" style="width:<?php echo $size_x;?>">
	<tr>
		<td width="50%">
			<table border="0" align="left" cellpadding="0" cellspacing="0">
				<tr>
					<td align="left">
						Prepared by:&nbsp;&nbsp;  <?php echo $user;?>
					</td>
				</tr>
				<tr>
					<td align="left">
						&nbsp;
					</td>
				</tr>
			</table>			
		</td>
		<td width="50%">
			<table border="0" align="right" cellpadding="0" cellspacing="0">
				<tr>	
					<td align="center">
						<?php echo $dean_name;?>
					</td>
				</tr>
				<tr>
					<td align="center">
						<?php echo $dean_position;?>						
					</td>
				</tr>			
			</table>
		</td>
	</tr>
	</table>

</body>

</html>
