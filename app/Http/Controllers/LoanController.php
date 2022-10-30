<?php

namespace App\Http\Controllers;
use App\Models\Loans;
use App\Models\User;
use App\Models\ScheduledRepayments;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    //Function To Create a Loan from custom
    public function createLoan(Request $request)
    {
        try
        {
            $user_id = '';
            $insert_data_loan_array = '';
            $get_token = $request->header('Authorization');
            if(isset($get_token) && !empty($get_token))
            {
                $get_user_data = User::checkUserExists($get_token);
                if(isset($get_user_data) && !empty($get_user_data))
                {
                    $user_id = $get_user_data->id;
                    $get_body_data = $request->all();
                    if(!isset($get_body_data['amount']) || empty($get_body_data['amount']))
                    {
                        return response()->json(['error' => 'Amount Field is Required'], 400);
                    }
                    if(!isset($get_body_data['tenure']) || empty($get_body_data['tenure']))
                    {
                        return response()->json(['error' => 'Tenure Field is Required'], 400);
                    }
                    if(isset($get_body_data['tenure']) && !empty($get_body_data['tenure']) && is_float($get_body_data['tenure']))
                    {
                        return response()->json(['error' => 'Tenure Must be a Number ! Decimal Not Allowed !'], 400);
                    }
                    if(isset($get_body_data['tenure']) && !empty($get_body_data['tenure']) && (!is_numeric($get_body_data['amount']) && !is_float($get_body_data['amount']) ))
                    {
                        return response()->json(['error' => 'Amount Must be a Number'], 400);
                    }
                    $amount =  (isset($get_body_data['amount']) && !empty($get_body_data['amount'])) ? $get_body_data['amount'] : "";
                    $loan_term =  (isset($get_body_data['tenure']) && !empty($get_body_data['tenure'])) ? $get_body_data['tenure'] : "";
                    $amount_to_paid = $amount/$loan_term;
                    $insert_data_loan_array = array(
                        'user_id' => $user_id,
                        'amount' => $amount,
                        'loan_term' => $loan_term,
                        'created_at'=>date('Y-m-d H:i:s'),
                        'updated_at'=>date('Y-m-d H:i:s')
                    );
                    $insert_loan_data = Loans::insertLoanData($insert_data_loan_array);
                    if(isset($insert_loan_data) && !empty($insert_loan_data))
                    {
                        $scheduled_repayments_data_array = [];
                        $temp_array = [];
                        $loan_id = $insert_loan_data;
                        $payment_date = '';
                        for ($i = 1; $i <= $loan_term; $i++)  
                        {  
                            $payment_date = date('Y-m-d', strtotime('+'.($i*7).'days'));
                            $scheduled_repayments_data_array = array(
                                'user_id'=>$user_id,
                                'loan_id'=>$loan_id,
                                'amount_to_be_paid'=>$amount_to_paid,
                                'payment_date'=>$payment_date,
                                'created_at'=>date('Y-m-d H:i:s'),
                                'updated_at'=>date('Y-m-d H:i:s')
                            );
                            $insert_scheduled_repayments_data = ScheduledRepayments::insertScheduledPaymentData($scheduled_repayments_data_array);
                            if(isset($insert_scheduled_repayments_data) && !empty($insert_scheduled_repayments_data))
                            {
                                $temp_array[] = $insert_scheduled_repayments_data;
                            }
                        }
                        $count_temp_array = count($temp_array);
                        if($count_temp_array == $loan_term)
                        {
                            $get_loan_data = Loans::getLoanData($loan_id);
                            $loan_status = ($get_loan_data->status == 0) ? 'Pending' : "Approved";
                            return response()->json(['Message' => 'Loan Application Submitted Successfully !', 'Loan Id'=>$get_loan_data->id,'Amount to Pay' => $get_loan_data->amount,'Loan Term'=>$get_loan_data->loan_term,'Loan Status'=>$loan_status], 200);
                        }
                        else
                        {
                            return response()->json(['error' => 'Some Error While Submitting Loan ! Contact Developer'], 500);
                        }
                    }
                }
                else
                {
                    return response()->json(['error' => 'Invalid Credentials'], 401);
                }
            }
            else
            {
                return response()->json(['error' => 'You are Unauthorised to Apply for Loan'], 403);
            }
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }

    //Function To Get all Loans Data For Admin
    public function getAllLoans(Request $request)
    {
        try
        {
            $data_array = [];
            $get_token = $request->header('Authorization');
            if(isset($get_token) && !empty($get_token))
            {
                $get_user_data = User::checkAdminExists($get_token);
                if(isset($get_user_data) && !empty($get_user_data))
                {
                    $get_all_loan_data = Loans::getAllLoansData($user_id=NULL);
                    if(isset($get_all_loan_data) && !empty($get_all_loan_data))
                    {
                        $sr = 1;
                       // echo '<pre>';print_r($get_all_loan_data);die;
                        foreach($get_all_loan_data as $key=>$val)
                        {

                            $data_array[$sr]['loan_id'] = $val->id;
                            $data_array[$sr]['user_id'] = $val->user_id;
                            $data_array[$sr]['user_name'] = $val->user_name;
                            $data_array[$sr]['amount'] = '$'.$val->amount;
                            $data_array[$sr]['loan_term'] = $val->loan_term;
                            $data_array[$sr]['status'] = ($val->status == 0) ? 'Pending' : 'Approved';
                            $data_array[$sr]['loan_payment_done'] =  '$'.$val->loan_payment_done;
                            $data_array[$sr]['loan_payment_status'] = ($val->loan_payment_status == 0) ? 'UnPaid' : 'Paid';
                            $data_array[$sr]['Date'] = date('Y-m-d',strtotime($val->created_at));
                            $sr++;
                        }
                        return response()->json($data_array, 200);
                    }
                    else
                    {
                        return response()->json(['Message' => 'No Data Found'], 200);
                    }
                }
                else
                {
                    return response()->json(['error' => 'Invalid Credentials or You Are Not Authorized to Access the Data'], 401);
                }
            }
            else
            {
                return response()->json(['error' => 'You are Unauthorised to Approve the Loan'], 403);
            }
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }

    //Function To Approve Loan For Admin
    public function approveLoan(Request $request)
    {
        try
        {
            $get_token = $request->header('Authorization');
            if(isset($get_token) && !empty($get_token))
            {
                $get_user_data = User::checkAdminExists($get_token);
                $check_loan_data_array = [];
                if(isset($get_user_data) && !empty($get_user_data))
                {
                    $data = $request->all();
                    //echo '<pre>';print_r($data);die;
                    if(isset($data) && !empty($data))
                    {
                        $sr = 1;
                        foreach($data as $k=>$v)
                        {
                            //Check if Loan Id Is missing Anywhere in Request
                            if(!isset($v['loan_id']) || empty($v['loan_id']))
                            {
                                return response()->json(['error' => 'Loan Id is Required'], 400);
                            }
                            if(isset($v['loan_id']) && !empty($v['loan_id']))
                            {
                                $check_if_loan_exists = Loans::getLoanData($v['loan_id']);
                                if(empty($check_if_loan_exists))
                                {
                                    $check_loan_data_array['Loan_not_updated']['Message'][] = 'Loan Not Found';
                                    $check_loan_data_array['Loan_not_updated']['Loan_id'][] = $v['loan_id'];
                                }
                                else
                                {
                                    $update_loan_status = Loans::updateLoanStatus($v['loan_id']);
                                    if(!empty($update_loan_status))
                                    {
                                        $check_loan_data_array['Loan_updated']['Message'][] = 'Loan Updated';
                                        $check_loan_data_array['Loan_updated']['Loan_id'][] = $v['loan_id'];
                                    }
                                }
                            }
                            $sr++;
                        }
                        return response()->json($check_loan_data_array, 200);
                    }
                }
                else
                {
                    return response()->json(['error' => 'Invalid Credentials or You Are Not Authorized to Access the Data'], 401);
                }
            }
            else
            {
                return response()->json(['error' => 'You are Unauthorised to Approve the Loan'], 403);
            }
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }

    //Show user specific loan details to customer
    public function getCustomerLoan(Request $request)
    {
        try
        {
            $data_array = [];
            $get_token = $request->header('Authorization');
            if(isset($get_token) && !empty($get_token))
            {
                $get_user_data = User::checkUserExists($get_token);
                if(isset($get_user_data) && !empty($get_user_data))
                {
                    $user_id = $get_user_data->id;
                    $get_all_loan_data = Loans::getAllLoansData($user_id);
                    if(isset($get_all_loan_data) && !empty($get_all_loan_data))
                    {
                        $sr = 1;
                        foreach($get_all_loan_data as $key=>$val)
                        {
                            $data_array[$user_id.'|'.$val->id]['loan_id'] = $val->id;
                            $data_array[$user_id.'|'.$val->id]['user_id'] = $val->user_id;
                            $data_array[$user_id.'|'.$val->id]['user_name'] = $val->user_name;
                            $data_array[$user_id.'|'.$val->id]['amount'] = '$'.$val->amount;
                            $data_array[$user_id.'|'.$val->id]['loan_term'] = $val->loan_term;
                            $data_array[$user_id.'|'.$val->id]['Admin_approval_status'] = ($val->status == 0) ? 'Pending' : 'Approved';
                            $data_array[$user_id.'|'.$val->id]['loan_payment_done'] =  '$'.$val->loan_payment_done;
                            $data_array[$user_id.'|'.$val->id]['loan_payment_status'] = ($val->loan_payment_status == 0) ? 'UnPaid' : 'Paid';
                            $data_array[$user_id.'|'.$val->id]['Loan_applied_on'] = date('Y-m-d',strtotime($val->created_at));
                            $data_array[$user_id.'|'.$val->id][$sr]['scheduled_payment_id'] = $val->scheduled_payment_id;
                            $data_array[$user_id.'|'.$val->id][$sr]['amount_to_pay'] = $val->amount_to_be_paid;
                            $data_array[$user_id.'|'.$val->id][$sr]['payment_date'] = $val->payment_date;
                            $data_array[$user_id.'|'.$val->id][$sr]['Payment_status'] = ($val->scheduled_payment_status == 0) ? 'UnPaid' : 'Paid';
                            $sr++;
                        }
                        return response()->json($data_array, 200);
                    }
                    else
                    {
                        return response()->json(['Message' => 'No Data Found'], 200);
                    }
                }
                else
                {
                    return response()->json(['error' => 'Invalid Credentials or You Are Not Authorized to Access the Data'], 401);
                }
            }
            else
            {
                return response()->json(['error' => 'You are Unauthorised to Approve the Loan'], 403);
            }
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }

    //Add Scheduled Repayment
    public function addScheduledRepayment(Request $request)
    {
        try
        {
            $get_token = $request->header('Authorization');
            if(isset($get_token) && !empty($get_token))
            {
                $get_user_data = User::checkUserExists($get_token);
                $check_loan_data_array = [];
                if(isset($get_user_data) && !empty($get_user_data))
                {
                    $data = $request->all();
                    $user_id = $get_user_data->id;
                    if(!isset($data['loan_id']) || empty($data['loan_id']))
                    {
                        return response()->json(['error' => 'loan_id Field is Required'], 400);
                    }
                    if(!isset($data['amount']) || empty($data['amount']))
                    {
                        return response()->json(['error' => 'amount Field is Required'], 400);
                    }
                    $loan_id = (isset($data['loan_id']) && !empty($data['loan_id'])) ? $data['loan_id'] : "";
                    $amount = (isset($data['amount']) && !empty($data['amount'])) ? $data['amount'] : "";
                    $temp_amount = (isset($data['amount']) && !empty($data['amount'])) ? $data['amount'] : "";
                    //Update Loan Amount Paid In loans Table
                    $check_loan_amount_completed = Loans::getLoanData($loan_id);
                    if(isset($check_loan_amount_completed) && !empty($check_loan_amount_completed))
                    {
                        //Check if Loan is Approved by admin or not
                        $check_loan_approval = Loans::checkLoanApproved($loan_id);
                        if(!isset($check_loan_approval) || empty($check_loan_approval))
                        {
                            return response()->json(['Message' => 'Loan Not Approved By Admin'], 403);
                        }
                        $loan_amount_to_be_paid = $check_loan_amount_completed->amount;
                        $loan_payment_payment_done_till_now = $check_loan_amount_completed->loan_payment_done;
                        if($loan_amount_to_be_paid == $loan_payment_payment_done_till_now)
                        {
                            return response()->json(['Message' => 'Complete Loan Amount is Already Paid'], 200);
                        }
                        else
                        {
                            $update_the_loan_amount_loan_table = Loans::updateLoanAmount($loan_id,$user_id,$temp_amount,$loan_amount_to_be_paid,$loan_payment_payment_done_till_now);
                            
                            if($update_the_loan_amount_loan_table == "OVERPAID")
                            {
                                $pending_amnt = $loan_amount_to_be_paid - $loan_payment_payment_done_till_now;
                                return response()->json(['Message' => 'Entered Amount is Greater than the pending amount to be Paid ! Pending Amount to be Paid is $'.$pending_amnt.''], 200);
                            }
                            //Get the scheduled_repayments amount from scheduled_repayments table which are not paid
                            $get_scheduled_repayments = ScheduledRepayments::getScheduledRepayments($loan_id,$user_id);
                            //echo '<pre>';print_r($update_the_loan_amount_loan_table);
                            if(isset($get_scheduled_repayments) && !empty($get_scheduled_repayments) && isset($update_the_loan_amount_loan_table) && !empty($update_the_loan_amount_loan_table))
                            {
                                foreach($get_scheduled_repayments as $k=>$v)
                                {
                                    //echo '<br>';echo $amount;
                                    $min_payment_date = $v->min_payment_date;
                                    $amount_to_be_paid = $v->amount_to_be_paid;
                                    $temp_amount_to_be_paid = $v->amount_to_be_paid;
                                    $temp_amount_paid = $v->amount_paid;
                                    if($temp_amount > 0)
                                    {
                                        if($amount > $amount_to_be_paid)
                                        {
                                           $update_scheduled_repayment = ScheduledRepayments::updateAmountPaid($loan_id,$user_id,$amount_to_be_paid,$min_payment_date,$temp_amount_to_be_paid,$temp_amount_paid);
                                           $amount = $amount-$amount_to_be_paid;
                                        }
                                        else
                                        {
                                            $update_scheduled_repayment = ScheduledRepayments::updateAmountPaid($loan_id,$user_id,$amount,$min_payment_date,$temp_amount_to_be_paid,$temp_amount_paid);
                                        }
                                    }                                   
                                    $temp_amount = $temp_amount - $amount_to_be_paid;
                                }
                                return response()->json(['Message' => 'Scheduled RePayment Done Successfully !'], 200);
                            }
                            else
                            {
                                return response()->json(['Message' => 'No Amount Left to Pay'], 200);
                            }
                        }
                    }
                    else
                    {
                        return response()->json(['error' => 'Loan Application Not Found'], 404);
                    }
                }
                else
                {
                    return response()->json(['error' => 'Invalid Credentials or You Are Not Authorized to Access the Data'], 401);
                }
            }
            else
            {
                return response()->json(['error' => 'You are Unauthorised to Approve the Loan'], 403);
            }
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }
}
