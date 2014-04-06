<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/course.inc");
	require_once("../include/feerate.inc");
	require_once("../include/feeelement.inc");
	auth_check( AUTH_ACCOUNTING );

$type_array = array(
	FEERATETYPE_PERUNIT => 'per unit',
	FEERATETYPE_PERSUBJECT => 'per subject',
	FEERATETYPE_PERSEMESTER => 'per semester',
);

function print_edit_form( $dat )
{
	global $type_array;
	$feeelement_array = get_feeelement_array(null,FEEFLAG_FEERATE);
	$depar[0] = " - all -";
	$depar += get_department_array();
	$_SESSION["goback"] = array(
		'page' => 'feeratetitle.php',
		'param' => array( ($dat==null?'add':'edit')=>1, 'feeratetitle_id'=>$dat["feeratetitle_id"] )
	);
	if( (! isset($dat["feeelement_id"])) && isset($_REQUEST["feeelement_id"]) ) {
		$dat["feeelement_id"] = $_REQUEST["feeelement_id"];
	}
	echo '<table border="1">';
	echo '<tr><td>Title</td>';
	echo '<td>'
		. mkhtml_select( "feeelement_id",$feeelement_array,$dat["feeelement_id"] )
		. "<input type=\"button\" value=\"new\" onClick=\"window.open('feeelement.php?add=1&fee_flag=" . FEEFLAG_FEERATE . "','_self');\">"
		. "<input type=\"button\" value=\"edit\" onClick=\"window.open('feeelement.php?edit=1&feeelement_id='+document.mainform.feeelement_id.value,'_self');\">"
		. "<input type=\"button\" value=\"del\" onClick=\"window.open('feeelement.php?del=1&feeelement_id='+document.mainform.feeelement_id.value,'_self');\">"
		. "</td></tr>\n";
	echo '<tr><td>Short name</td><td><input type="text" name="short_name" value="' . $dat["short_name"] . '"></td>' . "</tr>\n";
	echo '<tr><td>Type</td><td>' . mkhtml_select( "feeratetype",$type_array,$dat["feeratetype"] ) . "</td></tr>\n";
	echo '<tr><td>Default value</td><td>' . mkhtml_select( "defaultval",array(0=>"Off",1=>"On"),$dat["defaultval"] ) . "</td></tr>\n";
	echo '<tr><td>Department</td><td>' . mkhtml_select( "department_id",$depar,$dat["department_id"] ) . "</td></tr>\n";
	echo "</table>\n";
}

function print_edit_info( $dat )
{
	global $type_array;
	$feeelement_array = get_feeelement_array(null,FEEFLAG_FEERATE);
	echo '<table border="1">';
	echo '<tr><td>Title</td><td>' . $feeelement_array[$dat["feeelement_id"]] . "</td></tr>\n";
	echo '<tr><td>Short name</td><td>' . $dat["short_name"] . "</td></tr>\n";
	echo '<tr><td>Type</td><td>' . $type_array[$dat["feeratetype"]] . "</td></tr>\n";
	echo '<tr><td>Default value</td><td>' . ($dat["defaultval"]>0  ? "On" : "Off") . "</td></tr>\n";
	echo '<tr><td>Department</td><td>' . mkstr_neat( get_short_department_from_department_id($dat["department_id"]) ) . "</td></tr>\n";
	echo "</table>\n";
}

?>

<html>

<head>
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="base.css" />
<title> Fee Rate Title </title>
</head>

<body>

<?php
	$sy_id = $_SESSION["sy_id"];
	$str_schoolyear = lookup_schoolyear($sy_id);
	print_title( get_office_name($_SESSION["office"]), "Fee Rate Title", $str_schoolyear );

	echo '<form method="POST" name="mainform">';

	if( isset($_REQUEST["add"]) ) {
		if( ! isset($_REQUEST["exec"]) ) {
			echo '<div class="prompt">Enter new fee rate</div>';
			print_edit_form( null );
			echo '<input type="submit" name="exec" value="add">';
			echo '<input type="hidden" name="add">';
		} else {
			$obj = new model_feeratetitle;
			$obj->connect( auth_get_writeable() );
			if( $_REQUEST["department_id"]==0 ) unset($_REQUEST["department_id"]);
			if( $obj->add_auto_compliment( $_REQUEST )==false ) {
				echo '<div class="error">' . $obj->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Added successfully</div>';
			}
		}
	} else if( isset($_REQUEST["edit"]) ) {
		$feeratetitle_id = $_REQUEST["feeratetitle_id"];
		$obj = new model_feeratetitle;
		$obj->connect( auth_get_writeable() );
		if( ! isset($_REQUEST["exec"]) ) {
			$obj->get_by_id( $feeratetitle_id );
			$dat = $obj->get_fetch_assoc(0);
			echo '<div class="prompt">Modify fee rate title data</div>';
			print_edit_form( $dat );
			echo '<input type="submit" name="exec" value="Update">';
			echo '<input type="hidden" name="feeratetitle_id" value="' . $feeratetitle_id .'">';
			echo '<input type="hidden" name="edit">';
		} else {
			if( $_REQUEST["department_id"]==0 ) $_REQUEST["department_id"]='NULL';
			if( $obj->update( $_REQUEST )==false ) {
				echo '<div class="error">' . $obj->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Updated successfully</div>';
			}
		}
	} else if( isset($_REQUEST["del"]) ) {
		$feeratetitle_id = $_REQUEST["feeratetitle_id"];
		$obj = new model_feeratetitle;
		$obj->connect( auth_get_writeable() );
		if( ! isset($_REQUEST["exec"]) ) {
			$obj->get_by_id( $feeratetitle_id );
			$dat = $obj->get_fetch_assoc(0);
			echo '<div class="prompt">Delete following data?</div>';
			print_edit_info( $dat );
			echo '<input type="submit" name="exec" value="Delete">';
			echo '<input type="hidden" name="feeratetitle_id" value="' . $feeratetitle_id .'">';
			echo '<input type="hidden" name="del">';
		} else {
			if( $obj->del( $feeratetitle_id )==false ) {
				echo '<div class="error">' . $obj->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Delete successfully</div>';
			}
		}
	} else {
		$list = new model_feeratetitle;
		$list->connect();
		$list->get_list();

		echo '<table border="1">';
		echo '<tr><th>&nbsp;</th><th>Title</th><th>Short name</th><th>Type</th><th>Default</th><th>Department</th></tr>';
		for( $n=0; $n<$list->get_numrows(); $n++ ) {
			$dat = $list->get_fetch_assoc($n);
			if( ! array_key_exists( $dat["feeratetype"], $type_array ) ) continue;
			echo '<tr>';
			echo '<td><input type="radio" name="feeratetitle_id" value="' . $dat["feeratetitle_id"] . '"></td>';
			echo '<td>' . $dat["title"] . '</td>';
			echo '<td>' . $dat["short_name"] . '</td>';
			echo '<td>' . $type_array[$dat["feeratetype"]] . '</td>';
			echo '<td>' . ($dat["defaultval"] ? "On" : "Off") . '</td>';
			echo '<td>' . mkstr_neat( get_short_department_from_department_id($dat["department_id"]) ) . '</td>';
			echo "</tr>\n";
		}
		echo "</table>\n";
		echo '<input type="submit" name="edit" value="Edit">';
		echo '<input type="submit" name="del" value="Delete">';
		echo '<input type="submit" name="add" value="Add">' . "\n";
	}

	echo "</form>\n";

	print_footer();

	if( isset($_REQUEST["add"]) || isset($_REQUEST["edit"]) || isset($_REQUEST["del"]) ) {
		echo '<form method="POST" id="goback">';
		echo '<input type="submit" value="go back">';
		echo '</form>';
	} else {
		echo "<form action=\"" . $_SESSION["goback_feeratetitle"]["page"] . "\" method=\"POST\" id=\"goback\">";
		print_hidden( $_SESSION["goback_feeratetitle"]["param"] );
		echo "<input type=\"submit\" value=\"go back\">";
		echo "<input type=\"hidden\" name=\"sy_id\" value=\"$sy_id\">";
		echo "</form>\n";
	}
?>

</body>

</html>
