<?php
#Request details
$target = "modify-status";

#Connect to database
require '../seal_script/seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db,$dbname);

#Pull the information of the person making the request from Drupal users
global $user;   // load the user entity so to pick the field from.
$user_fields = user_load($user->uid);  // Check if we're dealing with an authenticated user
if($user->uid) {    // Get field value;
  $userarray = field_get_items('user', $user_fields, 'field_loc_location_code');
  $item = array_shift($userarray);
  $userloc = $item['value'];
}

if ( isset($_REQUEST['function']) ) {
  #This is coming from the update form.
  $illNUB = (isset($_REQUEST['illNUB']) ? $illNUB = $_REQUEST['illNUB'] : $illNUB = "");
  $function = $_REQUEST['function'];
  $LenderStatus = (isset($_REQUEST['LenderStatus']) ? $LenderStatus = $_REQUEST['LenderStatus'] : $LenderStatus = "");
  $lendnote = (isset($_REQUEST['lendnote']) ? $lendnote = $_REQUEST['lendnote'] : $lendnote = "");
  $lenderprivate = (isset($_REQUEST['lenderprivate']) ? $lenderprivate = $_REQUEST['lenderprivate'] : $lenderprivate = "");
  $lendnote = mysqli_real_escape_string($db,$lendnote);
  $lenderprivate = mysqli_real_escape_string($db,$lenderprivate);
  $sqlupdate = "UPDATE seal.`SENYLRC-SEAL2-STATS` SET LenderPrivate = '$lenderprivate', responderNOTE = '$lendnote', LenderStatus = '$LenderStatus' where `illNUB`= '$illNUB'";
  if (mysqli_query($db, $sqlupdate)) {
    echo "<script type='text/javascript'>location.replace('/lender-tasks?loc=" . $userloc . "&pagemode=0');</script>";
  } else {
    echo "Ooops, something went wrong! Please contact the NNYLN office for help.<br><br>";
    echo $sqlupdate;
  }
} else {
  #This is presenting the update form.
  $illNUB = (isset($_GET['illNUB']) ? $_GET['illNUB'] : "");
  $action = (isset($_GET['a']) ? $_GET['a'] : "");

  if ( strlen($illNUB) > 2 ){
    $sqlget = "SELECT LenderPrivate,responderNOTE,LenderStatus FROM seal.`SENYLRC-SEAL2-STATS` where `illNUB`= '$illNUB'";
    $RequestDetails = mysqli_query($db,$sqlget);
    while ($row = mysqli_fetch_assoc($RequestDetails)) {
      $lendnote = $row["responderNOTE"];
      $lenderprivate = $row["LenderPrivate"];
      $LenderStatus = $row["LenderStatus"];
    }
    if ( strlen($action) > 0 ){
      switch ($action) {
        case "0": #Mark unsent
          $LenderStatus = "";
          break;
        case "1": #Mark sent
          $LenderStatus = "Sent";
          break;
      }
      if ( $LenderStatus == "Sent" ) {
        echo "Changing status of " . $illNUB . " to 'sent'.";
      } else {
        echo "Changing status of " . $illNUB . " to 'unsent'.";
      }
    }
    echo "<form action='$target' method='post'>";
    echo "<input type='hidden' name='function' value= 'update'>";
    echo "<input type='hidden' name='illNUB' value= '$illNUB'>";
    echo "<input type='hidden' name='LenderStatus' value= '$LenderStatus'>";
    echo "<br>Lender Public Note: (Visible to the Borrower)<br>";
    echo "<textarea name='lendnote' rows='4' cols='50'>$lendnote</textarea><br>";
    echo "Lender Private Note: (Visible only your library's staff)<br>";
    echo "<textarea name='lenderprivate' rows='4' cols='50'>$lenderprivate</textarea><br>";
    echo "<input type='submit' value='Submit'>";
    echo "</form>";
  } else {
    echo "Nothing to do!";
  }
}
?>
