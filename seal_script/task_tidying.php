<?php
//This script cleans out the Borrower and Lender tasks screen for the folks
//that don't use them regularly.

$Months=6;
$today=date("m/d/Y");

#####Connect to database
require 'seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db,$dbname);

$sqlupdate="UPDATE `SENYLRC-SEAL2-STATS` SET LenderStatus = 'Sent', responderNOTE = CONCAT(responderNOTE, ' AUTOMARKED SENT $today') WHERE Fill=1 AND (LenderStatus IS NULL OR LenderStatus = '') AND BorrowerStatus = 'Returned'";
mysqli_query($db, $sqlupdate);

$sqlupdate="UPDATE `SENYLRC-SEAL2-STATS` SET LenderStatus = 'Sent', BorrowerStatus = 'Returned', responderNOTE = CONCAT(responderNOTE, ' AUTOMARKED SENT/RETURNED $today') WHERE Fill=1 AND (LenderStatus IS NULL OR LenderStatus = '') AND (BorrowerStatus IS NULL OR BorrowerStatus = '' OR BorrowerStatus = 'Arrived') AND STR_TO_DATE(Timestamp,'%Y-%m-%d %H:%i:%s') < NOW() - INTERVAL $Months MONTH";
mysqli_query($db, $sqlupdate);

$sqlupdate="UPDATE `SENYLRC-SEAL2-STATS` SET BorrowerStatus = 'Returned', responderNOTE = CONCAT(responderNOTE, ' AUTOMARKED RETURNED $today') WHERE Fill=1 AND LenderStatus = 'Sent' AND (BorrowerStatus IS NULL OR BorrowerStatus = '' OR BorrowerStatus = 'Arrived') AND STR_TO_DATE(Timestamp,'%Y-%m-%d %H:%i:%s') < NOW() - INTERVAL $Months MONTH;";
mysqli_query($db, $sqlupdate);
?>
