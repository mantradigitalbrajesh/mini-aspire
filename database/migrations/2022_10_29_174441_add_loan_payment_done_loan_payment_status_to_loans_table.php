<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLoanPaymentDoneLoanPaymentStatusToLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->string('loan_payment_done')->default('0')->after('status');
            $table->string('loan_payment_status')->default('0')->after('loan_payment_done'); //0 means complete loan amount is unpaid
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('loan_payment_done');
            $table->dropColumn('loan_payment_status');
        });
    }
}
