<?php
require_once "customer.php";
//Always first intialise user, by using login function.
$user=new ln_master();  #class  

/* Admin Login create */
$user->login($username, $password);

/*New  Customer Add*/
// To Create New Customer
$user->add_basic_customer($name, $adhaar_no, $mobile_no);


// To add document details 
$user->add_customer_documents($customer_id, $adhaar_card, $pan_card, $other_docs); //Each variable is contains path to file;



// To create new loan
$user->create_loan($customer_id, $amount, $start_date, $duration, $rate);
// Show Emi Amount To be paid by customer
$user->show_emi_amount($loan_id, $date);// $date will cotain date as string in format Y-M-D on which customer came to pay and LOAN_ID
// Pay EMI temprary
$user->pay_temp_emi($loan_id, $amount, $emi_date, $date);//$emi_date is date of EMI and $date is date on which EMI was paid.
// Confirm EMI Payement
$user->pay_confirm_emi($loan_id, $emi_date);
// Show EMI Payement pending for confirmation
$user->pending_emi_confirmation($date,$days); // Both parameter are optional, $date set date and $days set interval for confirmation requests.
// Show Loan Data
$user->show_loan_data($loan_id);
// Search Customer by name,mobile,adhaar no.
$user->search_customer($str);//$str can be mobile,adhaar or name;
?>

