<?php
### _tasks.php :: this is a library containing functions for lender_tasks and borrower_tasks.

function build_notes($reqnote,$lendnote) {
  $displaynotes = "";
  if ( (strlen($reqnote) > 2) && (strlen($lendnote) > 2) ) {
    $displaynotes = $reqnote . "</br>Lender Note: " . $lendnote;
  }
  if ( (strlen($reqnote) > 2) && (strlen($lendnote) < 2) ) $displaynotes=$reqnote;
  if ( (strlen($reqnote) < 2) && (strlen($lendnote) > 2) ) $displaynotes= "Lender Note: " . $lendnote;
  return $displaynotes;
}

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

function displaymodenav($task,$pagemode,$loc,$target,$hasnew) {
  $imagepath = "/sites/duenorth.nnyln.org/files/interface/";
  if ($task == "lend") {
    switch ($pagemode) {
      case 0: #Open
        if ($hasnew == "1") {
          echo "<a href='" . $target . "?loc=" . $loc . "&pagemode=1'><img src='" . $imagepath . "tasks_new_burning.png'></a> ";
        } else {
          echo "<a href='" . $target . "?loc=" . $loc . "&pagemode=1'><img src='" . $imagepath . "tasks_new_inactive.png'></a> ";
        }
        echo "<img src='" . $imagepath . "tasks_open_active.png'> ";
        echo "<a href='" . $target . "?loc=" . $loc . "&pagemode=2'><img src='" . $imagepath . "tasks_complete_inactive.png'></a> ";
        echo "<br>";
        break;
      case 1: #New
        echo "<img src='" . $imagepath . "tasks_new_active.png'> ";
        echo "<a href='" . $target . "?loc=" . $loc . "&pagemode=0'><img src='" . $imagepath . "tasks_open_inactive.png'></a> ";
        echo "<a href='" . $target . "?loc=" . $loc . "&pagemode=2'><img src='" . $imagepath . "tasks_complete_inactive.png'></a> ";
        echo "<br>";
        break;
      case 2: #Complete
        if ($hasnew == "1") {
          echo "<a href='" . $target . "?loc=" . $loc . "&pagemode=1'><img src='" . $imagepath . "tasks_new_burning.png'></a> ";
        } else {
          echo "<a href='" . $target . "?loc=" . $loc . "&pagemode=1'><img src='" . $imagepath . "tasks_new_inactive.png'></a> ";
        }
        echo "<a href='" . $target . "?loc=" . $loc . "&pagemode=0'><img src='" . $imagepath . "tasks_open_inactive.png'></a> ";
        echo "<img src='" . $imagepath . "tasks_complete_active.png'> ";
        echo "<br>";
        break;
    }
  } else {
    switch ($pagemode) {
      case 0: #Open
        echo "<img src='" . $imagepath . "tasks_open_active.png'> ";
        echo "<a href='" . $target . "?loc=" . $loc . "&pagemode=2'><img src='" . $imagepath . "tasks_complete_inactive.png'></a> ";
        break;
      case 2: #Complete
        echo "<a href='" . $target . "?loc=" . $loc . "&pagemode=0'><img src='" . $imagepath . "tasks_open_inactive.png'></a> ";
        echo "<img src='" . $imagepath . "tasks_complete_active.png'> ";
        break;
    }
  }
}

function buildsql($task,$pagemode,$loc,$filter_yes,$filter_no,$filter_expire,$filter_cancel,$filter_days,$filter_sent,$filter_noans,$sealSTAT) {
  $SQLMIDDLE ='';
  $SQLEND=" ORDER BY `index`  DESC";

  if ($filter_days == "all") {
    $SQL_DAYS = "";
  } else {
    $SQL_DAYS = " AND (DATE(`Timestamp`) BETWEEN NOW() - INTERVAL " . $filter_days . " DAY AND NOW() )";
  }

  switch ($task) {
    case "lend":
      $subject = "Destination";
      $SQLBASE="SELECT *, DATE_FORMAT(`Timestamp`, '%Y/%m/%d') FROM `$sealSTAT` WHERE `$subject` = '$loc' ";
      switch ($pagemode) {
        case 0: #Open
          $returnsql = $SQLBASE . $SQL_DAYS . "AND `fill` = 1  AND `LenderStatus` = '' OR `LenderStatus` IS NULL ". $SQLEND;
          break; #Open
        case 1: #New
          $returnsql = $SQLBASE . "and `Fill` = 3" . $SQLEND;
          break; #New
        case 2: #Complete
          if ($filter_yes == "yes") $SQLMIDDLE = "`fill`= 1 ";
          if ($filter_no == "yes") {
            if (strlen($SQLMIDDLE) > 2 ) {
              $SQLMIDDLE = $SQLMIDDLE . "OR `fill`= 0 ";
            } else {
              $SQLMIDDLE = "`fill`= 0 ";
            }
          }
          if ($filter_noans == "yes") {
            if (strlen($SQLMIDDLE) > 2 ) {
              $SQLMIDDLE = $SQLMIDDLE . "OR `fill`= 3 ";
            } else {
              $SQLMIDDLE = "`fill`= 3 ";
            }
          }
          if ($filter_expire == "yes") {
            if (strlen($SQLMIDDLE) > 2 ) {
              $SQLMIDDLE = $SQLMIDDLE . "OR `fill`= 4 ";
            } else {
              $SQLMIDDLE = "`fill`= 4 ";
            }
          }
          if ($filter_cancel == "yes") {
            if (strlen($SQLMIDDLE) > 2 ) {
              $SQLMIDDLE = $SQLMIDDLE . "OR `fill`= 6 ";
            } else {
              $SQLMIDDLE = "`fill`= 6 ";
            }
          }
          if ($filter_sent == "yes") {
            if (strlen($SQLMIDDLE) > 2 ) {
              $SQLMIDDLE = $SQLMIDDLE . "OR `LenderStatus`= 'Sent' ";
            } else {
              $SQLMIDDLE = "`LenderStatus`= 'Sent' ";
            }
          }
          $returnsql = $SQLBASE . $SQL_DAYS . " AND (" . $SQLMIDDLE . ")" . $SQLEND;
          break; #Complete
      } #Pagemode
      break; #lend
    case "borrow":
      $subject = "Requester LOC";
      $SQLBASE="SELECT *, DATE_FORMAT(`Timestamp`, '%Y/%m/%d') FROM `$sealSTAT` WHERE `$subject` = '$loc' ";
      switch ($pagemode) {
        case 0: #Open
          $SQLBASE="SELECT *, DATE_FORMAT(`Timestamp`, '%Y/%m/%d') FROM `$sealSTAT` WHERE NOT (`BorrowerStatus` <=> 'Returned') AND `$subject` = '$loc' ";
          if ($filter_yes == "yes") $SQLMIDDLE = "`fill`= 1 ";
          if ($filter_noans == "yes") {
            if (strlen($SQLMIDDLE) > 2 ) {
              $SQLMIDDLE = $SQLMIDDLE . "OR `fill`= 3 ";
            } else {
              $SQLMIDDLE = "`fill`= 3 ";
            }
          }
          if ($filter_sent == "yes") {
            if (strlen($SQLMIDDLE) > 2 ) {
              $SQLMIDDLE = $SQLMIDDLE . "OR `BorrowerStatus`= 'Arrived' ";
            } else {
              $SQLMIDDLE = "`BorrowerStatus`= 'Arrived' ";
            }
          }
          $returnsql = $SQLBASE . $SQL_DAYS . " AND (" . $SQLMIDDLE . ")" . $SQLEND;
          break; #Open
        case 2: #Complete
          if ($filter_no == "yes") $SQLMIDDLE = "`fill`= 0 ";
          if ($filter_expire == "yes") {
            if (strlen($SQLMIDDLE) > 2 ) {
              $SQLMIDDLE = $SQLMIDDLE . "OR `fill`= 4 ";
            } else {
              $SQLMIDDLE = "`fill`= 4 ";
            }
          }
          if ($filter_cancel == "yes") {
            if (strlen($SQLMIDDLE) > 2 ) {
              $SQLMIDDLE = $SQLMIDDLE . "OR `fill`= 6 ";
            } else {
              $SQLMIDDLE = "`fill`= 6 ";
            }
          }
          if ($filter_sent == "yes") {
            if (strlen($SQLMIDDLE) > 2 ) {
              $SQLMIDDLE = $SQLMIDDLE . "OR `BorrowerStatus`= 'Returned' ";
            } else {
              $SQLMIDDLE = "`BorrowerStatus`= 'Returned' ";
            }
          }
          $returnsql = $SQLBASE . $SQL_DAYS . " AND (" . $SQLMIDDLE . ")" . $SQLEND;
          break; #Complete
      } #pagemode
      break; #borrow
  } #Task
  return $returnsql;
} #buildsql
