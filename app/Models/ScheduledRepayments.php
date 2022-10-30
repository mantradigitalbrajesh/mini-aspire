<?php

namespace App\Models;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledRepayments extends Model
{
    use HasFactory;
    protected $table = 'scheduled_repayments';

    protected function insertScheduledPaymentData($scheduled_repayments_data_array)
    {
        $result = ScheduledRepayments::insertGetId($scheduled_repayments_data_array);
        return $result;
    }

    protected function getScheduledRepayments($loan_id,$user_id)
    {
        $result = ScheduledRepayments::select(DB::raw('MIN(payment_date) as min_payment_date'),'scheduled_repayments.*')
        ->where('loan_id','=',$loan_id)
        ->where('scheduled_payment_status','=',0)
        ->where('user_id','=',$user_id)
        ->groupBy('id')
        ->get();

        return $result;
    }

    protected function updateAmountPaid($loan_id,$user_id,$amount,$min_payment_date,$temp_amount_to_be_paid,$temp_amount_paid)
    {
        //echo '<br>Entered Amount:';echo $amount;
        //echo '<br>Amount to be Paid:';echo $temp_amount_to_be_paid;
        //echo '<br>Amount Paid:';echo $temp_amount_paid;echo '<br>';
        $amount_to_pay_for_pending_amt = '';
        if($amount < $temp_amount_to_be_paid && $temp_amount_paid != 0)
        {
            $amount_to_pay_for_pending_amt = $amount - $temp_amount_paid;
            $amount_to_pay_for_pending_amt = $amount_to_pay_for_pending_amt + $temp_amount_paid;
            $result = ScheduledRepayments::where('loan_id','=',$loan_id)
            ->where('scheduled_payment_status','=',0)
            ->where('user_id','=',$user_id)
            ->where('payment_date','=',$min_payment_date)
            ->update([
                'amount_paid'=>$amount,
                'scheduled_payment_status'=>1,
                'updated_at'=>date('Y-m-d H:i:s')
            ]);
        }
        if($amount == $temp_amount_to_be_paid)
        {
            $result = ScheduledRepayments::where('loan_id','=',$loan_id)
            ->where('scheduled_payment_status','=',0)
            ->where('user_id','=',$user_id)
            ->where('payment_date','=',$min_payment_date)
            ->update([
                'amount_paid'=>$amount,
                'scheduled_payment_status'=>1,
                'updated_at'=>date('Y-m-d H:i:s')
            ]);
        }
        if($amount < $temp_amount_to_be_paid && $temp_amount_paid == 0)
        {
            $result = ScheduledRepayments::where('loan_id','=',$loan_id)
            ->where('scheduled_payment_status','=',0)
            ->where('user_id','=',$user_id)
            ->where('payment_date','=',$min_payment_date)
            ->update([
                'amount_paid'=>$amount,
                'updated_at'=>date('Y-m-d H:i:s')
            ]);
        }
        
    }
}
