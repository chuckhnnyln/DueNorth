<?php
###borrower-tasks.php###
$target = "borrower-tasks";
$imagepath = "/sites/duenorth.nnyln.org/files/interface/";
$hasnew = "";

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
  $pagemode = (isset($_REQUEST['pagemode']) ? $_REQUEST['pagemode'] : "0");
  $filter_yes="yes";
  $filter_no="yes";
  $filter_noans="yes";
  $filter_expire="yes";
  $filter_cancel="yes";
  $filter_sent="yes";
  #$filter_destination="";
  #$filter_illnum="";
} else {
  $loc = $_REQUEST['loc'];
  $pagemode = (isset($_REQUEST['pagemode']) ? $_REQUEST['pagemode'] : "0");
  $filter_yes = (isset($_REQUEST['filter_yes']) ? $_REQUEST['filter_yes'] : "");
  $filter_no = (isset($_REQUEST['filter_no']) ? $_REQUEST['filter_no'] : "");
  $filter_expire = (isset($_REQUEST['filter_expire']) ? $_REQUEST['filter_expire'] : "");
  $filter_noans = (isset($_REQUEST['filter_noans']) ? $_REQUEST['filter_noans'] : "");
  $filter_cancel = (isset($_REQUEST['filter_cancel']) ? $_REQUEST['filter_cancel'] : "");
  $filter_days = (isset($_REQUEST['filter_days']) ? $_REQUEST['filter_days'] : "");
  $filter_sent = (isset($_REQUEST['filter_sent']) ? $_REQUEST['filter_sent'] : "");
}

displaymodenav("borrow",$pagemode,$loc,$target,$hasnew);

#Filter options
echo "<form action='$target' method='post'>";
echo "<input type='hidden' name='loc' value= '$loc'>";
echo "<input type='hidden' name='pagemode' value= '$pagemode'>";
echo "<p>Display Requests ";
if ($pagemode == 0) { #Open
  echo "<input type='checkbox' name='filter_yes' value='yes' " . checked($filter_yes) . ">Yes  ";
  echo "<input type='checkbox' name='filter_noans' value='yes' " . checked($filter_noans) . ">No Answer  ";
  echo "<input type='checkbox' name='filter_sent' value='yes' " . checked($filter_sent) . ">Arrived  ";
}
if ($pagemode == 2) { #Complete
  echo "<input type='checkbox' name='filter_expire' value='yes' " . checked($filter_expire) . ">Expired  ";
  echo "<input type='checkbox' name='filter_cancel' value='yes' " . checked($filter_cancel) . ">Canceled  ";
  echo "<input type='checkbox' name='filter_sent' value='yes' " . checked($filter_sent) . ">Returned  ";
}
echo "for ";
echo "<select name='filter_days'>";
echo "<option value='14' " . selected("14",$filter_days) . ">14 days</option>";
echo "<option value='30' " . selected("30",$filter_days) . ">30 days</option>";
echo "<option value='60' " . selected("60",$filter_days) . ">60 days</option>";
echo "<option value='90' " . selected("90",$filter_days) . ">90 days</option>";
echo "<option value='all' " . selected("all",$filter_days) . ">all days</option>";
echo "</select> ";
echo "<a href='$target?loc=$loc&pagemode=$pagemode'>clear</a>  ";
echo "<input type=Submit value=Update><br>";
#echo "ILL # <input name='filter_illnum' type='text' value='$filter_illnum'>  ";
#echo "Destination <input name='filter_destination' type='text' value='$filter_destination'>";
echo "</p>";
echo "</form>";

$getsql = buildsql("borrow",$pagemode,$loc,$filter_yes,$filter_no,$filter_expire,$filter_cancel,$filter_days,$filter_sent,$filter_noans,$sealSTAT);
echo "<br>" . $getsql;

$Getlist = mysqli_query($db,$getsql);
$GetListCount = mysqli_num_rows ($Getlist);
if ( $GetListCount > 0 ) {
  echo "<br>$GetListCount results<br>";
  echo "<table><TR><TH width='5%'>ILL #</TH><TH>&nbsp</TH><TH width='25%'>Title / Author</TH><TH>Need By</TH><TH>Lender Destination & Contact</TH><TH>Timestamp</TH><TH>Borrower</TH><TH>Status</TH><TH width=10%>Actions</TH></TR>";
  $rowtype=1;
  while ($row = mysqli_fetch_assoc($Getlist)) {
    $illNUB = $row["illNUB"];
    $title = $row["Title"];
    $author = $row["Author"];
    $reqnote = $row["reqnote"];
    $lendnote = $row["responderNOTE"];
    $needby = $row["needbydate"];
    $borrowerprivate = $row["BorrowerPrivate"];
    $dest = $row["Destination"];
    $reqp = $row["Requester person"];
    #$reql = $row["Requester lib"];
    #$reqemail = $row["requesterEMAIL"];
    $timestamp = $row["Timestamp"];
    $fill = $row["Fill"];
    if($fill=="1") $fill="Yes";
    if($fill=="0") $fill="No Fill";
    if($fill=="3") $fill="No Answer";
    if($fill=="4") $fill="Expired";
    if($fill=="6") $fill="Canceled";
    $borrowerstatus = $row["BorrowerStatus"];
    if($fill=="1" && $borrowerstatus=="Arrived") $fill="Arrived";
    if($fill=="1" && $borrowerstatus=="Returned") $fill="Returned";
    $dest=trim($dest);
    #Are there comments?
    if ( (strlen($lendnote)>2) || (strlen($reqnote)>2) || (strlen($borrowerprivate)>2) ) {
      $comments="<br><img src='/sites/duenorth.nnyln.org/files/interface/comment.png' width='20'>";
    } else {
      $comments="";
    }
    #Get the Destination Name
    if (strlen($dest)>2){
      $GETLISTSQLDEST="SELECT`Name`,`ILL Email` FROM `SENYLRC-SEAL2-Library-Data` where loc like '$dest'  limit 1";
      $resultdest=mysqli_query($db, $GETLISTSQLDEST);
      while ($rowdest = mysqli_fetch_assoc($resultdest)) {
        $dest=$rowdest["Name"];
        $destemail=$rowdest["ILL Email"];
      }
    }else{
      $dest="Error No Library Selected";
    }
    if ( $rowtype & 1 ) {
      $rowclass="odd";
    } else {
      $rowclass="even";
    }
    #$displaynotes=build_notes($reqnote,$lendnote);
    switch ($fill) {
      case "Yes":
        #Actions: edit public note
        echo "<TR class='$rowclass'><TD><a href='request-details?illNUB=$illNUB'>$illNUB</a></TD><TD><a href='request-details?illNUB=$illNUB&print=1'><img src='/sites/duenorth.nnyln.org/files/interface/print.png' width='20'></a>$comments</TD><TD>$title</br><i>$author</i></TD><TD>$needby</TD><TD>$reqp</br><a href='mailto:$reqemail?Subject=NOTE Request ILL# $illNUB' target='_blank'>$reql</a></TD><TD>$timestamp</TD><TD>$fill</TD><TD><a href='/modify-status?illNUB=$illNUB&a=1'>Mark Sent</a><br><a href='https://duenorth.nnyln.org/respond?num=$illNUB&a=0'>Answer No</a></TD></TR> ";
        break;
      case "No Fill":
        #Action: Edit public note
        echo "<TR class='$rowclass'><TD><a href='request-details?illNUB=$illNUB'>$illNUB</a></TD><TD><a href='request-details?illNUB=$illNUB&print=1'><img src='/sites/duenorth.nnyln.org/files/interface/print.png' width='20'></a>$comments</TD><TD>$title</br><i>$author</i></TD><TD>$needby</TD><TD>$reqp</br><a href='mailto:$reqemail?Subject=NOTE Request ILL# $illNUB' target='_blank'>$reql</a></TD><TD>$timestamp</TD><TD>$fill</TD><TD><a href='/modify-status?illNUB=$illNUB'>Edit Notes</a></TD></TR> ";
        break;
      case "No Answer":
        #Actions: Respond yes, respond no
        echo "<TR class='$rowclass'><TD><a href='request-details?illNUB=$illNUB'>$illNUB</a></TD><TD><a href='request-details?illNUB=$illNUB&print=1'><img src='/sites/duenorth.nnyln.org/files/interface/print.png' width='20'></a>$comments</TD><TD>$title</br><i>$author</i></TD><TD>$needby</TD><TD>$reqp</br><a href='mailto:$reqemail?Subject=NOTE Request ILL# $illNUB' target='_blank'>$reql</a></TD><TD>$timestamp</TD><TD>$fill</TD><TD><a href='https://duenorth.nnyln.org/respond?num=$illNUB&a=1'>Yes</a><br><br><a href='https://duenorth.nnyln.org/respond?num=$illNUB&a=0'>No</a></TD></TR> ";
        break;
      case "Sent":
        #Actions: Reopen
        echo "<TR class='$rowclass'><TD><a href='request-details?illNUB=$illNUB'>$illNUB</a></TD><TD><a href='request-details?illNUB=$illNUB&print=1'><img src='/sites/duenorth.nnyln.org/files/interface/print.png' width='20'></a>$comments</TD><TD>$title</br><i>$author</i></TD><TD>$needby</TD><TD>$reqp</br><a href='mailto:$reqemail?Subject=NOTE Request ILL# $illNUB' target='_blank'>$reql</a></TD><TD>$timestamp</TD><TD>$fill</TD><TD><a href='/modify-status?illNUB=$illNUB&a=0'>Mark Unsent</a><br><a href='/modify-status?illNUB=$illNUB'>Edit Notes</a></TD></TR> ";
        break;
      case "Expired":
        #Actions: None
        echo "<TR class='$rowclass'><TD><a href='request-details?illNUB=$illNUB'>$illNUB</a></TD><TD><a href='request-details?illNUB=$illNUB&print=1'><img src='/sites/duenorth.nnyln.org/files/interface/print.png' width='20'></a>$comments</TD><TD>$title</br><i>$author</i></TD><TD>$needby</TD><TD>$reqp</br><a href='mailto:$reqemail?Subject=NOTE Request ILL# $illNUB' target='_blank'>$reql</a></TD><TD>$timestamp</TD><TD>$fill</TD><TD>&nbsp</TD></TR> ";
        break;
      case "Canceled":
        #Actions None
        echo "<TR class='$rowclass'><TD><a href='request-details?illNUB=$illNUB'>$illNUB</a></TD><TD><a href='request-details?illNUB=$illNUB&print=1'><img src='/sites/duenorth.nnyln.org/files/interface/print.png' width='20'></a>$comments</TD><TD>$title</br><i>$author</i></TD><TD>$needby</TD><TD>$reqp</br><a href='mailto:$reqemail?Subject=NOTE Request ILL# $illNUB' target='_blank'>$reql</a></TD><TD>$timestamp</TD><TD>$fill</TD><TD>&nbsp</TD></TR> ";
        break;
      case "Arrived":
        #Actions: mark returned, edit notes
        break;
      case "Returned":
        #actions:
        break;
    }
    $rowtype = $rowtype + 1;
  }
  echo "</table>";
} else {
  echo "<br>Nothing to see here! Move along!";
}
?>
