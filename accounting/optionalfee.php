<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
	require_once("../include/school.inc");
	require_once("../include/feeelement.inc");
	require_once("../include/optionalfee.inc");
	auth_check( AUTH_ACCOUNTING );
?>

<html>

<head>
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="base.css" />
<title> Optional Fee </title>
</head>

<body>
<?php
	print_heading();
?>
<?php
	$sy_id = $_SESSION["sy_id"];
	$str_schoolyear = lookup_schoolyear($sy_id);
	print_title( get_office_name($_SESSION["office"]), "Optional Fee", $str_schoolyear );

	$school_id = $_REQUEST["school_id"];

	echo '<form method="POST" name="mainform">';
	if( isset($_REQUEST["add"]) ) {
		if( ! isset($_REQUEST["exec"]) ) {
			$_SESSION["goback"] = array(
				'page' => 'optionalfee.php',
				'param' => array( 'school_id'=>$school_id, 'add'=>1 )
			);
			echo '<div class="prompt">Enter new data</div>';
			echo "$year<br>";
			echo lookup_schoolid($school_id) . "<br>";

			echo '<table border="1">';
			$ar = get_feeelement_array( null,FEEFLAG_OPTIONALFEE );
			echo "<tr><td>Title</td><td>"
				. @mkhtml_select("feeelement_id",$ar,$_REQUEST["feeelement_id"])
				. "<input type=\"button\" value=\"new\" onClick=\"window.open('feeelement.php?add=1&fee_flag=" . FEEFLAG_OPTIONALFEE . "','_self');\">"
				. "<input type=\"button\" value=\"edit\" onClick=\"window.open('feeelement.php?edit=1&feeelement_id='+document.mainform.feeelement_id.value,'_self');\">"
				. "<input type=\"button\" value=\"del\" onClick=\"window.open('feeelement.php?del=1&feeelement_id='+document.mainform.feeelement_id.value,'_self');\">"
				. "</td></tr>";
			echo "<tr><td>Amount</td><td><input type=\"text\" name=\"amount\"></td></tr>";
			echo "</table>";
			echo "<input type=\"submit\" name=\"exec\" value=\"add\">";
			echo "<input type=\"hidden\" name=\"add\">";
			echo "<input type=\"hidden\" name=\"school_id\" value=\"" . $school_id . "\">";
		} else {
			$list = new model_optionalfee;
			$list->connect( auth_get_writeable() );
			$_REQUEST["sy_id"] = $sy_id;
			$_REQUEST["amount"] = retrieve_peso($_REQUEST["amount"]);
			if( $list->add( $_REQUEST )==false ) {
				echo '<div class="error">' . $list->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Added successfully</div>';
			}
		}
	} else if( isset($_REQUEST["edit"]) ) {
		if( ! isset($_REQUEST['optionalfee_id']) ) {
			echo '<div class="error">Not selected</div>';
		} else if( ! isset($_REQUEST["exec"]) ) {
			$_SESSION["goback"] = array(
				'page' => 'optionalfee.php',
				'param' => array( 'optionalfee_id'=>$_REQUEST["optionalfee_id"],'school_id'=>$school_id, 'edit'=>1 )
			);
			echo '<div class="prompt">Edit the data</div>';
			echo "$year<br>";
			echo lookup_schoolid($school_id) . "<br>";

			$list = new model_optionalfee;
			$list->connect();
			$dat = $list->get( $_REQUEST["optionalfee_id"] );

			$ar = get_feeelement_array();
			echo '<table border="1">';
			echo "<tr><td>Title</td><td>" . $ar[$dat["feeelement_id"]]
				. "<input type=\"hidden\" name=\"feeelement_id\" value=\"" . $dat["feeelement_id"] . "\">"
				. "<input type=\"button\" value=\"edit\" onClick=\"window.open('feeelement.php?edit=1&feeelement_id='+document.mainform.feeelement_id.value,'_self');\">"
				. "</td></tr>";
			echo "<tr><td>Amount</td><td><input type=\"text\" name=\"amount\" value=\"" . mkstr_peso($dat["amount"]) . "\"></td></tr>";
			echo "</table>";
			echo "<input type=\"submit\" name=\"exec\" value=\"Update\">";
			echo "<input type=\"hidden\" name=\"edit\">";
			echo "<input type=\"hidden\" name=\"optionalfee_id\" value=\"" . $_REQUEST["optionalfee_id"] . "\">";
			echo "<input type=\"hidden\" name=\"school_id\" value=\"" . $school_id . "\">";
		} else {
			$list = new model_optionalfee;
			$list->connect( auth_get_writeable() );
			$_REQUEST["amount"] = retrieve_peso($_REQUEST["amount"]);
			if( $list->update( $_REQUEST )==false ) {
				echo '<div class="error">' . $list->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Updated successfully</div>';
			}
		}
	} else if( isset($_REQUEST["del"]) ) {
		if( ! isset($_REQUEST['optionalfee_id']) ) {
			echo '<div class="error">Not selected</div>';
		} else if( ! isset($_REQUEST["exec"]) ) {
			echo '<div class="prompt">Delete following data?</div>';
			echo "$year<br>";
			echo lookup_schoolid($school_id) . "<br>";

			$list = new model_optionalfee;
			$list->connect();
			$dat = $list->get( $_REQUEST["optionalfee_id"] );

			echo "<table border=\"1\">";
			echo "<tr><th>Title</th><th>Amount</th></tr>";
			echo "<tr><td>" . $dat["title"] . "</td><td>" . mkstr_peso($dat["amount"]) . "</td>";
			echo "</tr>";
			echo "</table>";
			echo "<input type=\"submit\" name=\"del\" value=\"Delete\">";
			echo "<input type=\"hidden\" name=\"exec\">";
			echo "<input type=\"hidden\" name=\"optionalfee_id\" value=\"" . $_REQUEST["optionalfee_id"] . "\">";
			echo "<input type=\"hidden\" name=\"school_id\" value=\"" . $school_id . "\">";
		} else {
			$list = new model_optionalfee;
			$list->connect( auth_get_writeable() );
			if( $list->del( $_REQUEST["optionalfee_id"] )==false ) {
				echo '<div class="error">' . $list->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Deleted successfully</div>';
			}
		}
	} else {
		echo '<div class="prompt">Select School Type</div>';
		$ea = get_schoolid_array();
		echo mkhtml_select( "school_id", $ea, $school_id==0 ? MKHTML_SELECT_NONE : $school_id );
		echo "<input type=\"submit\" value=\"go\">";

		if( $school_id!=0 ) {
			$category_array = get_feecategory_array();
			
			$list = new model_optionalfee;
			$list->connect();

			$list->get_list( $sy_id,$school_id );

			echo "<table border=\"1\">";
			echo "<tr><th></th><th>Title</th><th>Amount</th><th>Category</th></tr>";
			for( $i=0; $i<$list->get_numrows(); $i++ ) {
				$dat = $list->get_fetch_assoc($i);
				echo "<tr>";
				printf( "<td><input type=\"radio\" name=\"optionalfee_id\" value=\"%s\"></td>", $dat["optionalfee_id"] );
				printf( "<td>%s</td><td class=\"peso\">%s</td>", $dat["title"], mkstr_peso($dat["amount"]) );
				echo '<td>' . $category_array[$dat["feecategory_id"]] . '</td>';
				echo "</tr>";
			}
			echo "</table>";
			if( $list->get_numrows()>0 ) {
				echo "<input type=\"submit\" name=\"edit\" value=\"Edit\">";
				echo "<input type=\"submit\" name=\"del\" value=\"Delete\">";
			}
			echo "<input type=\"submit\" name=\"add\" value=\"Add\">";
		}
	}
	echo '</form>';

	print_footer();

	if( isset($_REQUEST["add"]) || isset($_REQUEST["edit"]) || isset($_REQUEST["del"]) ) {
		echo '<form method="POST" id="goback">';
		echo '<input type="submit" value="go back">';
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
