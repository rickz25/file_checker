<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionValidationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_validations', function (Blueprint $table) {
       
            $table->string('CCCODE')->nullable(false);
            $table->string('MERCHANT_NAME')->nullable(false);
            $table->date('TRN_DATE')->nullable(false);
            $table->integer('NO_TRN')->nullable(false);
            $table->date('CDATE')->nullable();
            $table->string('TRN_TIME')->nullable(false);
            $table->string('TER_NO')->nullable(false);
            $table->string('TRANSACTION_NO')->nullable(false);
            $table->string('GROSS_SLS')->nullable()->default('0.00');
            $table->string('VAT_AMNT')->nullable()->default('0.00');
            $table->string('VATABLE_SLS')->nullable()->default('0.00');
            $table->string('NONVAT_SLS')->nullable()->default('0.00');
            $table->string('VATEXEMPT_SLS')->nullable()->default('0.00');
            $table->string('VATEXEMPT')->nullable()->default('0.00');
            $table->string('VATEXEMPT_AMNT')->nullable()->default('0.00');
            $table->string('LOCAL_TAX')->nullable()->default('0.00');
            $table->string('PWD_DISC')->nullable()->default('0.00');
            $table->string('SNRCIT_DISC')->nullable()->default('0.00');
            $table->string('EMPLO_DISC')->nullable()->default('0.00');
            $table->string('AYALA_DISC')->nullable()->default('0.00');
            $table->string('STORE_DISC')->nullable()->default('0.00');
            $table->string('OTHER_DISC')->nullable()->default('0.00');
            $table->string('REFUND_AMT')->nullable()->default('0.00');
            $table->string('SCHRGE_AMT')->nullable()->default('0.00');
            $table->string('OTHER_SCHR')->nullable()->default('0.00');
            $table->string('CASH_SLS')->nullable()->default('0.00');
            $table->string('CARD_SLS')->nullable()->default('0.00');
            $table->string('EPAY_SLS')->nullable()->default('0.00');
            $table->string('DCARD_SLS')->nullable()->default('0.00');
            $table->string('OTHERSL_SLS')->nullable()->default('0.00');
            $table->string('CHECK_SLS')->nullable()->default('0.00');
            $table->string('GC_SLS')->nullable()->default('0.00');
            $table->string('MASTERCARD_SLS')->nullable()->default('0.00');
            $table->string('VISA_SLS')->nullable()->default('0.00');
            $table->string('AMEX_SLS')->nullable()->default('0.00');
            $table->string('DINERS_SLS')->nullable()->default('0.00');
            $table->string('JCB_SLS')->nullable()->default('0.00');
            $table->string('GCASH_SLS')->nullable()->default('0.00');
            $table->string('PAYMAYA_SLS')->nullable()->default('0.00');
            $table->string('ALIPAY_SLS')->nullable()->default('0.00');
            $table->string('WECHAT_SLS')->nullable()->default('0.00');
            $table->string('GRAB_SLS')->nullable()->default('0.00');
            $table->string('FOODPANDA_SLS')->nullable()->default('0.00');
            $table->string('MASTERDEBIT_SLS')->nullable()->default('0.00');
            $table->string('VISADEBIT_SLS')->nullable()->default('0.00');
            $table->string('OPEN_SALES')->nullable()->default('0.00');
            $table->string('OPEN_SALES_2')->nullable();
            $table->string('OPEN_SALES_3')->nullable();
            $table->string('OPEN_SALES_4')->nullable();
            $table->string('OPEN_SALES_5')->nullable();
            $table->string('OPEN_SALES_6')->nullable();
            $table->string('OPEN_SALES_7')->nullable();
            $table->string('OPEN_SALES_8')->nullable();
            $table->string('OPEN_SALES_9')->nullable();
            $table->string('OPEN_SALES_10')->nullable();
            $table->string('OPEN_SALES_11')->nullable();
            $table->string('PAYPAL_SLS')->nullable()->default('0.00');
            $table->string('ONLINE_SLS')->nullable()->default('0.00');
            $table->string('GC_EXCESS')->nullable()->default('0.00');
            $table->bigInteger('MOBILE_NO')->nullable();
            $table->tinyInteger('NO_CUST')->nullable();
            $table->string('TRN_TYPE')->nullable();
            $table->string('SLS_FLAG')->nullable();
            $table->string('VAT_PCT')->nullable();
            $table->string('FILENAME')->nullable();
            $table->timestamps();
        
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction_validations');
    }
}
