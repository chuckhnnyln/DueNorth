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

function displaymodenav($task,$pagemode,$loc,$target) {
  if ($task == "lend") {
    echo "<br>";
    switch ($pagemode) {
      case 0:
        echo "<a href='" . $target . "?loc=" . $loc . "&pagemode=1'>new</a> : OPEN : <a href='" . $target . "?loc=" . $loc . "&pagemode=2'>complete</a>";
        break;
      case 1:
        echo "NEW : open : complete";
        break;
      case 3:
        echo "new : open : COMPLETE";
        break;
    }
  } else {
    switch ($pagemode) {
      case 0:
        echo "new : OPEN : complete";
        break;
      case 3:
        echo "new : open : COMPLETE";
        break;
    }
  }
}

?>
