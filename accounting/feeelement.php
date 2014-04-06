<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/school.inc");
	require_once("../include/course.inc");
	require_once("../include/feeelement.inc");
	auth_check( AUTH_ACCOUNTING );
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="base.css" />
<title> Fee Element </title>
</head>

<body>

<?php
	$sy_id = $_SESSION["sy_id"];
	$str_schoolyear = lookup_schoolyear($sy_id);
	print_title( get_office_name($_SESSION["office"]), "Fee Element", $str_schoolyear );

	echo '<form method="POST" name="mainform">';
	if( isset($_REQUEST["add"]) ) {
		if( ! isset($_REQUEST["exec"]) ) {
			$fee_flag = $_REQUEST["fee_flag"];
			$_SESSION["goback_feecategory"] = array(
				'page' => 'feeelement.php',
				'param' => array( 'fee_flag'=>$fee_flag, 'add'=>1 )
			);
			echo '<div class="prompt">Enter new title data</div>';
			echo '<table border="1">';
			echo '<tr><td>Category</td><td>'
				. mkhtml_select( "feecategory_id", get_feecategory_array(null,$fee_flag), $_REQUEST["feecategory_id"] )
				. "<input type=\"button\" value=\"new\" onClick=\"window.open('feecategory.php?add=1&fee_flag=" . $fee_flag . "','_self');\">"
				. "<input type=\"button\" value=\"edit\" onClick=\"window.open('feecategory.php?edit=1&feecategory_id='+document.mainform.feecategory_id.value,'_self');\">"
				. "<input type=\"button\" value=\"del\" onClick=\"window.open('feecategory.php?del=1&feecategory_id='+document.mainform.feecategory_id.value,'_self');\">"
				. '</td></tr>';
			echo '<tr><td>Title</td><td><input type="text" name="title"></td></tr>';
			echo '</table>';
			echo '<input type="submit" name="exec" value="add">';
			echo '<input type="hidden" name="add">';
			echo "<input type=\"hidden\" name=\"fee_flag\" value=\"" . $fee_flag . "\">";
		} else {
			$obj = new model_feeelement;
			$obj->connect( auth_get_writeable() );
			$feeelement_id = $obj->add_auto( $_REQUEST );
			if( $feeelement_id==false ) {
				echo '<div class="error">' . $obj->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Added successfully</div>';
			}
		}
	} else if( isset($_REQUEST["edit"]) ) {
		$feeelement_id = $_REQUEST["feeelement_id"];
		$obj = new model_feeelement;
		$obj->connect( auth_get_writeable() );
		if( ! isset($_REQUEST["exec"]) ) {
			$_SESSION["goback_feecategory"] = array(
				'page' => 'feeelement.php',
				'param' => array( 'edit'=>1, 'feeelement_id'=>$feeelement_id )
			);
			$obj->get_by_id( $feeelement_id );
			$dat = $obj->get_fetch_assoc(0);
			echo '<div class="prompt">Modify title data</div>';
			echo '<table border="1">';
			echo '<tr><td>Category</td><td>'
				. mkhtml_select( "feecategory_id", get_feecategory_array(null,$dat['fee_flag']), $dat["feecategory_id"] )
				. "<input type=\"button\" value=\"edit\" onClick=\"window.open('feecategory.php?edit=1&feecategory_id='+document.mainform.feecategory_id.value,'_self');\">"
				. '</td></tr>';
			echo '<tr><td>Title</td><td><input type="text" name="title" value="' . $dat["title"] . '"></td></tr>';
			echo '</table>';
			echo '<input type="submit" name="exec" value="Update">';
			echo '<input type="hidden" name="feeelement_id" value="' . $feeelement_id .'">';
			echo '<input type="hidden" name="edit">';
			//echo "<input type=\"hidden\" name=\"fee_flag\" value=\"" . $fee_flag . "\">";
		} else {
			if( $obj->update( $_REQUEST )==false ) {
				echo '<div class="error">' . $obj->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Updated successfully</div>';
			}
		}
	} else if( isset($_REQUEST["del"]) ) {
		$feeelement_id = $_REQUEST["feeelement_id"];
		$obj = new model_feeelement;
		$obj->connect( auth_get_writeable() );
		if( ! isset($_REQUEST["exec"]) ) {
			$category_array = get_feecategory_array();
			$obj->get_by_id( $feeelement_id );
			$dat = $obj->get_fetch_assoc(0);
			echo '<div class="prompt">Delete following data?</div>';
			echo '<table border="1">';
			echo '<tr><td>Title</td><td>' . $dat["title"] . '</td></tr>';
			echo '<tr><td>Category</td><td> ' . $category_array[$dat["feecategory_id"]] . '</td></tr>';
			echo '</table>';
			echo '<input type="submit" name="exec" value="Delete">';
			echo '<input type="hidden" name="feeelement_id" value="' . $feeelement_id .'">';
			echo '<input type="hidden" name="del">';
		} else {
			if( $obj->del( $feeelement_id )==false ) {
				echo '<div class="error">' . $obj->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Delete successfully</div>';
			}
		}
	} else {
	}
	echo '</form>';

	print_footer();

	echo "<form action=\"" . $_SESSION["goback"]["page"] . "\" method=\"POST\" id=\"goback\">";
	print_hidden( $_SESSION["goback"]["param"] );
	print_hidden( array('feeelement_id'=>$feeelement_id) );
	echo "<input type=\"submit\" value=\"go back\">";
	echo "<input type=\"hidden\" name=\"sy_id\" value=\"$sy_id\">";
	echo "</form>";
?>

</body>

</html>
