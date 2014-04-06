
<?php
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
	require_once("../include/enrol_student.inc");

if ( isset($_REQUEST['btnSave']) ) {

	

}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<link rel="stylesheet" type="text/css" href="base.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<style type="text/css">
<!--
input.menu { width:20em; text-align:left; padding-left:1em; padding-right:1em }
-->


</style>
<style type="text/css" media="screen">
<!--
@import url("p7tp/p7tp_01.css");
-->
</style>
<title>Assessment Remarks</title>
</head>

<body>

<form method="post" action="">
<?php
$sy_id = $_REQUEST['sy_id'];
$student_id = $_REQUEST['student_id'];



$student = new model_enrol_student;
$student->connect();
$student->get_info($sy_id,$student_id);
$dat = $student->get_fetch_assoc(0);
$str_schoolyear = lookup_schoolyear($sy_id);
echo '<br /><br />';
echo '<table border="0" align="center" width="400">';
echo '<tr>';
echo '<td>' . mkstr_name_lfm($dat["first_name"],$dat["middle_name"],$dat["last_name"]) . '</td><td align="right">' . $str_schoolyear . '</td></tr>';

?>
<tr>
<td colspan="2">

  <textarea name="txt_remarks" cols="50" rows="5"></textarea>
  <br />
  
</td>
</tr><tr><td>
  <input type="submit" name="btnClose" value="  Close  " onClick="window.close()"/>

  <input type="submit" name="btnSave" value="   Save  " /></td><td align="right"> * Assessment Remarks</td>
</tr>
</table>
</form>
</body>
</html>
