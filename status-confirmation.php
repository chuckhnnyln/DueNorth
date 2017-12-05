<?php

if (isset($_REQUEST['task'])) $task = $_REQUEST['task'];
if (isset($_REQUEST['system'])) $system = $_REQUEST['system'];
if (isset($_REQUEST['proceed'])) $proceed = $_REQUEST['proceed'];

if (($system == "none") || ($system == "")) {
  $action="stop";
} elseif ($proceed == "Proceed") {
  $action="doit";
} else {
  $action="go";
}

if ($action == "go") {
  if ($system == "CVES") $displaysystem="Champlain Valley Education Services School Library System";
  if ($system == "CEFL") $displaysystem="Clinton Essex Franklin Library System";
  if ($system == "FEH") $displaysystem="Franklin-Essex-Hamilton School Library System";
  if ($system == "JLHO") $displaysystem="Jefferson-Lewis School Library System";
  if ($system == "NCLS") $displaysystem="North Country Library System";
  if ($system == "NNYLN") $displaysystem="Northern New York Library Network";
  if ($system == "OSW") $displaysystem="Oswego County School Library System at CiTi";
  if ($system == "SLL") $displaysystem="St. Lawrence-Lewis School Library System";
  echo "You have chosen to <b>$task lending</b> for all libraries of the <b>$displaysystem</b>.<br><br>";
  echo "This will overwrite the setting for these libraries. Are you sure you wish to proceed? ";
  ?><form action="/status-confirmation" method="post">
  <input type="hidden" name="task" value="<?php echo $task;?>">
  <input type="hidden" name="system" value="<?php echo $system;?>">
  <input type="submit" name="proceed" value="Proceed"> <a href='/adminlib'>Cancel</a></form><?php
} elseif ($action == "doit") {
  echo "<b>The libraries have been updated!<b>";
  #Connect to database
  require '../seal_script/seal_db.inc';
  $db = mysqli_connect($dbhost, $dbuser, $dbpass);
  mysqli_select_db($db,$dbname);

  if ($task == "suspend") {
    #Suspend
    $sqlupdate = "UPDATE `$sealLIB` SET suspend='1' WHERE `system` = '$system' ";
  } else {
    #Activate
    $sqlupdate = "UPDATE `$sealLIB` SET suspend='0' WHERE `system` = '$system' ";
  }
  $result = mysqli_query($db,$sqlupdate);

  #Close the database
  mysqli_close($db);
} else {
  echo "Sorry! We cannot complete your action.  <a href='/testing?action=5'>Please go back</a> and select a library system.";
}
?>
