<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>

  <script>
  $(document).ready(function() {
    $("#datepicker").datepicker();
     $("#datepicker2").datepicker();
  });
  </script>

<?php

#Connect to database
require '../seal_script/seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
$csvpath="/var/www/seal/sites/duenorth.nnyln.org/files/csv";

function elementHunt($startdated, $hunting) {
  switch ($hunting) {
    case "D":
      $hunted = substr($startdated, 3, 2);
      break;
    case "M":
      $hunted = substr($startdated, 0, 2);
      break;
    case "Y":
      $hunted = substr($startdated, 6, 4);
      break;
  }
  return $hunted;
}

function convertDate($InputDate){
  $Y = elementHunt($InputDate,"Y");
  $M = elementHunt($InputDate,"M");
  $D = elementHunt($InputDate,"D");
  $OutputDate = $Y . "-" . $M . "-" . $D;
  return $OutputDate;
}

function selected($fixed,$filter_value) {
  if ($fixed == $filter_value) {
    $filterout = "selected";
  } else {
    $filterout = "";
  }
  return $filterout;
}

function checked($fixed,$filter_value) {
  if ($fixed == $filter_value) {
    $filterout = "checked";
  } else {
    $filterout = "";
  }
  return $filterout;
}

function countstatus($start,$end,$role,$status,$system,$library,$db) {
  $recordcount = 0;
  $query="SELECT COUNT(1) FROM seal.`SENYLRC-SEAL2-STATS` where `Timestamp` >= '$start 00:00:00' and `Timestamp` <= '$end 11:59:59' and `fill` = $status";
  if ( $role == "borrow" ) {
    $query = $query . " and `ReqSystem` = '$system'";
    if ( $library != "" ) {
      $query = $query . " and `Requester LOC` = '$library'";
    }
  }
  if ( $role == "lend" ) {
    $query = $query . " and `DestSystem` = '$system'";
    if ( $library != "" ) {
      $query = $query . " and `Destination` = '$library'";
    }
  }
  $result = mysqli_query($db,$query);
  $row = mysqli_fetch_row($result);
  $recordcount = $row[0];
  return $recordcount;
}

### Get filter info from last visit or start anew ###

if (isset($_REQUEST['libsystem'])) {
  $libsystem = $_REQUEST['libsystem'];
} else {
  $libsystem = "";
}

if (isset($_REQUEST['startdate'])) {
  $startdate = $_REQUEST['startdate'];
} else {
  $startdate = "09/01/2017";
}

if (isset($_REQUEST['enddate'])) {
  $enddate = $_REQUEST['enddate'];
} else {
  $enddate = date("m/d/Y");
}

if (isset($_REQUEST['sessionID'])) {
  $sessionID = $_REQUEST['sessionID'];
} else {
  $sessionID = 1000 + rand (0,8999);
}

if (isset($_REQUEST['csv'])) {
  $csv = "on";
} else {
  $csv = "off";
}

#Show filter options
echo "<h3>Enter your desired date range:</h3>";
echo "<form method='post' action='old-school-stats'>";
echo "Start Date:";
echo "<input id='datepicker' value = " . $startdate . " name='startdate'/>";
echo " End Date:";
echo "<input id='datepicker2' value = " . $enddate . "  name='enddate'/>";
echo " <input name='csv' type='checkbox' " . checked("on",$csv) . "> Generate CSV?";
echo " <a href='old-school-stats'>Clear</a>";
echo "<br><br>Requesting Library System: <select name='libsystem'>";
echo "<option value='' " . selected("",$libsystem) . ">None</option>";
echo "<option value = 'CVES' " . selected("CVES",$libsystem) . ">Champlain Valley Education Services School Library System</option>";
echo "<option value = 'CEFL' " . selected("CEFL",$libsystem) . ">Clinton Essex Franklin Library System</option>";
echo "<option value = 'FEH' " . selected("FEH",$libsystem) . ">Franklin-Essex-Hamilton School Library System</option>";
echo "<option value = 'JLHO' " . selected("JLHO",$libsystem) . ">Jefferson-Lewis School Library System</option>";
echo "<option value = 'NCLS' " . selected("NCLS",$libsystem) . ">North Country Library System</option>";
echo "<option value = 'NNYLN' " . selected("NNYLN",$libsystem) . ">Northern New York Library Network</option>";
echo "<option value = 'OSW' " . selected("OSW",$libsystem) . ">Oswego County School Library System at CiTi</option>";
echo "<option value = 'SLL' " . selected("SLL",$libsystem) . ">St. Lawrence-Lewis School Library System</option>";
echo "</select>";
echo "<input type='hidden' name='sessionID' value='$sessionID'>";
echo " <input type='submit' value='Submit'>";
echo "</form>";

if ( $libsystem != "" ) {

  $startd = convertDate($startdate);
  $endd = convertDate($enddate);

  $sysbfill=countstatus($startd,$endd,"borrow",1,$libsystem,"",$db);
  $sysbnfill=countstatus($startd,$endd,"borrow",0,$libsystem,"",$db);
  $sysbexpire=countstatus($startd,$endd,"borrow",4,$libsystem,"",$db);
  $sysbcancel=countstatus($startd,$endd,"borrow",6,$libsystem,"",$db);
  $sysbnoans=countstatus($startd,$endd,"borrow",3,$libsystem,"",$db);
  $sysbtotal=$sysbfill+$sysbnfill+$sysbexpire+$sysbcancel+$sysbnoans;

  $sysbfillpercent = number_format(($sysbfill / $sysbtotal) * 100, 1 ) . '%';
  $sysbnfillpercent = number_format(($sysbnfill / $sysbtotal) * 100, 1 ) . '%';
  $sysbexpirepercent = number_format(($sysbexpire / $sysbtotal) * 100, 1 ) . '%';
  $sysbcancelpercent = number_format(($sysbcancel / $sysbtotal) * 100, 1 ) . '%';
  $sysbnoanspercent = number_format(($sysbnoans / $sysbtotal) * 100, 1 ) . '%';

  $syslfill=countstatus($startd,$endd,"lend",1,$libsystem,"",$db);
  $syslnfill=countstatus($startd,$endd,"lend",0,$libsystem,"",$db);
  $syslexpire=countstatus($startd,$endd,"lend",4,$libsystem,"",$db);
  $syslcancel=countstatus($startd,$endd,"lend",6,$libsystem,"",$db);
  $syslnoans=countstatus($startd,$endd,"lend",3,$libsystem,"",$db);
  $sysltotal=$syslfill+$syslnfill+$syslexpire+$syslcancel+$syslnoans;

  $syslfillpercent = number_format(($syslfill / $sysltotal) * 100, 1 ) . '%';
  $syslnfillpercent = number_format(($syslnfill / $sysltotal) * 100, 1 ) . '%';
  $syslexpirepercent = number_format(($syslexpire / $sysltotal) * 100, 1 ) . '%';
  $syslcancelpercent = number_format(($syslcancel / $sysltotal) * 100, 1 ) . '%';
  $syslnoanspercent = number_format(($syslnoans / $sysltotal) * 100, 1 ) . '%';

  echo "<table width='50%' cellpadding='0' cellspacing='0'>";
  echo "<tr valign='top'>";
  echo "<th width='50%'><p align='right'><b>Borrower Statistics System-Wide</b></p></th>";
  echo "<th width='50%'><p align='right'><b>Lender Statistics System-Wide</b></p></th>";
  echo "</tr>";
  echo "<tr valign='top'>";
  echo "<td width='50%'><p align='right'>Total Requests: " . $sysbtotal . "</p></td>";
  echo "<td width='50%'><p align='right'>Total Requests: " . $sysltotal . "</p></td>";
  echo "</tr>";
  echo "<tr valign='top'>";
  echo "<td width='50%'><p align='right'>Filled: " . $sysbfill . " (" . $sysbfillpercent . ")</p></td>";
  echo "<td width='50%'><p align='right'>Filled: " . $syslfill . " (" . $syslfillpercent . ")</p></td>";
  echo "</tr>";
  echo "<tr valign='top'>";
  echo "<td width='50%'><p align='right'>Not Filled: " . $sysbnfill . " (" . $sysbnfillpercent . ")</p></td>";
  echo "<td width='50%'><p align='right'>Not Filled: " . $syslnfill . " (" . $syslnfillpercent . ")</p></td>";
  echo "</tr>";
  echo "<tr valign='top'>";
  echo "<td width='50%'><p align='right'>Expired: " . $sysbexpire . " (" . $sysbexpirepercent . ")</p></td>";
  echo "<td width='50%'><p align='right'>Expired: " . $syslexpire . " (" . $syslexpirepercent . ")</p></td>";
  echo "</tr>";
  echo "<tr valign='top'>";
  echo "<td width='50%'><p align='right'>Canceled: " . $sysbcancel . " (" . $sysbcancelpercent . ")</p></td>";
  echo "<td width='50%'><p align='right'>Canceled: " . $syslcancel . " (" . $syslcancelpercent . ")</p></td>";
  echo "</tr>";
  echo "<tr valign='top'>";
  echo "<td width='50%'><p align='right'>Not Answered: " . $sysbnoans . " (" . $sysbnoanspercent . ")</p></td>";
  echo "<td width='50%'><p align='right'>Not Answered: " . $syslnoans . " (" . $syslnoanspercent . ")</p></td>";
  echo "</tr></table>";

  #fetch list of libraries belonging to this system
  if ($csv == "on" ) {
    $filedate = date("Y-m-d");
    $csvlink = "<a href='sites/duenorth.nnyln.org/files/csv/$libsystem-$filedate-ID$sessionID.csv' target='_blank'>Download CSV</a>";
    $header = "Library,ILL,Borrower,Filled,Un-Filled,Expired,Canceled,Lender,Filled,Un-Filled,Expired,Canceled\n";
    $csvfile = fopen("$csvpath/$libsystem-$filedate-ID$sessionID.csv", "w");
    fwrite ($csvfile, "$libsystem DueNorth Statistics\n");
    fwrite ($csvfile, "From $startdate to $enddate\n\n");
    fwrite ($csvfile, $header);
  } else {
    $csvlink = "";
  }
  $query="SELECT Name,loc FROM seal.`SENYLRC-SEAL2-Library-Data` where system = '$libsystem' order by 'Name'";
  $result = mysqli_query($db,$query);
  echo "<table>";
  echo "<tr><th colspan=2>$csvlink</th><th colspan=4>Borrower</th><th colspan=4>Lender</th></tr>";
  echo "<tr><th>Library</th><th>ILL</th><th>Filled</th><th>Un-Filled</th><th>Expired</th><th>Canceled</th><th>Filled</th><th>Un-Filled</th><th>Expired</th><th>Canceled</th></tr>";
  while ($row = mysqli_fetch_row($result)) {
    $bfill=countstatus($startd,$endd,"borrow",1,$libsystem,$row[1],$db);
    $bnfill=countstatus($startd,$endd,"borrow",0,$libsystem,$row[1],$db);
    $bexpire=countstatus($startd,$endd,"borrow",4,$libsystem,$row[1],$db);
    $bcancel=countstatus($startd,$endd,"borrow",6,$libsystem,$row[1],$db);

    $lfill=countstatus($startd,$endd,"lend",1,$libsystem,$row[1],$db);
    $lnfill=countstatus($startd,$endd,"lend",0,$libsystem,$row[1],$db);
    $lexpire=countstatus($startd,$endd,"lend",4,$libsystem,$row[1],$db);
    $lcancel=countstatus($startd,$endd,"lend",6,$libsystem,$row[1],$db);
    echo "<tr><td>$row[0]</td><td>$row[1]</td><td>$bfill</td><td>$bnfill</td><td>$bexpire</td><td>$bcancel</td><td>$lfill</td><td>$lnfill</td><td>$lexpire</td><td>$lcancel</td></tr>";
    $txt = "$row[0],$row[1],,$bfill,$bnfill,$bexpire,$bcancel,,$lfill,$lnfill,$lexpire,$lcancel\n";
    if ( $csv == "on" ) fwrite ($csvfile,$txt);
  }
  echo "</table>";
  if ( $csv == "on" ) fclose ($csvfile);
} else {
  echo "Select a library system to begin.";
}
