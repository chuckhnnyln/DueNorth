<?php
###lender-history.php###
$target = "testing";

#Shared code between lender-tasks and borrow-tasks
require '../seal_script/_tasks.php';

#Connect to database
require '../seal_script/seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db,$dbname);

#Pull the information of the person making the request from Drupal users
global $user;   // load the user entity so to pick the field from.
$user_fields = user_load($user->uid);  // Check if we're dealing with an authenticated user
if($user->uid) {    // Get field value;
  $arraydays = field_get_items('user', $user_fields, 'field_filter_days');
  $item = array_shift($arraydays);
  $filter_days = $item['value'];
  if ( $filter_days == "" ) { $filter_days = 30; } else { $filter_days = trim($filter_days); }
}

if (isset($_GET['loc'])) {
  $loc = $_GET['loc'];
  #Quick-look into the database to see if this library has any new requests. If so pagemode new!
  $quicklook = "SELECT * FROM seal.`SENYLRC-SEAL2-STATS` where Fill='3' and Destination='$loc'";
  $results = mysqli_query($db,$quicklook);
  $quicklookresults = mysqli_num_rows ($results);
  if ($quicklookresults > 0 ) {
    $pagemode=1; #New
  } else {
    $pagemode=0; #Open
  }
  $filter_yes="yes";
  $filter_no="";
  $filter_noans="yes";
  $filter_expire="";
  $filter_cancel="";
  $filter_sent="yes";
  #$filter_destination="";
  #$filter_illnum="";
} else {
  $loc = $_REQUEST['loc'];
  $pagemode = (isset($_REQUEST['pagemode']) ? $_REQUEST['pagemode'] : "0");
  $filter_yes = (isset($_REQUEST['filter_yes']) ? $_REQUEST['filter_yes'] : "");
  $filter_no = (isset($_REQUEST['filter_no']) ? $_REQUEST['filter_no'] : "");
  $filter_expire = (isset($_REQUEST['filter_expire']) ? $_REQUEST['filter_expire'] : "");
  $filter_cancel = (isset($_REQUEST['filter_cancel']) ? $_REQUEST['filter_cancel'] : "");
  $filter_days = (isset($_REQUEST['filter_days']) ? $_REQUEST['filter_days'] : "");
  $filter_sent = (isset($_REQUEST['filter_sent']) ? $_REQUEST['filter_sent'] : "");
  #$filter_destination = (isset($_REQUEST['filter_destination']) ? $_REQUEST['filter_destination'] : "");
  #$filter_illnum = (isset($_REQUEST['filter_illnum']) ? $_REQUEST['filter_illnum'] : "");
}

displaymodenav("lend",$pagemode,$loc,$target);

#Filter options
if ($pagemode != 1) {
  echo "<form action='$target' method='post'>";
  echo "<input type='hidden' name='loc' value= '$loc'>";
  echo "<input type='hidden' name='pagemode' value= '$pagemode'>";
  echo "<p>Display Requests ";
  if ($pagemode != 0) {
    echo "<input type='checkbox' name='filter_sent' value='yes' " . checked($filter_sent) . ">No  ";
    echo "<input type='checkbox' name='filter_no' value='yes' " . checked($filter_no) . ">No  ";
    echo "<input type='checkbox' name='filter_expire' value='yes' " . checked($filter_expire) . ">Expired  ";
    echo "<input type='checkbox' name='filter_cancel' value='yes' " . checked($filter_cancel) . ">Canceled  ";
  }
  echo "for ";
  echo "<select name='filter_days'>";
  echo "<option value='14' " . selected("14",$filter_days) . ">14 days</option>";
  echo "<option value='30' " . selected("30",$filter_days) . ">30 days</option>";
  echo "<option value='60' " . selected("60",$filter_days) . ">60 days</option>";
  echo "<option value='90' " . selected("90",$filter_days) . ">90 days</option>";
  echo "<option value='all' " . selected("all",$filter_days) . ">all days</option>";
  echo "</select> ";
  echo "<a href='$target?loc=$loc'>clear</a>  ";
  echo "<input type=Submit value=Update><br>";
  #echo "ILL # <input name='filter_illnum' type='text' value='$filter_illnum'>  ";
  #echo "Destination <input name='filter_destination' type='text' value='$filter_destination'>";
  echo "</p>";
  echo "</form>";
}


?>
