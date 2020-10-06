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

<?php

function find_catalog($location){
  switch ($location) {
    case "Oswego County Schools":
      return "OPALS";
      break;
    case "Jefferson - Lewis SLS":
      return "OPALS";
      break;
    case "St.Lawrence - Lewis SLS":
      return "OPALS";
      break;
    case "Franklin Essex Hamiliton SLS":
      return "OPALS";
      break;
    case "Franklin Essex Hamilton SLS":
      return "OPALS";
      break;
    case "Champlain Valley Educational Services SLS":
      return "OPALS";
      break;
    case "St. Lawrence University":
      return "Innovative";
      break;
    case "Paul Smith's College":
      return "SirsiDynix";
      break;
    case "NCLS":
      return "SirsiDynix";
      break;
    case "CEF Library System":
      return "Horizon";
      break;
    case "SUNY Oswego":
      return "Primo";
      break;
    case "SUNY Potsdam":
      return "Primo";
      break;
    case "SUNY Canton":
      return "Primo";
      break;
    case "SUNY Plattsburgh":
      return "Primo";
      break;
    case "Jefferson Community College":
      return "Primo";
      break;
    case "North Country Community College":
      return "Primo";
      break;
    case "Clarkson University Library":
      return "Worldcat";
      break;
    case "Fort Drum McEwen Library":
      return "Millennium";
      break;
  }
}

function find_locationinfo ($locationalias) {
  $libparticipant='';
  require '../seal_script/seal_db.inc';
  $db = mysqli_connect($dbhost, $dbuser, $dbpass);
  mysqli_select_db($db,$dbname);
  $GETLISTSQL="SELECT loc,participant,`ILL Email`,suspend,system,Name FROM `SENYLRC-SEAL2-Library-Data` where alias = '$locationalias' ";
  $result=mysqli_query($db, $GETLISTSQL);
  $row = mysqli_fetch_row($result);
  $libparticipant = $row;
  return $libparticipant;
}

function check_itemtype ($destill,$itemtype) {
  require '../seal_script/seal_db.inc';
  $db = mysqli_connect($dbhost, $dbuser, $dbpass);
  mysqli_select_db($db,$dbname);
  $GETLISTSQL="SELECT book,av,journal,reference,ebook FROM `SENYLRC-SEAL2-Library-Data` where loc = '$destill' ";
  $result=mysqli_query($db, $GETLISTSQL);
  while($row = $result->fetch_assoc() ) {
    if ((strcmp($itemtype, 'book') == 0) || (strcmp($itemtype, 'book (large print)') == 0)) {
      #See if  request is for a book
      if ( $row['book']==1   ) {
      #Checking if book is allowed
        return 1;
      }
    }
    if ( (strcmp($itemtype, 'journal') == 0) || (strcmp($itemtype, 'journal (electronic)') == 0) || (strcmp($itemtype, 'newspaper') == 0) ) {
    #See if  request is for a journal
      if ( $row['journal']==1   ) {
      #Checking if journal is allowed
        return 1;
      }
    }
    if ( (strcmp($itemtype, 'book (electronic)') == 0) || (strcmp($itemtype, 'web') == 0) ) {
    #See if  request is for ebook
      if ( $row['ebook']==1   ) {
      #Checking if e-book is allowed
        return 1;
      }
    }
    if ( (strpos($itemtype, 'recording') !== FALSE) || (strpos($itemtype, 'video') !== FALSE) || (strpos($itemtype, 'audio') !== FALSE) ){
    #See if  request is  audio video related
      if ( $row['av']==1   ) {
      #Checking if AV is allowed
        return 1;
      }
    }
    if ( (strcmp($itemtype, 'other') == 0) || (strcmp($itemtype, 'music-score') == 0) || (strcmp($itemtype, 'map') == 0) || (strcmp($itemtype, 'other (electronic)') == 0) ) {
    #See if  request is for reference
      if ( $row['reference']==1   ) {
      #Checking if reference is allowed
        return 1;
      }
    }
  }
  return 0; #Matched none of the above
} # end check_itemtype

function normalize_availability($itemavail) {
  $itemavail = str_replace (" ","", $itemavail);
  $itemavail = str_replace ("\n","", $itemavail);
  switch ($itemavail) {
    case "-":
      return 1;
      break;
    case "AVAILABLE":
      return 1;
      break;
    case "Available":
      return 1;
      break;
    case "available":
      return 1;
      break;
    case "CheckedIn":
      return 1;
      break;
    default:
      return 0;
  }
}

function primo_adjustlocation($itemlocation){
  # Takes a string like 'Bumble Library / Upper Floor' and returns ''Bumble Library'
  $simplelocation = substr ($itemlocation, 0, strpos($itemlocation, '/')-1);
  return $simplelocation;
}

function normalize_availability_NCLS($itemavail) {
  #NCLS has lots of availability statuses, so this fails just the un-loanable ones.
  $itemavail = str_replace (" ","", $itemavail);
  $itemavail = str_replace ("\n","", $itemavail);
  if (strpos($itemavail, '/') !== false) {
    $itemavail = "DATED";
  }
  switch ($itemavail) {
    case "DATED":
      return 0;
      break;
    case "ADULT-HOM":
      return 0;
      break;
    case "BHM-LOAN":
      return 0;
      break;
    case "BIKEMOBILE":
      return 0;
      break;
    case "BRO-LOAN":
      return 0;
      break;
    case "BVF-LOAN":
      return 0;
      break;
    case "CATALOGING":
      return 0;
      break;
    case "CHECKEDOUT":
      return 0;
      break;
    case "CCF-LOAN":
      return 0;
      break;
    case "DAMAGED":
      return 0;
      break;
    case "DISCARD":
      return 0;
      break;
    case "EDW-LOAN":
      return 0;
      break;
    case "ELL-LOAN":
      return 0;
      break;
    case "GCF-LOAN":
      return 0;
      break;
    case "HOLDS":
      return 0;
      break;
    case "HOP-LOAN":
      return 0;
      break;
    case "ILL":
      return 0;
      break;
    case "INPROCESS":
      return 0;
      break;
    case "INSHIPPING":
      return 0;
      break;
    case "INTERNET":
      return 0;
      break;
    case "INTRANSIT":
      return 0;
      break;
    case "JLBOCES":
      return 0;
      break;
    case "LAF-LOAN":
      return 0;
      break;
    case "LONGOVRDUE":
      return 0;
      break;
    case "LOST":
      return 0;
      break;
    case "LOST-ASSUM":
      return 0;
      break;
    case "LOST-CLAIM":
      return 0;
      break;
    case "MISSING":
      return 0;
      break;
    case "OSC-LOAN":
      return 0;
      break;
    case "OSWBOCES":
      return 0;
      break;
    case "OTHER-LOAN":
      return 0;
      break;
    case "PROCESSING":
      return 0;
      break;
    case "RCF-LOAN":
      return 0;
      break;
    case "REPAIR":
      return 0;
      break;
    case "RIC-LOAN":
      return 0;
      break;
    case "RIVRVIEWCF":
      return 0;
      break;
    case "ROD-LOAN":
      return 0;
      break;
    case "SPECIALNH":
      return 0;
      break;
    case "STAFFONLY":
      return 0;
      break;
    case "STLBOCES":
      return 0;
      break;
    case "WCF-LOAN":
      return 0;
      break;
    case "WLY-LOAN":
      return 0;
      break;
    default:
      return 1;
  }
}

function set_availability($itemavail) {
  if ($itemavail == 1) return "Available";
  if ($itemavail == 0) return "Unavailable";
  if ($itemavail == 2) return "UNKNOWN";
}

#Get the IDs needed for curl command
$jession= $_GET['jsessionid'];
$windowid= $_GET['windowid'];
$idc= $_GET['id'];
#Define the server to make the CURL request to
$reqserverurl='https://duenorth.indexdata.com/service-proxy/?command=record\\&windowid=';
#Define the CURL command
$cmd= "curl -b JSESSIONID=$jession $reqserverurl$windowid\\&id=". urlencode($idc);
#put in curl command in as html comment for development
echo "<!-- my cmd is  $cmd \n-->";
#Run the CURL to get XML data
$output = shell_exec($cmd);
#put xml in html src for development
echo "<!-- \n";
print_r ($output);
echo "\n-->\n\n";

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

echo "<p>Please review the details of your request and then select a library to send your request to.</p>";
echo "<form action='sent' method='post'>";
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
echo "<h1>Request Details</h1>";
echo "Need by date <input type='text' name='needbydate'><br><br>";
echo "<br>Borrower Public Note: (Visible to the Lender)<br>";
echo "<textarea name='reqnote' rows='2' cols='50'></textarea><br>";
echo "Borrower Private Note: (Visible only your library's staff)<br>";
echo "<textarea name='borrowerprivate' rows='2' cols='50'></textarea><br>";
echo "Is this a request for an article?";
echo "Yes <input type='radio' onclick='javascript:yesnoCheck();' name='yesno' id='yesCheck'>";
echo "No <input type='radio' onclick='javascript:yesnoCheck();' name='yesno' id='noCheck' checked='checked'><br>";
echo "<div id='ifYes' style='display:none'>";
echo "Article Title: <input size='80' type='text' name='arttile'><br>";
echo "Article Author: <input size='80' type='text' name='artauthor'><br>";
echo "Volume: <input size='80' type='text' name='artvolume'><br>";
echo "Issue:  <input type='text' name='artissue'><br>";
echo "Pages: <input type='text' name='artpage' ><br>";
echo "Issue Month: <input type='text' name='artmonth' ><br>";
echo "Issue Year: <input type='text' name='artyear' ><br>";
echo "Copyright compliance:  <select name='artcopyright'>";
echo "<option value=''></option>";
echo "<option value='ccl'>CCL</option>";
echo "<option value='ccg'>CCG</option></select></div><br>";

//XML file for request for development
#$file = 'https://duenorth.nnyln.org/killa1.xml';
#$records = new SimpleXMLElement($file, null, true); //for testing

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

echo "Requested Title: <b>" . $requestedtitle  ."  ". $requestedtitle2 . "</b><br>";
echo "Requested Author: <b>" . $requestedauthor ."</b><br>";
echo "Item Type:  " . $itemtype."<br>";
echo "Publication Date: " . $pubdate."<br>";
if (strlen($issn)>0) echo "ISSN: " . $issn."<br>";
if (strlen($isbn)>0) echo "ISBN: " . $isbn."<br>";
echo "<br>";

#Covert single quotes to code so they don't get cut off
$requestedtitle=htmlspecialchars($requestedtitle, ENT_QUOTES);
$requestedtitle2=htmlspecialchars($requestedtitle2, ENT_QUOTES);
$requestedauthor =htmlspecialchars($requestedauthor , ENT_QUOTES);
#echo "<input type='hidden' name='bibtitle' value= ' ".$requestedtitle ." : ". $requestedtitle2 ." '>";
echo "<input type='hidden' name='bibtitle' value= ' ".$requestedtitle ." ". $requestedtitle2 ." '>";
echo "<input type='hidden' name='bibauthor' value= ' ".$requestedauthor ." '>";
echo "<input type='hidden' name='bibtype' value= ' ".$itemtype ." '>";
echo "<input type='hidden' name='pubdate' value= ' ".$pubdate ." '>";
echo "<input type='hidden' name='isbn' value= ' ".$isbn ." '>";
echo "<input type='hidden' name='issn' value= ' ".$issn ." '>";

echo "<p>Select the library you would like to request from.<br>";
echo "Please limit multiple copy requests to classroom sets or book clubs.</p>";
echo "<p>This is a request for: <br>";
echo "<input type='radio' name='singlemulti' id='singleCheck' checked='checked' onclick='javascript:multiRequest();'> a single copy <input type='radio' name='singlemulti' id='multiCheck' onclick='javascript:multiRequest();'> multiple copies<br><p>";

$failmessage=''; #Delcaring this variable
$loccount='0'; #Counts available locations
$deadlibraries = array(); #Initializes the array which keeps the unavailable libraries.
foreach ($records->location as $location) { #Locations loop start
  $catalogtype = find_catalog($location['name']);
  $urlrecipe = $location->{'md-url_recipe'};
  $mdid = $location->{'md-id'};
  foreach ($location->holdings->holding as $holding) { #generic holding loop start
    $itemavail=$holding->localAvailability;
    if ($catalogtype == "OPALS") {$itemavail=$itemavail>0?$itemavail="-":$itemavail="0";} #OPALS might return (-1 through +X)
    if ($location['name'] == "NCLS") {
      $itemavail=normalize_availability_NCLS($itemavail);
    } else {
      $itemavail=normalize_availability($itemavail); #0=No, 1=Yes
    }
    $itemavailtext=set_availability($itemavail);
    $itemcallnum=$holding->callNumber;
    $itemcallnum=htmlspecialchars($itemcallnum,ENT_QUOTES); #Sanitizes callnumbers with special characters in them
    $itemlocation=$holding->localLocation; #Gets the alias
    if ($catalogtype == "Worldcat" || $catalogtype == "Millennium") $itemlocation=$location['name'];
    if ($catalogtype == "Primo") $itemlocation=primo_adjustlocation($itemlocation);
    $locationinfo=find_locationinfo($itemlocation);
    $itemlocation=htmlspecialchars($itemlocation,ENT_QUOTES); #Sanitizes locations with special characters in them
    $destill=$locationinfo[0]; #Destination ILL Code
    $destpart=$locationinfo[1]; #0=No, 1=Yes
    $destemail=$locationinfo[2]; #Destination emails
    $destsuspend=$locationinfo[3]; #0=No, 1=Yes
    $destlibsystem=$locationinfo[4]; #Destination library system
    $destlibname=$locationinfo[5]; #Destination library name
    $destlibname=htmlspecialchars($destlibname,ENT_QUOTES); #Sanitizes library names with special characters in them
    $desttypeloan=check_itemtype($destill,$itemtype); #0=No, 1=Yes
    if ( ($catalogtype == "Innovative") && ($itemlocation == "ODY Folio") ) $desttypeloan=1;
    $itemlocallocation=$itemlocation; #Needed in sent.php

    echo "<!-- \n";
    echo "catalogtype: $catalogtype \n";
    echo "itemavail: $itemavail (1) \n";
    echo "itemavailtext: $itemavailtext \n";
    echo "itemlocallocation: $itemlocallocation \n";
    echo "itemlocation: $itemlocation \n";
    echo "destill: $destill \n";
    echo "destpart: $destpart (1)\n";
    echo "destemail: $destemail \n";
    echo "destsuspend: $destsuspend (0)\n";
    echo "destlibsystem: $destlibsystem \n";
    echo "destlibname: $destlibname \n";
    echo "desttypeloan: $desttypeloan (1)\n";
    echo "failmessage: $failmessage\n";
    echo "--> \n\n";

    $destfail=0; #0=No, 1=Yes
    if ( $itemavail == 0 ) {
      $destfail = 1;
      $failmessage = "Material unavailable, see source ILS/LMS for details";
    }
    if ( $destpart == 0 ) {
      $destfail = 1;
      $failmessage = "Library not particpating in DueNorth";
    }
    if ( strlen($destemail) < 2 ) {
      $destfail = 1;
      $failmessage = "Library has no ILL email configured";
    }
    if ( $destsuspend == 1 ) {
      $destfail = 1;
      $failmessage = "Library not loaning / closed";
    }
    if ( $desttypeloan == 0 ) {
      $destfail = 1;
      $failmessage = "Library not loaning this material type";
    }
    if ( ($destlibsystem == $field_home_library_system[0]['value']) && ($field_filter_own_system[0]['value'] == 1) ) {
      $destfail = 1;
      $failmessage = "Library a member of your system, please request through your ILS/LMS";
    }
    if ( $destill == "" ) {
      $destfail = 1;
      $destlibname = $itemlocation;
      $destlibsystem = "Unknown";
      $failmessage = "No alias match in DueNorth directory";
    }
    if ( $destfail == 0 ) {
      $itemcallnum= preg_replace('/[:]/', ' ' , $itemcallnum);
      $itemlocation= preg_replace('/[:]/', ' ' , $itemlocation);
      $itemlocallocation= preg_replace('/[:]/', ' ' , $itemlocallocation);
      echo"<div class='multiplereq'><input type='checkbox' class='librarycheck' name='destination[]' value='". $itemlocation .":".$destlibname.":".$destlibsystem.":".$itemavailtext.":".$itemcallnum.":".$itemlocallocation.":".$destemail.":".$destill."'><strong>".$destlibname."</strong> (".$destlibsystem."), Availability: $itemavailtext, Call Number:$itemcallnum  </br></div>";
      echo"<div class='singlereq'><input type='radio' class='librarycheck' name='destination[]' value='". $itemlocation .":".$destlibname.":".$destlibsystem.":".$itemavailtext.":".$itemcallnum.":".$itemlocallocation.":".$destemail.":".$destill."'><strong>".$destlibname."</strong> (".$destlibsystem."), Availability: $itemavailtext, Call Number:$itemcallnum  </br></div>";
      $loccount=$loccount+1;
    } else {
      $deadlibraries[] = "<div class='grayout'>$destlibname ($destlibsystem), $failmessage</div>";
      echo "<!-- Holding location failed checks. --> \n";
    }
  } #Generic holding loop end
  } #End generic handler
echo "</select>";
foreach ($deadlibraries as $line) {
  echo $line;
}
if ($loccount > 0) {
  echo "<br><input type=Submit value=Submit> ";
  #If we have no locations don't show submit and display error
} else {
  echo "<br><b>Sorry, no available library to route your request at this time.</b>  <a href='https://duenorth.nnyln.org'>Would you like to try another search ?</a>";
}
echo "</form>";
?>
