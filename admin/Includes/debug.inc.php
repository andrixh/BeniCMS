<?php //DEBUG 
if (conf('DEBUG') || ($_SERVER['SERVER_NAME'] == conf('HTTP_HOST_LIVE') && conf('DEBUG_REMOTE') && isset($_SESSION['login']))) {
	ob_start();
	error_reporting(E_ALL);
	require_once ('Lib/FirePHPCore/FirePHP.class.php');
	$firephp = FirePHP::getInstance(true);
	$firephp->setOptions(array(
				'maxObjectDepth' => 10,
                'maxArrayDepth' => 10,
                'maxDepth' => 10,
                'useNativeJsonEncode' => true,
                'includeLineNumbers' => true));
	$firephp->registerErrorHandler($throwErrorExceptions=false);
	$firephp->registerExceptionHandler();
	$firephp->registerAssertionHandler($convertAssertionErrorsToExceptions=true, $throwAssertionExceptions=false);
	
	$_debug_group_depth = 0;
	$_debug_group_colors = array('#717171','#c10000','#9e770e','#4f7010','#158b87','#173c9a','#6619a8','#717171','#c10000','#9e770e','#4f7010','#158b87','#173c9a','#6619a8');

	function _t ($label='Stack Trace'){
		
			global $firephp;	
			$firephp->trace($label);

	}
	
	function _d ($value,$label=''){ // Debug Log
		
			global $firephp;
			$showValue = '';
			if (is_bool($value)){
				if ($value == true){
					$showValue = 'true';
				} else {
					$showValue = 'false';
				}
			} else {
				$showValue = $value;	
			}
			if ($label == ''){
				$firephp->log($showValue);
			} else {
				$firephp->log($showValue,$label);
			}
		
	}
	
	function _g ($label,$collapsed=false){ //Debug Group
	
			global $firephp;
			global $_debug_group_depth;
			global $_debug_group_colors;
			$firephp->group($label,array('Collapsed' => $collapsed,'Color'=>$_debug_group_colors[$_debug_group_depth]));
			$_debug_group_depth++;
		
	}
	
	function _gc ($label) { //Debug Group Collapsed
	
			global $firephp;
			_g($label, true);
		
	}
	
	function _u(){ // Debug GroupClose
	
			global $firephp;
			global $_debug_group_depth;
			$firephp->groupEnd();
			$_debug_group_depth--;
		
	}
	_gc('GLOBALS');_d($_SERVER,'$_SERVER');_d($_SESSION,'$_SESSION');_d($_ENV,'$_ENV');_d($_COOKIE,'$_COOKIE');_d($_FILES,'$_FILES');_d($_GET,'$_GET');_d($_POST,'$_POST');_u();
} else {
	error_reporting(0);
	function _t ($label='Stack Trace'){return false;}function _d ($value='',$label=''){return false;}function _g ($label='',$collapsed=false){return false;}function _gc ($label=''){return false;}function _u(){return false;}
}