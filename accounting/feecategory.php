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
<title> Fee Category </title>
</head>

<body>

<?php
	$sy_id = $_SESSION["sy_id"];
	$str_schoolyear = lookup_schoolyear($sy_id);
	print_title( get_office_name($_SESSION["office"]), "Fee Category", $str_schoolyear );

	if( isset($_REQUEST["add"]) ) {
		if( ! isset($_REQUEST["exec"]) ) {
			$fee_flag = $_REQUEST["fee_flag"];
			echo '<div class="prompt">Enter new category</div>';
			echo '<form method="POST">';
			echo '<table border="1">';
			echo '<tr><td>Category</td><td><input type="text" name="title"></td></tr>';
			echo '</table>';
			echo '<input type="submit" name="exec" value="add">';
			echo '<input type="hidden" name="add">';
			echo "<input type=\"hidden\" name=\"fee_flag\" value=\"" . $fee_flag . "\">";
			echo '</form>';
		} else {
			$obj = new model_feecategory;
			$obj->connect( auth_get_writeable() );
			$feecategory_id = $obj->add_auto( $_REQUEST );
			if( $feecategory_id==false ) {
				echo '<div class="error">' . $obj->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Added successfully</div>';
			}
		}
	} else if( isset($_REQUEST["edit"]) ) {
		$feecategory_id = $_REQUEST["feecategory_id"];
		$obj = new model_feecategory;
		$obj->connect( auth_get_writeable() );
		if( ! isset($_REQUEST["exec"]) ) {
			$obj->get_by_id( $feecategory_id );
			$dat = $obj->get_fetch_assoc(0);
			echo '<div class="prompt">Modify title data</div>';
			echo '<form method="POST">';
			echo '<table border="1">';
			echo '<tr><td>Category</td><td><input type="text" name="title" value="' . $dat["title"] . '"></td></tr>';
			echo '</table>';
			echo '<input type="submit" name="exec" value="Update">';
			echo '<input type="hidden" name="feecategory_id" value="' . $feecategory_id .'">';
			echo '<input type="hidden" name="edit">';
			//echo "<input type=\"hidden\" name=\"fee_flag\" value=\"" . $fee_flag . "\">";
			echo '</form>';
		} else {
			if( $obj->update( $_REQUEST )==false ) {
				echo '<div class="error">' . $obj->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Updated successfully</div>';
			}
		}
	} else if( isset($_REQUEST["del"]) ) {
		$feecategory_id = $_REQUEST["feecategory_id"];
		$obj = new model_feecategory;
		$obj->connect( auth_get_writeable() );
		if( ! isset($_REQUEST["exec"]) ) {
			$obj->get_by_id( $feecategory_id );
			$dat = $obj->get_fetch_assoc(0);
			echo '<form method="POST">';
			echo '<div class="prompt">Delete following data?</div>';
			echo '<table border="1">';
			echo '<tr><td>Title</td><td>' . $dat["title"] . '</td></tr>';
			echo '</table>';
			echo '<input type="submit" name="exec" value="Delete">';
			echo '<input type="hidden" name="feecategory_id" value="' . $feecategory_id .'">';
			echo '<input type="hidden" name="del">';
			echo '</form>';
		} else {
			if( $obj->del( $feecategory_id )==false ) {
				echo '<div class="error">' . $obj->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Delete successfully</div>';
			}
		}
	} else {
	}

	print_footer();

	echo "<form action=\"" . $_SESSION["goback_feecategory"]["page"] . "\" method=\"POST\" id=\"goback\">";
	print_hidden( $_SESSION["goback_feecategory"]["param"] );
	print_hidden( array("feecategory_id"=>$feecategory_id) );
	echo "<input type=\"submit\" value=\"go back\">";
	echo "<input type=\"hidden\" name=\"sy_id\" value=\"$sy_id\">";
	echo "</form>";
?>

</body>

</html>
