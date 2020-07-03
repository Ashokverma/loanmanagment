<?php
/* LS = Loan System */
require_once 'config.php';
require_once 'util.php';
class ln_master extends PDO{
	
	private $username;
	private $login_status;
	private $Q_LOGIN;
	private $AUTH;		
	private $Q_CIDAVAILABLE;
	private $Q_ADDADHAARDOC;
	private $Q_ADDPANCARDDOC;
	private $Q_ADDOTHERDOC;
	private $Q_LOANIDAVAILABLE;
	private $Q_CREATELOANID;
	private $Q_CREATELOANEMI;
	private $Q_SHOWEMIAMOUNT;
	private $Q_PAYTEMPEMI;
	private $Q_CONFIRMTEMPEMI;
	private $Q_SHOWPENDINGEMICONFIRM;
	private $Q_LOANDETAILS;
	private $Q_LOANEMIDETAILS;
	private $Q_SEARCHCUSTOMER;
	private $Q_SEARCHLOANBYCID;
	function __construct(){
		parent::__construct($dsn = "mysql:host=".DB_HOST.";dbname=".DB_DNAME.";charset=".DB_CHRST.";",DB_USER, DB_PASS);	
		$this->login_status=false;
		$AUTH=false;
	}
	function login($username,$password){
		if(!filter_lnm_username($username)){return returnStatus(-1, "Username not in valid format.");}
		if(!filter_lnm_password($password)){return returnStatus(-1, "Password not in valid format.");}
		$this->Q_LOGIN=$this->prepare("SELECT COUNT(*),AUTH FROM `lm_master` WHERE `LM_USERNAME`=? AND `LM_PASSWORD`=?");
		$this->Q_LOGIN->execute([$username,passEncrypt($password)]);
		$result=$this->Q_LOGIN->fetch(PDO::FETCH_ASSOC);
		if($result["COUNT(*)"]==1){
			$this->username=$username;
			$this->login_status=true;
			$this->AUTH=$result["AUTH"];
			return true;
		}else{
			return returnStatus(-1, "Username or Password incorrect.");
		}
	}
	function add_basic_customer($name,$adhaar_no,$mobile_no){
		if($this->AUTH!="ADMIN"){
			return returnStatus(-1, "You are not authorised for this function.");
		}
		$tempid=generateRandomString(10);
		$this->Q_CIDAVAILABLE=$this->prepare('SELECT COUNT(*) FROM `LM_CUSTOMER` WHERE `C_ID`=?');
		while(1)
		{
			debug_echo("Checking $tempid for availablity.");
			$this->Q_CIDAVAILABLE->execute(array($tempid));
			$result=$this->Q_CIDAVAILABLE->fetch(PDO::FETCH_ASSOC);
			if($result["COUNT(*)"]==0){
				break;
			}
			$tempid=generateRandomString(10);
		}
		$this->Q_CREATECUSTOMER=$this->prepare("INSERT INTO `lm_customer`(`C_ID`, `C_NAME`, `C_ADHAAR`,`C_MOBILENO`) VALUES (?,?,?,?);");
		if(!filter_adhaar($adhaar_no)){return returnStatus(-1, "Adhaar No. Not Valid.");}
		if(!filter_cname($name)){return returnStatus(-1, "Name is not in valid format.");}
		if(!filter_mobileno($mobile_no)){return returnStatus(-1, "Mobile is not in valid format.");}
		if(($result=$this->Q_CREATECUSTOMER->execute([$tempid,$name,$adhaar_no,$mobile_no]))){
			return returnStatus(1, "User added successfully.");
		}else{
			return returnStatus(-1, "Same Credential Already exists.");
		}
	}
	function add_customer_documents($customer_id,$adhaar_card,$pan_card,$other_docs){
		$final_status;
		if($this->AUTH!="ADMIN"){
			return returnStatus(-1, "You are not authorised for this function.");
		}
		if($pan_card!=""){
			$this->Q_ADDPANCARDDOC=$this->prepare("UPDATE `lm_customer` SET `C_DOC_PAN`=? WHERE `C_ID`=?");
			$pstring=array2str($pan_card,FILENAME_SEPARATOR);
			if($this->Q_ADDPANCARDDOC->execute([$pstring,$customer_id])){
				$final_status["PANCARD_STAT"]=returnStatus(1, "Pancard Document Added Successfully.");
			}else{
				$final_status["PANCARD_STAT"]=returnStatus(-1, "Pandcard Document was not addded.");
			}
		}
		if($adhaar_card!=""){
			$this->Q_ADDADHAARDOC=$this->prepare("UPDATE `lm_customer` SET `C_DOC_ADHAAR`=? WHERE `C_ID`=?");
			$pstring=array2str($adhaar_card,FILENAME_SEPARATOR);
			if($this->Q_ADDADHAARDOC->execute([$pstring,$customer_id])){
				$final_status["ADHAAR_STAT"]=returnStatus(1, "Adhaar Card Document Added Successfully.");
			}else{
				$final_status["ADHAAR_STAT"]=returnStatus(-1, "Adhaar Card Document was not addded.");
			}
		}
		if($other_docs!=""){
			$this->Q_ADDOTHERDOC=$this->prepare("UPDATE `lm_customer` SET `C_DOC_OTHERDOC`=? WHERE `C_ID`=?");
			$pstring=array2str($other_docs,FILENAME_SEPARATOR);
			if($this->Q_ADDOTHERDOC->execute([$pstring,$customer_id])){
				$final_status["OTHERDOC_STAT"]=returnStatus(1, "Other Documents Added Successfully.");
			}else{
				$final_status["OTHERDOC_STAT"]=returnStatus(-1, "Other Documents was not addded.");
			}
		}
		return $final_status;
	}
	function create_loan($customer_id,$amount,$start_date,$duration,$rate,$type="MONTHLY"){	
		if(!filter_date($start_date)){return returnStatus(-1, "Date is Not Valid.");}
		if(!filter_amount($amount)){return returnStatus(-1, "Amount is Not Valid.");}
		if(!filter_number($duration)){return returnStatus(-1, "Duration is Not Valid.");}
		if(!filter_number($rate)){return returnStatus(-1, "Rate is Not Valid.");}
		$this->Q_CIDAVAILABLE=$this->prepare('SELECT COUNT(*) FROM `LM_CUSTOMER` WHERE `C_ID`=?');
		$this->Q_CIDAVAILABLE->execute(array($customer_id));
		$result=$this->Q_CIDAVAILABLE->fetch(PDO::FETCH_ASSOC);
		if($result["COUNT(*)"]==0){
			return returnStatus(-1, "Customer Id Not Valid.");
		}
		$date=new DateTime($start_date,new DateTimeZone('Asia/Kolkata'));
		debug_echo("Total Amount=".$total_amount=$amount+($amount*$rate/100));
		$emi_amount=$total_amount/$duration;
		$emi;
		if($type=="MONTHLY"){
			for ($i=0;$i<$duration;++$i){
				$date=date_add($date, date_interval_create_from_date_string('1 month'));
				$emi[$i]["date"]=date_format($date, 'Y-m-d');
				$emi[$i]["amount"]=(int)$emi_amount;
			}
		}else if ($type=="WEEKLY"){
			for ($i=0;$i<$duration;++$i){
				$date=date_add($date, date_interval_create_from_date_string('1 week'));
				$emi[$i]["date"]=date_format($date, 'Y-m-d');
				$emi[$i]["amount"]=(int)$emi_amount;
			}	
		}
		$loan_id="LOAN_".generateRandomString(10);
		$this->Q_LOANIDAVAILABLE=$this->prepare('SELECT COUNT(*) FROM `LM_LOAN` WHERE `LOAN_ID`=?');
		while(1)
		{
			$this->Q_LOANIDAVAILABLE->execute(array($loan_id));
			$result=$this->Q_LOANIDAVAILABLE->fetch(PDO::FETCH_ASSOC);
			if($result["COUNT(*)"]==0){
				break;
			}
			$loan_id="LOAN_".generateRandomString(10);
		}
		$this->Q_CREATELOANID=$this->prepare("INSERT INTO `lm_loan`(`C_ID`, `LOAN_ID`, `LOAN_AMOUNT`, `LOAN_DURATION`, `LOAN_TYPE`, `LOAN_STARTDATE`, `LOAN_CREATORID`) VALUES (?,?,?,?,?,?,?)");
		if($this->Q_CREATELOANID->execute([$customer_id,$loan_id,$amount,$duration,$type,$start_date,$this->username])){
			$this->Q_CREATELOANEMI=$this->prepare("INSERT INTO `lm_emi`(`EMI_AMOUNT`, `EMI_DATE`, `LOAN_ID`) VALUES (?,?,?)");
			foreach ($emi  as $e){
				if(!$this->Q_CREATELOANEMI->execute([$e["amount"],$e["date"],$loan_id])){
					return returnStatus(-1, "Some error occured During creation of Loan EMIs.");
				}
			}
			return returnStatus(1, "Loan Created.",array("loan_id"=>$loan_id,"customer_id"=>$customer_id,"loan_amount"=>$amount));
		}
	}
	private function check_loan_id($loan_id){
		$this->Q_LOANIDAVAILABLE=$this->prepare('SELECT COUNT(*) FROM `LM_LOAN` WHERE `LOAN_ID`=?');
			$this->Q_LOANIDAVAILABLE->execute(array($loan_id));
			$result=$this->Q_LOANIDAVAILABLE->fetch(PDO::FETCH_ASSOC);
			if($result["COUNT(*)"]==1){
				return true;
			}else{
				return false;
			}
	}
	function show_emi_amount($loan_id,$date){
		if(!$this->check_loan_id($loan_id)){return returnStatus(-1,"Loan Id not found");}
		if(!filter_date($date)){return returnStatus(-1, "Date is invalid.");}
		//Check with ASOK
		$rate=100;
		$this->Q_SHOWEMIAMOUNT=$this->prepare("SELECT `EMI_AMOUNT`,SUM(EMI_AMOUNT)-SUM(`EMI_AMOUNT_PAID`)+IF(`PLENTY_STATUS`=1,DATEDIFF(`EMI_PAYMENT_DATE`,`EMI_DATE`)*$rate,0)+SUM(IF(PLENTY_STATUS=1,IF(`EMI_PAYMENT_DATE`='0000-00-00',DATEDIFF('$date',`EMI_DATE`)*$rate,0),0)) AS 'TOTAL_AMOUNT',IF(`PLENTY_STATUS`=1,DATEDIFF(`EMI_PAYMENT_DATE`,`EMI_DATE`)*$rate,0)+SUM(IF(PLENTY_STATUS=1,IF(`EMI_PAYMENT_DATE`='0000-00-00',DATEDIFF('$date',`EMI_DATE`)*$rate,0),0)) AS LATE_CHARGES,SUM(EMI_AMOUNT)-SUM(`EMI_AMOUNT_PAID`) AS EMI_AMOUNT2 FROM `lm_emi` WHERE EMI_DATE<='$date' AND LOAN_ID=?");
		$this->Q_SHOWEMIAMOUNT->execute([$loan_id]);
		if($result=$this->Q_SHOWEMIAMOUNT->fetch(PDO::FETCH_ASSOC)){
			return returnStatus(1, "Amount To Be Paid",array("TOTAL_AMOUNT"=>$result["TOTAL_AMOUNT"],"LATE_CHARGES"=>$result["LATE_CHARGES"],"EMI_AMOUNT"=>$result["EMI_AMOUNT"]));
		}else{
			return returnStatus(-1, "Some Error Occured.");
		}
	}
	function pay_temp_emi($loan_id,$amount,$emi_date,$date){
		if($this->AUTH!="OPERA"&$this->AUTH!="ADMIN"){
			return returnStatus(-1, "You are not authorised for this function.");
		}
		if(!$this->check_loan_id($loan_id)){return returnStatus(-1,"Loan Id not found");}
		if(!filter_date($date)){return returnStatus(-1, "Date is invalid.");}
		if(!filter_date($emi_date)){return returnStatus(-1, "Date is invalid.");}
		$this->Q_PAYTEMPEMI=$this->prepare("UPDATE lm_emi SET EMI_AMOUNT_PAID=?, EMI_PAYMENT_DATE=?,EMI_STATUSTEMP=? WHERE LOAN_ID=? AND EMI_DATE=?");
		if($this->Q_PAYTEMPEMI->execute([$amount,$date,1,$loan_id,$emi_date])){
			return returnStatus(1, "Payment Successfully Recieved.");	
		}else{
			return returnStatus(1, "Payment was not successfully recieved.");
		}		
	}
	function pay_confirm_emi($loan_id,$emi_date,$reset_flag=false){
		if($emi_date==""){
			$emi_date=date("Y-m-d");
		}
		if(!$this->check_loan_id($loan_id)){return returnStatus(-1,"Loan Id not found");}
		if(!filter_date($emi_date)){return returnStatus(-1, "Date is invalid.");}
		$this->Q_CONFIRMTEMPEMI=$this->prepare("UPDATE lm_emi SET EMI_STATUSCONFIRM=".(($reset_flag)?0:1)." WHERE LOAN_ID=? and EMI_DATE=?");
		if($this->Q_CONFIRMTEMPEMI->execute([$loan_id,$emi_date])){
			return returnStatus(1, "Payment was successfully confirmed.");
		}else{
			return returnStatus(1, "Payment was not successfully confirmed.");
		}
		
	}
	function pending_emi_confirmation($date="",$days=false){
		if($date==""){
			$date=date("Y-m-d");
		}
		$duration="DATEDIFF(`EMI_DATE`,$date)<".$days;
		if(!$days){
			$duration=1;
		}
		if(!filter_date($date)){return returnStatus(-1, "Date is invalid.");}
		$this->Q_SHOWPENDINGEMICONFIRM=$this->prepare("SELECT `LOAN_ID`,`EMI_AMOUNT`,`EMI_DATE`,`EMI_AMOUNT_PAID` FROM `lm_emi` WHERE $duration AND `EMI_STATUSTEMP`=1");
		$this->Q_SHOWPENDINGEMICONFIRM->execute([]);
		$result=$this->Q_SHOWPENDINGEMICONFIRM->fetchAll(PDO::FETCH_ASSOC);
		return returnStatus(1,"",$result);
	}
	public function show_loan_data($loan_id){
		if(!$this->check_loan_id($loan_id)){return returnStatus(-1,"Loan Id not found");}
		$final_data=array();
		$this->Q_LOANDETAILS=$this->prepare("SELECT CUST.C_NAME AS CUSTOMERNAME,CUST.C_ADHAAR AS ADHAARNUMBER,CUST.C_MOBILENO AS MOBILE,EMI.`LOAN_ID` AS LOAN_UNIQUEID,EMI.`LOAN_AMOUNT` AS AMOUNT,EMI.LOAN_STARTDATE AS STARTDATE,EMI.`LOAN_DURATION`,EMI.`LOAN_TYPE`,EMI.`LOAN_CREATORID` FROM `lm_loan` EMI INNER JOIN lm_customer CUST ON  EMI.`C_ID`=CUST.C_ID AND EMI.LOAN_ID=?");
		$this->Q_LOANDETAILS->execute([$loan_id]);
		$final_data["LOAN_DETAILS"]=$this->Q_LOANDETAILS->fetch(PDO::FETCH_ASSOC);
		$this->Q_LOANEMIDETAILS=$this->prepare("SELECT `EMI_AMOUNT`,`EMI_DATE` AS DUE_DATE,`EMI_AMOUNT_PAID`,`EMI_PAYMENT_DATE` AS PAYEMENT_DATE FROM `lm_emi` WHERE `LOAN_ID`=?");
		$this->Q_LOANEMIDETAILS->execute([$loan_id]);
		$final_data["EMI_DETAILS"]=$this->Q_LOANEMIDETAILS->fetchAll(PDO::FETCH_ASSOC);
		return returnStatus(1,"Loan Details Found",array("results"=>$final_data));
	}
	public function search_customer($str){
		if($str==""){
			return returnStatus(-1, "Empty search term.");
		}
		$str="%".$str."%";
		//$this->Q_SEARCHCUSTOMER=$this->prepare("SELECT `C_ID` AS CUSTOMER_ID,`C_NAME` AS CUSTOMER_NAME,`C_ADHAAR` AS CUSTOMER_ADHAAR,`C_MOBILENO` AS MOBILE FROM `lm_customer` WHERE `C_NAME` LIKE ? OR `C_ADHAAR` LIKE ? OR `C_MOBILENO` LIKE ?");
		$this->Q_SEARCHCUSTOMER=$this->prepare("SELECT lm_loan.C_ID AS CUSTOMER_ID,`C_NAME` AS CUSTOMER_NAME,`C_ADHAAR` AS CUSTOMER_ADHAAR,`C_MOBILENO` AS MOBILE,lm_loan.LOAN_ID FROM `lm_customer` inner join lm_loan ON (`C_NAME` LIKE ? OR `C_ADHAAR` LIKE ? OR `C_MOBILENO` LIKE ? )and lm_loan.C_ID=lm_customer.C_ID");
		if($this->Q_SEARCHCUSTOMER->execute([$str,$str,$str])){
		$result=$this->Q_SEARCHCUSTOMER->fetchAll(PDO::FETCH_ASSOC);
		return returnStatus(1,"Search Result successful.",array("results"=>$result));
		}else{
		return returnStatus(-1,"Search was not successful.");
		}
	}
	public function search_loan_by_customer($customerid){
		$str=$customerid;
		if($str==""){
			return returnStatus(-1, "Empty search term.");
		}
		//$str="%".$str."%";
		$this->Q_SEARCHLOANBYCID=$this->prepare("SELECT DISTINCT(LOAN_ID),LOAN_AMOUNT FROM `lm_loan` WHERE C_ID=?");
		if($this->Q_SEARCHLOANBYCID->execute([$str])){
		$result=$this->Q_SEARCHLOANBYCID->fetchAll(PDO::FETCH_ASSOC);
		return returnStatus(1,"Search Result successful.",array("results"=>$result));
		}else{
		return returnStatus(-1,"Search was not successful.");
		}
	}
}

?>