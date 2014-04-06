<?

require_once 'JSON.php';
$objJsonConverter = new Services_JSON();

$arrResponse = array();

if (isset($_POST["checked"])) {
  $arrResponse["received"] = stripcslashes($_POST["checked"]);
}

// Return response
echo $objJsonConverter->encode($arrResponse);

?>