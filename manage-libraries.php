<?php
###manage-libraries.php###

function checked($filter_value) {
  if ( ($filter_value == "yes") ) {
    $filterout="checked";
  } else {
    $filterout="";
  }
  return $filterout;
}

function selected($days,$filter_value) {
  if ($days == $filter_value) {
    $filterout = "selected";
  } else {
    $filterout = "";
  }
  return $filterout;
}

if (isset($_REQUEST['library'])) {
  $LibraryName = $_REQUEST['library'];
} else {
  $LibraryName = "";
}

if (isset($_REQUEST['loc'])) {
  $loc = $_REQUEST['loc'];
} else {
  $loc = "";
}

$firstpass = ( isset($_REQUEST['firstpass']) ? "no" : "yes" );

if ( $firstpass == "no" ) {
  $filter_library = ( isset($_REQUEST['library']) ? $filter_library = $_REQUEST['library'] : "" );
  $filter_loc = ( isset($_REQUEST['loc']) ? $filter_loc = $_REQUEST['loc'] : "" );
  $filter_alias = ( isset($_REQUEST['filter_alias']) ? $filter_alias = $_REQUEST['filter_alias'] : "" );
  $filter_illemail = ( isset($_REQUEST['filter_illemail']) ? $filter_illemail = $_REQUEST['filter_illemail'] : "" );
  $filter_numresults = ( isset($_REQUEST['filter_numresults']) ? $filter_numresults = $_REQUEST['filter_numresults'] : "" );
  $filter_offset = ( isset($_REQUEST['filter_offset']) ? $filter_offset = $_REQUEST['filter_offset'] : "0" );
  if ( ($filter_library != "") || ($filter_loc != "") || ($filter_alias != "") || ($filter_illemail != "") ) {
    $filter_aliasblank = "";
    $filter_illemailblank = "";
    $filter_illpart = "";
    $filter_suspend = "";
    $filter_system = ( isset($_REQUEST['filter_system']) ? $filter_system = $_REQUEST['filter_system'] : "" );
  } else {
    $filter_illemailblank = ( isset($_REQUEST['filter_illemailblank']) ? $filter_illemailblank = $_REQUEST['filter_illemailblank'] : "" );
    $filter_illpart = ( isset($_REQUEST['filter_illpart']) ? $filter_illpart = $_REQUEST['filter_illpart'] : "" );
    $filter_suspend = ( isset($_REQUEST['filter_suspend']) ? $filter_suspend = $_REQUEST['filter_suspend'] : "" );
    $filter_system = ( isset($_REQUEST['filter_system']) ? $filter_system = $_REQUEST['filter_system'] : "" );
    $filter_aliasblank = ( isset($_REQUEST['filter_aliasblank']) ? $filter_aliasblank = $_REQUEST['filter_aliasblank'] : "" );
  }
} else {
  $filter_library = ( isset($_REQUEST['library']) ? $filter_library = $_REQUEST['library'] : "" );
  $filter_loc = ( isset($_REQUEST['loc']) ? $filter_loc = $_REQUEST['loc'] : "" );
  $filter_offset = 0;
  $filter_alias = "";
  $filter_aliasblank = "";
  $filter_illemail = "";
  $filter_illemailblank = "";
  $filter_suspend = "";
  $filter_system = "";
  $filter_numresults = "25";
  if ( ($filter_library != "") || ($filter_loc != "") ) {
      $filter_illpart = "";
  } else {
    $filter_illpart = "yes";
  }
}

echo "<p>Diagnostic Block";
echo "<br>firstpass: " . $firstpass;
echo "<br>filter_library: " . $filter_library;
echo "<br>filter_loc: " . $filter_loc;
echo "<br>filter_alias: " . $filter_alias;
echo "<br>filter_aliasblank: " . $filter_aliasblank;
echo "<br>filter_illemail: " . $filter_illemail;
echo "<br>filter_illemailblank: " . $filter_illemailblank;
echo "<br>filter_illpart: " . $filter_illpart;
echo "<br>filter_suspend: " . $filter_suspend;
echo "<br>filter_system: " . $filter_system;
echo "<br>filter_numresults: " . $filter_numresults;
echo "<br></p>";

#Connect to database
require '../seal_script/seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db,$dbname);

#Sanitize data
$loc = mysqli_real_escape_string($db,$loc);

$SQLBASE="SELECT * FROM `$sealLIB` WHERE ";
$SQLEND=" ORDER BY `Name` ASC ";

if ( $filter_numresults != "all" ) {
  $sqllimiter = $filter_numresults * $filter_offset;
  $SQLLIMIT = " LIMIT " . $sqllimiter . ", " . $filter_numresults;
} else {
  $SQLLIMIT = "";
}

$SQLMIDDLE =''; #This builds the display options for the SQL
$SQLMIDDLE= ( $filter_aliasblank == "yes" ? $SQLMIDDLE = "`alias` = '' " : $SQLMIDDLE = "`alias` <> '' " );
$SQLMIDDLE= ( $filter_illemailblank == "yes" ? $SQLMIDDLE = $SQLMIDDLE . "AND `ILL Email` = '' " : $SQLMIDDLE = $SQLMIDDLE . "AND `ILL Email` <> '' " );
$SQLMIDDLE= ( $filter_illpart == "yes" ? $SQLMIDDLE = $SQLMIDDLE . "AND `participant` = 1 " : $SQLMIDDLE = $SQLMIDDLE . "AND `participant` = 0 " );
$SQLMIDDLE= ( $filter_suspend == "yes" ? $SQLMIDDLE = $SQLMIDDLE . "AND `suspend` = 0 " : $SQLMIDDLE = $SQLMIDDLE . "AND `suspend` = 1 " );
$SQLMIDDLE= ( strlen($filter_library) >= 2 ? $SQLMIDDLE = $SQLMIDDLE . "AND `Name` like  '%" . $filter_library . "%' " : $SQLMIDDLE = $SQLMIDDLE );

#$GETFULLSQL = $SQLBASE . $SQLEND;
$GETLISTSQL = $SQLBASE . $SQLMIDDLE . $SQLEND . $SQLLIMIT;

#$GETLIST = mysqli_query($db,$GETLISTSQL);
#$GETCOUNT = mysqli_query($db,$GETFULLSQL);
#$GETLISTCOUNTwhole = mysqli_num_rows ($GETCOUNT);

echo $GETLISTSQL . "</br>";

echo "<form action='testing' method='post'>";
echo "<input type='hidden' name='firstpass' value= 'no'>";
echo "<p>Display filters:";
echo "<input type='checkbox' name='filter_aliasblank' value='yes' " . checked($filter_aliasblank) . ">Missing alias ";
echo "<input type='checkbox' name='filter_illemailblank' value='yes' " . checked($filter_illemailblank) . ">Missing ILL Email ";
echo "<input type='checkbox' name='filter_illpart' value='yes' " . checked($filter_illpart) . ">ILL Participant ";
echo "<input type='checkbox' name='filter_suspend' value='yes' " . checked($filter_suspend) . ">ILL Suspended ";
echo "<br>Library System: <select name='filter_system'>";
echo "<option value='' " . selected("",$filter_system) . ">All</option>";
echo "<option value = 'CVES' " . selected("CVES",$filter_system) . ">Champlain Valley Education Services School Library System</option>";
echo "<option value = 'CEFL' " . selected("CEFL",$filter_system) . ">Clinton Essex Franklin Library System</option>";
echo "<option value = 'FEH' " . selected("FEH",$filter_system) . ">Franklin-Essex-Hamilton School Library System</option>";
echo "<option value = 'JLHO' " . selected("JLHO",$filter_system) . ">Jefferson-Lewis School Library System</option>";
echo "<option value = 'NCLS' " . selected("NCLS",$filter_system) . ">North Country Library System</option>";
echo "<option value = 'NNYLN' " . selected("NNYLN",$filter_system) . ">Northern New York Library Network</option>";
echo "<option value = 'OSW' " . selected("OSW",$filter_system) . ">Oswego County School Library System at CiTi</option>";
echo "<option value = 'SLL' " . selected("SLL",$filter_system) . ">St. Lawrence-Lewis School Library System</option>";
echo "</select>";
echo "<br>Search:";
echo "<br>Library Name: <input name='library' type='text' value='$filter_library'> ";
echo "<br>Library Alias: <input name='filter_alias' type='text' value='$filter_alias'> ";
echo "<br>ILL Code: <input name='loc' type='text' value='$filter_loc'> ";
echo "<br>ILL Email: <input name='filter_illemail' type='text' value='$filter_illemail'> ";
echo "<br><select name='filter_numresults'></br>";
echo "<option " . selected("25",$filter_numresults) . " value = '25'>25</option>";
echo "<option " . selected("50",$filter_numresults) . " value = '50'>50</option>";
echo "<option " . selected("100",$filter_numresults) . " value = '100'>100</option>";
echo "<option " . selected("all",$filter_numresults) . " value = 'all'>All</option>";
echo "</select> results per page. ";
echo "<br> " . $GETLISTCOUNTwhole;
$resultpages = ceil ($GETLISTCOUNTwhole / $filter_numresults);
$display_page = $filter_offset + 1;
if ( $filter_numresults != "all" ) {
  echo "Currently on page <select name='filter_offset'>";
  for ($x = 1; $x <= $resultpages; $x++) {
    $localoffset = $x - 1;
    echo "<option " . selected($localoffset,$filter_offset) . " value = '" . $localoffset . "'>" . $x . "</option>";
  }
echo "</select> of " . $resultpages . ".";
}
echo "<br><a href='testing'>Clear</a> <input type=Submit value=Update><br>";
echo "</form>";
?>
