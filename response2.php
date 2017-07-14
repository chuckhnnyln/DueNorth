<?php

##Get values
$reqnumb=$_REQUEST["num"];
if (isset($_REQUEST['a'])) {    $reqanswer = $_REQUEST['a'];    }else{    $reqanswer='';   }



#####Connect to database
require '../seal_script/seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db,$dbname);

####Escape values for security
$reqnumb = mysqli_real_escape_string($db, $reqnumb);
$reqanswer = mysqli_real_escape_string($db, $reqanswer);


###Process any notes from the lender#############################
if ($_SERVER['REQUEST_METHOD'] == 'POST')  {
	$respnote=$_REQUEST["respondnote"];
	$resfill=$_REQUEST["fill"];
	####Escape values for security
    $respnote = mysqli_real_escape_string($db,  $respnote);
	$resfill = mysqli_real_escape_string($db,  $resfill);
    $sqlupdate = "UPDATE `seal`.`SENYLRC-SEAL2-STATS` SET `emailsent` = '1' , `responderNOTE` =  '$respnote' WHERE `illNUB` = '$reqnumb'";

    if (mysqli_query($db, $sqlupdate)) {
        echo "Thank you.  Your response has been recorded to the request<br><br>";
        ####Setup the note data to be in email
        $respnote=stripslashes($respnote);
        if (strlen($respnote)>0)  $respnote="The lending library has noted the following <br> $respnote";
	        $sqlselect="select responderNOTE,requesterEMAIL,Title,Destination from  `seal`.`SENYLRC-SEAL2-STATS` where illNUB='$reqnumb'  LIMIT 1 ";
            $result = mysqli_query($db,$sqlselect);
            $row = mysqli_fetch_array($result) ;
            $title =$row['Title'];
            $requesterEMAIL=$row['requesterEMAIL'];
            $destlib=$row['Destination'];
            ###Get the Destination Name
            $GETLISTSQLDEST="SELECT`Name`, `ILL Email` FROM `SENYLRC-SEAL2-Library-Data` where loc like '$destlib'  limit 1";
            $resultdest=mysqli_query($db, $GETLISTSQLDEST);
                while ($rowdest = mysqli_fetch_assoc($resultdest)) {
                                $destlib=$rowdest["Name"];
                                $destemail=$rowdest["ILL Email"];
                        }
          #In case the ILL email for the destination library is more than one, break it down to comma for php mail
          $destemailarray = explode(';', $destemail);
          $destemail_to = implode(',', $destemailarray);

        $headers = "From: DueNorth <duenorth@nnyln.org>\r\n" ;
        $headers .= "MIME-Version: 1.0\r\n";
       $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";



            ####sending filled email#####
		    if ($resfill=='1'){

                #######Setting up email notification
                $message = "Your ILL request $reqnumb for $title will be filled by $destlib <br><br>$respnote ".
                                     "<br><br>Please email <b>".$destemail_to."</b> for future communications regarding this request ";
                #######Setup php email headers
                $to=$requesterEMAIL;
                $subject = "ILL Request Filled ILL# $reqnumb  ";
                #####SEND requester an email to let them know the request will be filled
                $message = preg_replace('/(?<!\r)\n/', "\r\n", $message);
                $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
                mail($to, $subject, $message, $headers);

		    ###Sending not filledmessage#########
	        }else{
    		    #######Setting up email notification
                $message = "Your ILL request $reqnumb for $title can not be filled by $destlib.<br> <br>$respnote<br><br> <a href='http://seal2.senylrc.org'>Would you like to try a different library</a>?";
               #######Setup php email headers
                $to=$requesterEMAIL;
                $subject = "ILL Request Not Filled ILL# $reqnumb  ";
                #####SEND requester an email to let them know the request will be filled
                $message = preg_replace('/(?<!\r)\n/', "\r\n", $message);
                $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
                mail($to, $subject, $message, $headers);

	    }
       }else{
          echo "Unable to record answer for ILL request $reqnumb please call SENYLRC to report this error";
       }
####No notes but answering yes or no to ILL request#########
}else{
   ###################The Request will be filled##############################################
   if ($reqanswer=='1'){
       $sqlupdate = "UPDATE `seal`.`SENYLRC-SEAL2-STATS` SET `Fill` =  '1' WHERE `illNUB` = '$reqnumb'";
       if (mysqli_query($db, $sqlupdate)) {
        ########Generate web message
        echo "Please click the submit button to confirm you will fill the request.<br>  Thank You.";
        ?>
       <br><br><h4>Please note the delivery method, tracking info, special handling, etc)</h4>
       <form action="/respond" method="post">
       <input type='hidden' name='num' value= '<? echo $reqnumb ?>' '>
	   <input type='hidden' name='fill' value='1'>
       <textarea name='respondnote' rows="4" cols="50"></textarea><br>
       <input type="submit" value="Submit">
       </form>
       <?php
       ########This will generate an error if database can't be updated########
       }else{
         echo "Unable to record answer for ILL request $reqnumber please call SENYLRC to report this error";
      }
   #########The request will not be filled###########################
  }else{
    $sqlupdate = "UPDATE `seal`.`SENYLRC-SEAL2-STATS` SET `Fill` =  '0' WHERE `illNUB` = '$reqnumb'";
       if (mysqli_query($db, $sqlupdate)) {
          ####Generate web message
          echo "Please click the submit button to confirm you can not fill the request";
		   ?>
          <br><br><h4>Would you like to add a note about the decline?<br> </h4>
          <form action="/respond" method="post">
          <input type='hidden' name='num' value= '<? echo $reqnumb ?>' '>
	      <input type='hidden' name='fill' value='0'>
          <textarea name='respondnote' rows="4" cols="50"></textarea><br>
          <input type="submit" value="Submit">
           </form>
		   <?php
       ####Generate an error if database can't be updated#####
       }else{
          echo "Unable to record answer for ILL request $reqnumb please call SENYLRC to report this error";
       }
  ###End if statement of yes or no#####
  }
#####End if statement if we are updating the note box####
}
mysqli_close($db);

?>
