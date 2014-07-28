<?php
//Errors
function generateMessageBar(){
	$result='';
	if (isset($_SESSION['errorMsg'])){	
		$result ='<div class="messageBar ';
		if ($_SESSION['errorLevel']==0){
			$result.= 'messageInfo';
		} else if ($_SESSION['errorLevel']==1){
			$result.= 'messageWarning';
		} else if ($_SESSION['errorLevel']==2){
			$result.='messageError';
		}
		$result.='">'.$_SESSION['errorMsg'].'</div>';
		unset($_SESSION['errorMsg']);
		unset($_SESSION['errorLevel']);
	}
	return $result;
}

function setError($message='',$level=0,$overwrite=true){
	$changeMessage = true;
	if (isset($_SESSION['errorMsg']) && $overwrite==false){
		$changeMessage = false;
	}	
	if ($changeMessage){
		if ($message == ''){
			unset($_SESSION['errorMsg']);
			unset($_SESSION['errorLevel']);
		} else {
			$_SESSION['errorMsg']=$message;
			$_SESSION['errorLevel']=$level;
		}
	}
}
