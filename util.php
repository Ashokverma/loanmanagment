<?php
//REGEX
$filter_adhaar_no='/(?=(?:.{14}|.{16})$)[0-9]*$/mi';;

//

//Filter Function Start.
// Customer Name
function filter_address($input){
	return true;
}
function filter_mobileno($input){
	return true;
}
function filter_lnm_username($username){// LNM=> Loan Master
	return true;
}
function filter_lnm_password($password){
	return true;
}
function filter_adhaar($input){
	preg_match_all($GLOBALS['filter_adhaar_no'], $input, $matches, PREG_SET_ORDER, 0);
	if(count($matches)==1){
		return true;
	}else{
		return false;
	}
}
function filter_cname($input){
	return $input;
}
function filter_date($date){
	try {
		new DateTime($date);
		return true;
	} catch (Exception $e) {
		return false;
	}
}
function filter_amount($number){ // To be completed.
	return true;
}
function filter_number($number){ // To be completed.
	return true;
}
// Filter Function End.
function generateRandomString($length = 10) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}
function debug_echo($str){
	if(DEBUG_STATUS){
		echo $str."<br>";
	}
}
function returnStatus($status,$msg,$array=""){
	$obj=array();
	$obj["status"]=$status;
	$obj["msg"]=$msg;
	if(is_array($array)){
		foreach($array as $x => $x_value){
			$obj[$x]=$x_value;
		}
	}
	return $obj;
}
function passEncrypt($password){
	return md5($password);
}
function array2str($array,$separator){
	$output="";
	if(is_array($array)){
	foreach($array as $ele){
		$output.=$ele.$separator;
	}
	return $output;
	}else{
		return $array.$separator;
	}
}

?>