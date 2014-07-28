<?php //VALIDATORS
function allowedChars($string, $allowedChars){
	$result = true;
	for ($i = 0; $i<mb_strlen($string); $i++){
		$char = mb_substr($string,$i,1);
		if (mb_substr_count($allowedChars,$char) == 0){
			$result=false;
			break;
		}
	}		
	return $result;
}

function validEmailAddress($email,$dnsCheck=false) {
    return filter_var($email,FILTER_VALIDATE_EMAIL);
}

function validFullName($fullName) {
	if (count(explode(' ',$fullName))<=1) {
		return false;
	}else{
		return true;
	}
}

function valid_int($int){
	$result = false;
	if (preg_match('/^\d+$/',$int)){
		$result = true;
	}
	return $result;
}

function generatePassword ($length = 8)
{
  // start with a blank password
  $password = "";
  // define possible characters
  $possible = "0123456789bcdfghjkmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ"; 
  // set up a counter
  $i = 0; 
  // add random characters to $password until $length is reached
  while ($i < $length) { 
    // pick a random character from the possible ones
    $char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
    // we don't want this character if it's already in the password
    if (!strstr($password, $char)) { 
      $password .= $char;
      $i++;
    }
  }
  // done!
  return $password;
}

function validDate($dateStr){
	$monthDays = array(31,28,31,30,31,30,31,31,30,31,30,31); 
	$dateParts = explode('-',$dateStr);
	if (count($dateParts)!=3){
		return false;
	}
	if ($dateParts[0]<2000 || $dateParts[0]>2100){
		return false;
	}
	if ($dateParts % 4 == 0){
		$monthDays[1]=29;
	}
	if ($dateParts[1]<1 || $dateParts[1]>12){
		return false;
	}
	if ($dateParts[2]<1 || $dateParts[2]>$monthDays[$dateParts[1]-1]){
		return false;
	}
	return true;
}
