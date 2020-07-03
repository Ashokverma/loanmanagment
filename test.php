<?php
require_once "customer.php";

echo "<pre>";

$user=new ln_master();

$username="ashok";
$password="password";



print_r($user->login($username, $password));


$name="Ashok";

$adhaar_no="1122112211227777";

$mobile_no="9999999999";

print_r($user->add_basic_customer($name, $adhaar_no, $mobile_no));

$str="ashok";
print_r($res=$user->search_customer($str));





echo $customer_id=$res["results"][0]["CUSTOMER_ID"];


print_r($user->search_loan_by_customer($customer_id));

/*$amount="1000";
$start_date="2018-09-20";
$duration="6";
$rate="10";

print_r($user->create_loan($customer_id, $amount, $start_date, $duration, $rate));
*/
$loan_id="LOAN_XED0TKLzQj";


print_r($user->show_loan_data($loan_id));

?>