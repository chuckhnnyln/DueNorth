<?php

#response.php

#Get values
$reqnumb=$_REQUEST["num"];

if (isset($_REQUEST['a'])) {
  $reqanswer = $_REQUEST['a'];
} else {
  $reqanswer='';
}

#Connect to database
require '../seal_script/seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db,$dbname);

#Escape values for security
$reqnumb = mysqli_real_escape_string($db, $reqnumb);
$reqanswer = mysqli_real_escape_string($db, $reqanswer);

if ($_SERVER['REQUEST_METHOD'] == 'POST')  {
  #Process any notes from the lender
  $respnote=$_REQUEST["respondnote"];
  $resfill=$_REQUEST["fill"];
  #Escape values for security
  $respnote = mysqli_real_escape_string($db,  $respnote);
  $resfill = mysqli_real_escape_string($db,  $resfill);
  $sqlupdate = "UPDATE `seal`.`SENYLRC-SEAL2-STATS` SET `emailsent` = '1' , `responderNOTE` =  '$respnote' WHERE `illNUB` = '$reqnumb'";
  if (mysqli_query($db, $sqlupdate)) {
    echo "Thank you.  Your response has been recorded to the request<br><br>";
    #Setup the note data to be in email
    $respnote=stripslashes($respnote);
    if (strlen($respnote)>0) $respnote="The lending library has noted the following:<br> $respnote";
    $sqlselect="select responderNOTE,requesterEMAIL,Title,Destination from `seal`.`SENYLRC-SEAL2-STATS` where illNUB='$reqnumb'  LIMIT 1 ";
    $result = mysqli_query($db,$sqlselect);
    $row = mysqli_fetch_array($result) ;
    $title =$row['Title'];
    $requesterEMAIL=$row['requesterEMAIL'];
    $destlib=$row['Destination'];
    #Get the Destination Name
    $GETLISTSQLDEST="SELECT`Name`, `ILL Email` FROM `SENYLRC-SEAL2-Library-Data` where loc like '$destlib'  limit 1";
    $resultdest=mysqli_query($db, $GETLISTSQLDEST);
    while ($rowdest = mysqli_fetch_assoc($resultdest)) {
      $destlib=$rowdest["Name"];
      $destemail=$rowdest["ILL Email"];
    }
    #In case the ILL email for the destination library is more than one, break it down to comma for php mail
    $destemailarray = explode(';', $destemail);
    $destemail_to = implode(',', $destemailarray);
    #sending filled email
    if ($resfill=='1') {
      #Sending filled message
      $message = "Your ILL request $reqnumb for $title will be filled by $destlib <br><br>$respnote <br><br>Please email <b>" . $destemail_to . "</b> for future communications regarding this request ";
      #Setup php email headers
      $to=$requesterEMAIL;
      $subject = "FILLED ILL Request ILL# $reqnumb";
      $headers = 'MIME-Version: 1.0' . "\r\n" . 'From: "DueNorth" <duenorth@nnyln.org>' . "\r\n" . "Reply-to: " . $destemail_to . "\r\n" . 'Content-type: text/html; charset=utf8';
      #SEND requester an email to let them know the request will be filled
      $message = preg_replace('/(?<!\r)\n/', "\r\n", $message);
      $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
      mail($to, $subject, $message, $headers);
    } else {
      #Sending not filled message
      $message = "Your ILL request $reqnumb for $title can not be filled by $destlib.<br> <br>$respnote<br><br> <a href='https://duenorth.nnyln.org'>Would you like to try a different library</a>?";
      #Setup php email headers
      $to=$requesterEMAIL;
      $subject = "UNFILLED ILL Request ILL# $reqnumb ";
      $headers = 'MIME-Version: 1.0' . "\r\n" . 'From: "DueNorth" <duenorth@nnyln.org>' . "\r\n" . 'Content-type: text/html; charset=utf8';
      #SEND requester an email to let them know the request will not be filled
      $message = preg_replace('/(?<!\r)\n/', "\r\n", $message);
      $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
      mail($to, $subject, $message, $headers);
    }
  } else {
    echo "Unable to record answer for ILL request $reqnumb please call SENYLRC to report this error";
  }
} else {
  #The Request will be filled WITH no notes.
  if ($reqanswer=='1') {
    $sqlupdate = "UPDATE `seal`.`SENYLRC-SEAL2-STATS` SET `Fill` =  '1' WHERE `illNUB` = '$reqnumb'";
    if (mysqli_query($db, $sqlupdate)) {
      #Generate web message
      echo "Please click the submit button to confirm you will fill the request.<br>  Thank You.";
      echo "<br><br><h4>Please note the delivery method, tracking info, special handling, etc)</h4>";
      echo "<form action='/respond' method='post'>";
      echo "<input type='hidden' name='num' value='" . $reqnumb . "'>";
      echo "<input type='hidden' name='fill' value='1'>";
      echo "<textarea name='respondnote' rows='4' cols='50'></textarea><br>";
      echo "<input type='submit' value='Submit'>";
      echo "</form>";
      #This will generate an error if database can't be updated
    } else {
      echo "Unable to record answer for ILL request $reqnumb please call NNYLN to report this error";
    }
  } else {
    #The Request will be NOT filled WITH no notes.
    $sqlupdate = "UPDATE `seal`.`SENYLRC-SEAL2-STATS` SET `Fill` =  '0' WHERE `illNUB` = '$reqnumb'";
    if (mysqli_query($db, $sqlupdate)) {
      #Generate web message
      echo "Please click the submit button to confirm you can not fill the request";
      echo "<br><br><h4>Would you like to add a note about the decline?<br> </h4>";
      echo "<form action='/respond' method='post'>";
      echo "<input type='hidden' name='num' value='" . $reqnumb . "'>";
      echo "<input type='hidden' name='fill' value='0'>";
      echo "<textarea name='respondnote' rows='4' cols='50'></textarea><br>";
      echo "<input type='submit' value='Submit'>";
      echo "</form>";
      #Generate an error if database can't be updated
      } else {
        echo "Unable to record answer for ILL request $reqnumb please call NNYLN to report this error";
      }
  } #End if statement of yes or no
#End if statement if we are updating the note box####
}
mysqli_close($db);

?>
