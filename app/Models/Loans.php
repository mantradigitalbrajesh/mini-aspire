<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loans extends Model
{
    use HasFactory;
    protected $table = 'loans';
    
    protected function insertLoanData($insert_data_loan_array)
    {
        $result = Loans::insertGetId($insert_data_loan_array);
        return $result;
    }

    protected function getLoanData($loan_id)
    {
        $result = Loans::where('id','=',$loan_id)->first();
        return $result;
    }

    //GET All Loans Data
    protected function getAllLoansData($user_id)
    {
        $query = Loans::select('loans.*','users.name as user_name')
        ->join('users','users.id','=','loans.user_id');

        if(!is_null($user_id) && !empty($user_id))
        {
            $query->select('loans.*','users.name as user_name','scheduled_repayments.amount_to_be_paid','scheduled_repayments.payment_date','scheduled_repayments.scheduled_payment_status','scheduled_repayments.id as scheduled_payment_id','scheduled_repayments.amount_paid')->join('scheduled_repayments','scheduled_repayments.loan_id','=','loans.id');
            $query->where('loans.user_id','=',$user_id);
        }

        $result = $query->get();
        return $result;
    }

    //Update Loan status to approved
    protected function updateLoanStatus($loan_id)
    {
        $result = Loans::where('id','=',$loan_id)->update([
            'status'=>1,
            'updated_at'=>date('Y-m-d H:i:s')
        ]);
        return $result;
    }

    //Check if Loan is Approved 
    protected function checkLoanApproved($loan_id)
    {
        $result = Loans::where('id','=',$loan_id)->where('status','=',1)->first();
        return $result;
    }
    //Update Loan Amount

    protected function updateLoanAmount($loan_id,$user_id,$temp_amount,$loan_amount_to_be_paid,$loan_payment_payment_done_till_now)
    {
        // echo '<br>Entered Amount:';echo $temp_amount;
        // echo '<br>Amount to be Paid:';echo $loan_amount_to_be_paid;
        // echo '<br>Amount Paid till now:';echo $loan_payment_payment_done_till_now;echo '<br>';
        //die;
        $result = '';
        
        if($temp_amount == $loan_amount_to_be_paid)
        {
            Loans::where('id','=',$loan_id)
            ->where('user_id','=',$user_id)
            ->where('loan_payment_status','=',0)
            ->update([
                'loan_payment_done'=>$temp_amount,
                'loan_payment_status'=>1,
                'updated_at'=>date('Y-m-d H:i:s')
            ]);
            $result = "PAID";

        }

        if($loan_amount_to_be_paid > $loan_payment_payment_done_till_now && $loan_payment_payment_done_till_now == 0)
        {
            if($loan_amount_to_be_paid < ($temp_amount+$loan_payment_payment_done_till_now) && $loan_payment_payment_done_till_now == 0)
            {
                $result = "OVERPAID";
                return $result;
            }
            Loans::where('id','=',$loan_id)
            ->where('user_id','=',$user_id)
            ->where('loan_payment_status','=',0)
            ->update([
                'loan_payment_done'=>$temp_amount,
                'updated_at'=>date('Y-m-d H:i:s')
            ]);
            $result = "PAID";
        }

        if($loan_amount_to_be_paid > $loan_payment_payment_done_till_now && $loan_payment_payment_done_till_now != 0)
        {
            if($loan_amount_to_be_paid < ($temp_amount+$loan_payment_payment_done_till_now) && $loan_payment_payment_done_till_now != 0)
            {
                $result = "OVERPAID";
                return $result;
            }
            $amnt = $loan_payment_payment_done_till_now + $temp_amount;
            Loans::where('id','=',$loan_id)
            ->where('user_id','=',$user_id)
            ->where('loan_payment_status','=',0)
            ->update([
                'loan_payment_done'=>$amnt,
                'updated_at'=>date('Y-m-d H:i:s')
            ]);
            $result = "PAID";
            $check_if_loan_amount_fully_paid  = $this->getLoanData($loan_id);
            if(isset($check_if_loan_amount_fully_paid) && !empty($check_if_loan_amount_fully_paid))
            {
                if($check_if_loan_amount_fully_paid->loan_payment_done == $check_if_loan_amount_fully_paid->amount )
                {
                    Loans::where('id','=',$loan_id)
                    ->where('user_id','=',$user_id)
                    ->where('loan_payment_status','=',0)
                    ->update([
                        'loan_payment_status'=>1,
                        'updated_at'=>date('Y-m-d H:i:s')
                    ]);
                    $result = "PAID";
                }
            }
            
        }
        return $result;
    }
}
