<script>
(function($) {

Drupal.behaviors.DisableInputEnter = {
  attach: function(context, settings) {
    $('input', context).once('disable-input-enter', function() {
      $(this).keypress(function(e) {
        if (e.keyCode == 13) {
          e.preventDefault();
        }
      });
    });
  }
}
})(jQuery);
</script>
<p>Please review the details of your request and then select a library to send your request to.</p>
<form action="sent" method="post">
<?php
####Define the different library systems
#OPALS
$OSW="Oswego County Schools";
$JLHO="Jefferson-Lewis SLS";
$SLL="St. Lawrence-Lewis SLS";
$FEH="Franklin Essex Hamilton SLS";
$CVES="Champlain Valley Educational Services SLS";

#Innovative
$SLU="St. Lawrence University";

#SirsiDynix
$PSCOLL="Paul Smith's College";
$NCLS="North Country Library System";

#SirsiDynix Horizon
$CEFL="CEF Library System";

#ExLibris Aleph
$SUNYOSW="SUNY Oswego";
$SUNYPOT="SUNY Potsdam";
$SUNYCAN="SUNY Canton";
$SUNYPLA="SUNY Plattsburgh";
$JEFCC="Jefferson Community College";
$NCCC="North Country Community College";

#Get the IDs needed for curl command
$jession= $_GET['jsessionid'];
$windowid= $_GET['windowid'];
$idc= $_GET['id'];

#Function to see if requester and destination are part of same system
function checkfilter ($libsystem,$profilesystem){
  if  ($profilesystem==$libsystem){
    $filtervalue='1';
  } else {
    $filtervalue='0';
  }
  return $filtervalue;
}

#Function to see if item is available for loan
function checkitype ($mylocholding,$itemtype){
  require '../seal_script/seal_db.inc';
  $db = mysqli_connect($dbhost, $dbuser, $dbpass);
  mysqli_select_db($db,$dbname);
  $GETLISTSQL="SELECT book,av,journal,reference,ebook FROM `SENYLRC-SEAL2-Library-Data` where alias like '%$mylocholding%' ";
  $result=mysqli_query($db, $GETLISTSQL);
  while($row = $result->fetch_assoc() ){
    if (strpos($itemtype, 'book') !== 1) {
      #See if  request is for a book
      if ( $row['book']==1   ) {
      #Checking if book is allowed
        return 1;
      }
    }
    if (strpos($itemtype, 'recording') !== 1) {
    #See if  request is  audio video related
      if ( $row['av']==1   ) {
      #Checking if AV is allowed
        return 1;
      }
    }
    if (strpos($itemtype, 'video') !== 1) {
    #See if  request is  audio video related
      if ( $row['av']==1   ) {
      #Checking if AV is allowed
        return 1;
      }
    }
    if (strpos($itemtype, 'journal') !== 1) {
    #See if  request is for a journal
      if ( $row['journal']==1   ) {
      #Checking if journal is allowed
        return 1;
      }
    }
    if (strpos($itemtype, 'reference') !== 1) {
    #See if  request is for reference
      if ( $row['reference']==1   ) {
      #Checking if reference is allowed
        return 1;
      }
    }
    if (strpos($itemtype, 'electronic') !== 1) {
    #See if  request is for ebook
      if ( $row['ebook']==1   ) {
      #Checking if e-book is allowed
        return 1;
      }
    }
  }
}

#Function to see if library is part of SEAL via alias
function checklib_ill ($mylocholding){
  $libparticipant='';
  require '../seal_script/seal_db.inc';
  $db = mysqli_connect($dbhost, $dbuser, $dbpass);
  mysqli_select_db($db,$dbname);
  $GETLISTSQL="SELECT loc,participant,`ILL Email` FROM `SENYLRC-SEAL2-Library-Data` where alias like '%$mylocholding%' ";
  $result=mysqli_query($db, $GETLISTSQL);
  $row = mysqli_fetch_row($result);
  $libparticipant = $row;
  return $libparticipant;
}

#Function to see if library is part of SEAL via name
function checkname_ill ($mylocholding){
  $libparticipant='';
  require '../seal_script/seal_db.inc';
  $db = mysqli_connect($dbhost, $dbuser, $dbpass);
  mysqli_select_db($db,$dbname);
  $GETLISTSQL="SELECT loc,participant,`ILL Email` FROM `SENYLRC-SEAL2-Library-Data` where name like '%$mylocholding%' ";
  $result=mysqli_query($db, $GETLISTSQL);
  $row = mysqli_fetch_row($result);
  $libparticipant = $row;
  return $libparticipant;
}

#Function to translate library name from alias to real name
function getlibname ($mylocholding){
  $libname='';
  require '../seal_script/seal_db.inc';
  $db = mysqli_connect($dbhost, $dbuser, $dbpass);
  mysqli_select_db($db,$dbname);
  $GETLISTSQL="SELECT name FROM `SENYLRC-SEAL2-Library-Data` where alias like '%$mylocholding%' ";
  $result=mysqli_query($db,$GETLISTSQL);
  $row = mysqli_fetch_row($result);
  $libname[0] = $row[0];
  $libname[1] = $GETLISTSQL;
  return $libname;
}

#Function to get lib system ID
function getlibsystem ($mylocholding){
  $libsystemq='';
  require '../seal_script/seal_db.inc';
  $db = mysqli_connect($dbhost, $dbuser, $dbpass);
  mysqli_select_db($db,$dbname);
  $GETLISTSQL="SELECT system FROM `SENYLRC-SEAL2-Library-Data` where alias like '%$mylocholding%' ";
  $result=mysqli_query($db,$GETLISTSQL);
  $row = mysqli_fetch_row($result);
  $libsystemq[0] = $row[0];
  $libsystemq[1] = $GETLISTSQL;
  return $libsystemq[0];
}

#Function to see if library is syspended
function checklib_suspend ($mylocholding){
  $libparticipant='';
  require '../seal_script/seal_db.inc';
  $db = mysqli_connect($dbhost, $dbuser, $dbpass);
  mysqli_select_db($db,$dbname);
  $GETLISTSQL="SELECT suspend FROM `SENYLRC-SEAL2-Library-Data` where alias like '%$mylocholding%' ";
  $result=mysqli_query($db,$GETLISTSQL);
  $row = mysqli_fetch_row($result);
  $libparticipant = $row[0];
  return $libparticipant;
}

#This function is used for the encoding of the curl command
function myUrlEncode($string) {
    $entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
    $replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
    return str_replace($entities, $replacements, urlencode($string));
}

#Define the server to make the CURL request to
$reqserverurl='https://duenorth.indexdata.com/service-proxy/?command=record\\&windowid=';
#Define the CURL command
$cmd= "curl -b JSESSIONID=$jession $reqserverurl$windowid\\&id=". urlencode($idc);

#put in curl command in as html comment for development
#echo "<!-- my cmd is  $cmd \n-->";

#Run the CURL to get XML data
$output = shell_exec($cmd);

#put xml in html src for development
#echo "<!-- \n";
#print_r ($output);
#echo "\n-->\n\n";

#Pull the information of the person making the request from Drupal users
global $user;   // load the user entity so to pick the field from.

$user_contaning_field = user_load($user->uid);  // Check if we're dealing with an authenticated user

if($user->uid) {    // Get field value;
  $field_first_name = field_get_items('user', $user_contaning_field, 'field_first_name');
  $field_last_name = field_get_items('user', $user_contaning_field, 'field_last_name');
  $field_your_institution = field_get_items('user', $user_contaning_field, 'field_your_institution');
  $field_loc_location_code = field_get_items('user', $user_contaning_field, 'field_loc_location_code');
  $field_street_address =   field_get_items('user', $user_contaning_field, 'field_street_address');
  $field_city_state_zip =   field_get_items('user', $user_contaning_field, 'field_city_state_zip');
  $field_work_phone =   field_get_items('user', $user_contaning_field, 'field_work_phone');
  $field_home_library_system =   field_get_items('user', $user_contaning_field, 'field_home_library_system');
  $field_filter_own_system =   field_get_items('user', $user_contaning_field, 'field_filter_own_system');
  $email = $user->mail;
}

#Display the details of the person making the request
echo "<h1>Requester Details</h1>";
echo "First Name:  " .$field_first_name[0]['value']. "<br>";
echo "Last Name:  ".$field_last_name[0]['value']. "<Br>";
echo "E-mail:  ".$email. "<br>";
echo "Institution:  ".$field_your_institution[0]['value'] ."<br>";
echo "Work Phone: ".$field_work_phone[0]['value'] ."<br>";
echo "Mailing Address:<br>  ".$field_street_address[0]['value'] ."<br> ".$field_city_state_zip[0]['value'] ."<br><br>";
echo "<input type='hidden' name='fname' value= ' ".$field_first_name[0]['value'] ." '>";
echo "<input type='hidden' name='lname' value= ' ".$field_last_name[0]['value'] ." '>";
echo "<input type='hidden' name='email' value= ' ".$email ."'>";
echo "<input type='hidden' name='inst' value= ' ".$field_your_institution[0]['value'] ." '>";
echo "<input type='hidden' name='address' value= ' ".$field_street_address[0]['value'] ." '>";
echo "<input type='hidden' name='caddress' value= ' ".$field_city_state_zip[0]['value'] ." '>";
echo "<input type='hidden' name='wphone' value= ' ".$field_work_phone[0]['value'] ." '>";
echo "<input type='hidden' name='reqLOCcode' value= ' ".$field_loc_location_code[0]['value'] ." '>";
#Display the request form to user
?>
<hr>
Need by date <input type="text" name="needbydate"><br>
Note <input type="text" size="100" name="reqnote"><br><br>
Is this a request for an article?
Yes <input type="radio" onclick="javascript:yesnoCheck();" name="yesno" id="yesCheck">
No <input type="radio" onclick="javascript:yesnoCheck();" name="yesno" id="noCheck"><br>
<div id="ifYes" style="display:none">
Article Title: <input size="80" type="text" name="arttile"><br>
Article Author: <input size="80" type='text' name='artauthor'><br>
Volume: <input size="80" type='text' name='artvolume'><br>
Issue:  <input type='text' name='artissue'><br>
Pages: <input type='text' name='artpage' ><br>
Issue Month: <input type='text' name='artmonth' ><br>
Issue Year: <input type='text' name='artyear' ><br>
Copyright compliance:  <select name="artcopyright">  <option value=""></option> <option value="ccl">CCL</option>   <option value="ccg">CCG</option>  </select>
</div><br><hr>
<?php

//XML file for request for development
#$file = 'http://seal2.senylrc.org/zackwork/output.xml';
//load test file from server
//$records = new SimpleXMLElement($file, null, true); //for testing

#Now we process the xml for Indexdata
$records = new SimpleXMLElement($output); // for production
$requestedtitle=$records->{'md-title-complete'};
$requestedtitle2=$records->{'md-title-number-section'};
$requestedauthor=$records->{'md-author'};
$requested=$records->{'md-title'};
$itemtype=$records->{'md-medium'};
#Remove any white space stored in item type
$itemtype=trim($itemtype);
$pubdate=$records->{'md-date'};
$isbn=$records->{'md-isbn'};
$issn=$records->location->{'md-issn'};
echo "Requested Title:<b>: " . $requestedtitle  ."  ". $requestedtitle2 . "</b><br>";
echo "Requested Author:<b>: " . $requestedauthor ."</b><br>";
echo "Item Type:  " . $itemtype."<br>";
echo "Publication Date: " . $pubdate."<br>";
if (strlen($issn)>0)   echo "ISSN: " . $issn."<br>";
if (strlen($isbn)>0)   echo "ISBN: " . $isbn."<br>";
echo "<br>";
#Covert single quotes to code so they don't get cut off
$requestedtitle=htmlspecialchars($requestedtitle, ENT_QUOTES);
$requestedtitle2=htmlspecialchars($requestedtitle2, ENT_QUOTES);
$requestedauthor =htmlspecialchars($requestedauthor , ENT_QUOTES);
echo "<input type='hidden' name='bibtitle' value= ' ".$requestedtitle ." : ". $requestedtitle2 ." '>";
echo "<input type='hidden' name='bibauthor' value= ' ".$requestedauthor ." '>";
echo "<input type='hidden' name='bibtype' value= ' ".$itemtype ." '>";
echo "<input type='hidden' name='pubdate' value= ' ".$pubdate ." '>";
echo "<input type='hidden' name='isbn' value= ' ".$isbn ." '>";
echo "<input type='hidden' name='issn' value= ' ".$issn ." '>";

#Pull holding info and make available to requester to choose one
#Set receiver email to senylrc for testing
#$destemail="chuckh@nnyln.org";

##This will loop through all the libraries that have title and see if they should be in drop down to a make a request
echo "<select required name='destination'>";
echo "<option value=''> Please Select a library</option>";
#This variable is used to count destination libraries available to make the request
$loccount='1';
foreach ($records->location as $location)  {

#Set to the locname to the current location node in xml response
$locname = $location['name'];

if (($locname == $OSW) || ($locname == $JLHO)  ||   ($locname == $SLL)  ||   ($locname == $FEH)  ||   ($locname == $CVES)) {
  #Pull the checksum for the location
  $schoolchecksum=$location['checksum'];
  #redo the curl statement to includes the checksum
  $cmdschool= "curl -b JSESSIONID=$jession $reqserverurl$windowid\\&id=". urlencode($idc)."\&checksum=$schoolchecksum\&offset=1";
  #This echo will show the CURL statment as an HTML comment
  echo "<!-- my cmd school is $cmdschool \n-->";
  $outputschool = shell_exec($cmdschool);
  $recordssSCHOOL = new SimpleXMLElement($outputschool); // for production
  #print_r($recordssSCHOOL);
  #Go through the holding records
  foreach ($recordssSCHOOL->d852 as $d852){
    $schoolavil=$d852['i1'];
    $schoolloc=$d852->sb;
    $schoolcall1=$d852->sh;
    #See if holding is from a SEAL Library and get email
    $sealcheck=checklib_ill($schoolloc);
    $destloc=$sealcheck[0];
    $destemail=$sealcheck[2];
    $sealstatus=$sealcheck[1];
    #See if library is suspended
    $suspendstatus=checklib_suspend($schoolloc);
    #Check if they will loan that item type
    $itemtypecheck = checkitype ($schoolloc,$itemtype);
    if (($suspendstatus==0)&&($itemtypecheck==1)&&($sealstatus==1)&&(strlen($destemail)) > 2) {
      #only process a library if they particate in seal and have a lending email
      #Translates values to txt for patron on item  status
      #if ( $schoolavil>0 ) { $schooltxtavail="Not Available"; }else{ $schooltxtavail="Available"; }
      $schooltxtavail="UNKNOWN";
      #Translate library alias to a real name for patron
      $libname=getlibname($schoolloc);
      #Translate library alias to get libsystem
      $libsystemq=getlibsystem($schoolloc);
      #Set Libname from XML data
      $libname=$libname[0];
      #If we don't have a real name in database use the libary alias from the XML data
      if (  strlen($libname) <2) { $libname=$schoolloc; }
      #Show this option to patron if SEAL Status is 1 and Suspendstatus is 0
      $loccount=$loccount+1;
			$mylocalcallLocation='';
      $schoolcall1= preg_replace('/[:]/', ' ' , $schoolcall1);
      echo"<option value='". $schoolloc .":".$libname.":".$libsystemq.":".$schooltxtavail.":".$schoolcall1.":".$mylocalcallLocation.":".$destemail.":".$destloc."'>Library:<strong>".$libname."</strong>   Availability: $schooltxtavail Call Number:$schoolcall1  </option>";
      }
    }#End looping through each of the school locations
  } elseif ($locname == $NCLS){
    foreach ($location->holdings->holding as $holding){
      $mylocholding=$locname;
      $mylocalcallNumber=$holding->callNumber;
      $mylocalAvailability=$holding->localAvailability;
      $mylocalcallLocation=$holding->localLocation;
      #Translate the - in the catalog to txt
      if ($mylocalAvailability == "-") {
        $available=1;
      } else {
        $available=0;
      }
      $mylocalAvailability=str_replace("-","Available",$mylocalAvailability);
      #Translate library alias to a real name for patron
      $realname=getlibname($mylocalcallLocation);
      $libname=$realname[0];
      #Translate library alias to get libsystem
      $libsystemq=getlibsystem($mylocalcallLocation);
      #See if holding is from a SEAL Library and get email
      $sealcheck=checklib_ill($mylocalcallLocation);
      $destloc=$sealcheck[0];
      $destemail=$sealcheck[2];
      $sealstatus=$sealcheck[1];
      #See if library is suspended
      $suspendstatus=checklib_suspend($libname);
      #Check if they will loan that item type
      $itemtypecheck = checkitype ($mylocalcallLocation,$itemtype);
      #Show this option to patron if SEAL Status is 1 and Suspendstatus is 0
      if (($suspendstatus==0)&&($itemtypecheck==1)&&($sealstatus==1)&&(strlen($destemail) > 2)&&($available==1) ) {
        $loccount=$loccount+1;
        echo"<option value='". $mylocholding.":".$libname.":".$libsystemq.":".$mylocalAvailability.":".$mylocalcallNumber.":".$mylocalcallLocation.":".$destemail.":".$destloc." '>Library:<strong>".$libname."</strong>   Availability: $mylocalAvailability  Call Number: $mylocalcallNumber</option> ";
      }
    } # END NCLS
  } elseif ($locname == $SLU){
    foreach ($location->holdings->holding as $holding){
      $mylocholding=$locname;
      $mylocalcallNumber=$holding->callNumber;
      $mylocalAvailability=$holding->localAvailability;
      $mylocalcallLocation=$holding->localLocation;
      #Translate the - in the catalog to txt
      if ($mylocalAvailability == "AVAILABLE") {
        $available=1;
      } else {
        $available=0;
      }
      #Translate library alias to a real name for patron
      $libname=$mylocholding;
      #Translate library alias to get libsystem
      $libsystemq="NNYLN";
      #See if holding is from a SEAL Library and get email
      $sealcheck=checkname_ill($mylocholding);
      $destloc=$sealcheck[0];
      $destemail=$sealcheck[2];
      $sealstatus=$sealcheck[1];
      #See if library is suspended
      $suspendstatus=checklib_suspend($libname);
      #Check if they will loan that item type
      $itemtypecheck = checkitype ($libname,$itemtype);
      #Show this option to patron if SEAL Status is 1 and Suspendstatus is 0
      if (($suspendstatus==0)&&($itemtypecheck==1)&&($sealstatus==1)&&(strlen($destemail) > 2)&&($available==1) ) {
        $loccount=$loccount+1;
        echo"<option value='". $mylocholding.":".$libname.":".$libsystemq.":".$mylocalAvailability.":".$mylocalcallNumber.":".$mylocalcallLocation.":".$destemail.":".$destloc." '>Library:<strong>".$libname."</strong>   Availability: $mylocalAvailability  Call Number: $mylocalcallNumber</option> ";
      }
    }
  } elseif ($locname == $CEFL){
    foreach ($location->holdings->holding as $holding){
      $mylocholding=$locname;
      $mylocalcallNumber=$holding->callNumber;
      $mylocalAvailability=$holding->localAvailability;
      $mylocalcallLocation=$holding->localLocation;
      #Translate the - in the catalog to txt
      $mylocalAvailability=str_replace("\n",'',$mylocalAvailability);
      if ($mylocalAvailability == "Available") {
        $available=1;
      } else {
        $available=0;
      }
		$mylocalAvailability=str_replace("Available","UNKNOWN",$mylocalAvailability);
      #Translate library alias to a real name for patron
      $realname=getlibname($mylocalcallLocation);
      $libname=$realname[0];
      #Translate library alias to get libsystem
      $libsystemq=getlibsystem($mylocalcallLocation);
      #See if holding is from a SEAL Library and get email
      $sealcheck=checklib_ill($mylocalcallLocation);
      $destloc=$sealcheck[0];
      $destemail=$sealcheck[2];
      $sealstatus=$sealcheck[1];
      #See if library is suspended
      $suspendstatus=checklib_suspend($libname);
      #Check if they will loan that item type
      $itemtypecheck = checkitype ($mylocalcallLocation,$itemtype);
      #Show this option to patron if SEAL Status is 1 and Suspendstatus is 0
      if (($suspendstatus==0)&&($itemtypecheck==1)&&($sealstatus==1)&&(strlen($destemail) > 2)&&($available==1) ) {
        $loccount=$loccount+1;
        echo"<option value='". $mylocholding.":".$libname.":".$libsystemq.":".$mylocalAvailability.":".$mylocalcallNumber.":".$mylocalcallLocation.":".$destemail.":".$destloc." '>Library:<strong>".$libname."</strong>   Availability: $mylocalAvailability  Call Number: $mylocalcallNumber</option> ";
      }
  } # END CEFL
} else {
  foreach ($location->holdings->holding as $holding){
    $mylocholding=$holding->localLocation;
    $mylocalcallNumber=$holding->callNumber;
    $mylocalAvailability=$holding->localAvailability;
    #This is used for the college folks more often
    $mylocalcallLocation=$holding->shelvingLocation;
    #Have to do this for the those who put quotes in the call number
    $mylocalcallNumber=htmlspecialchars($mylocalcallNumber, ENT_QUOTES);
    #See if holding is from a SEAL Library and get email
    $sealcheck=checklib_ill($mylocholding);
    $destloc=$sealcheck[0];
    $destemail=$sealcheck[2];
    $sealstatus=$sealcheck[1];
    #See if library is suspended
    $suspendstatus=checklib_suspend($mylocholding);
    #Check if they will loan that item type
    $itemtypecheck = checkitype ($mylocholding,$itemtype);
    if  (($sealstatus==1)&&($itemtypecheck==1) && (strlen($destemail) > 2)&& ($suspendstatus==0)){
      #only process a library if they particate in seal and have a lending email
      #Set the Library name to the catalog name  this is OK for places that don't have multple locations defined
      $libname=$locname;
      #Get the Library system for the destination library
      $libsystemq=getlibsystem($mylocholding);
      #See if we need to filter library for requester from MH requesters
      if ($field_filter_own_system[0]['value']>0){
        $filterstatus=checkfilter ("MH",$field_home_library_system[0]['value']);
        } else {
          $filterstatus=0;
        }
        #If they are not filtering own system show this library as a destination
        if ($filterstatus==0) {
          $loccount=$loccount+1;
          echo"<option value='". $mylocholding.":".$libname.":".$libsystemq.":".$mylocalAvailability.":".$mylocalcallNumber.":".$mylocalcallLocation.":".$destemail.":".$destloc."'>Library:<strong>".$libname."</strong> Availability: $mylocalAvailability  Call Number: $mylocalcallNumber</option>";
        }
      } #End processing destination library that is active in SEAL
    } #This end the foreach statement in the last else for catalogs
  }
}#This is the end of the for loop for locations
##End of looking at holdings##
echo "</select>";
#If we have locations to route to show submit
if ($loccount>0){
  echo "<input type=Submit value=Submit> ";
  #If we have no locations don't show submit and display error
} else {
  echo "<br><br>Sorry, no available library to route your request at this time.  <a href='https://duenorth.nnyln.org'>Would you like to try another search ?</a>";
}
?>
</form>
