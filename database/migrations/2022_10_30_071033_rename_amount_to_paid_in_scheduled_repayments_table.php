<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameAmountToPaidInScheduledRepaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('scheduled_repayments', function (Blueprint $table) {
            $table->string('amount_paid')->default('0')->after('amount_to_paid');
            $table->renameColumn('amount_to_paid', 'amount_to_be_paid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('scheduled_repayments', function (Blueprint $table) {
            $table->dropColumn('amount_paid');
            $table->renameColumn('amount_to_be_paid', 'amount_to_paid');
        });
    }
}
