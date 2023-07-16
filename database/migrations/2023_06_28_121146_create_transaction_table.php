<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction', function (Blueprint $table) {
            // $table->integer('POS_AGENT_MAPPING_HEADER')->nullable(false);
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
        
        });
    }


    // $table->string('CCCODE')->nullable(false);
    // $table->string('MERCHANT_NAME')->nullable(false);
    // $table->date('TRN_DATE')->nullable(false);
    // $table->integer('NO_TRN')->nullable(false);
    // $table->date('CDATE')->nullable(false);
    // $table->string('TRN_TIME')->nullable(false);
    // $table->string('TER_NO')->nullable(false);
    // $table->string('TRANSACTION_NO')->nullable(false);
    // $table->decimal('GROSS_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('VAT_AMNT', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('VATABLE_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('NONVAT_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('VATEXEMPT_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('VATEXEMPT', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('VATEXEMPT_AMNT', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('LOCAL_TAX', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('PWD_DISC', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('SNRCIT_DISC', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('EMPLO_DISC', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('AYALA_DISC', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('STORE_DISC', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('OTHER_DISC', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('REFUND_AMT', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('SCHRGE_AMT', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('OTHER_SCHR', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('CASH_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('CARD_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('EPAY_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('DCARD_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('OTHERSL_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('CHECK_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('GC_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('MASTERCARD_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('VISA_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('AMEX_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('DINERS_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('JCB_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('GCASH_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('PAYMAYA_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('ALIPAY_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('WECHAT_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('GRAB_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('FOODPANDA_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('MASTERDEBIT_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('VISADEBIT_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('OPEN_SALES', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('OPEN_SALES_2', 12, 2)->nullable();
    // $table->decimal('OPEN_SALES_3', 12, 2)->nullable();
    // $table->decimal('OPEN_SALES_4', 12, 2)->nullable();
    // $table->decimal('OPEN_SALES_5', 12, 2)->nullable();
    // $table->decimal('OPEN_SALES_6', 12, 2)->nullable();
    // $table->decimal('OPEN_SALES_7', 12, 2)->nullable();
    // $table->decimal('OPEN_SALES_8', 12, 2)->nullable();
    // $table->decimal('OPEN_SALES_9', 12, 2)->nullable();
    // $table->decimal('OPEN_SALES_10', 12, 2)->nullable();
    // $table->decimal('OPEN_SALES_11', 12, 2)->nullable();
    // $table->decimal('PAYPAL_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('ONLINE_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('GC_EXCESS', 12, 2)->nullable(false)->default('0.00');
    // $table->bigInteger('MOBILE_NO', 12, 2)->nullable();
    // $table->tinyInteger('NO_CUST')->nullable(false);
    // $table->string('TRN_TYPE')->nullable(false);
    // $table->string('SLS_FLAG')->nullable(false);
    // $table->decimal('VAT_PCT', 6, 2)->nullable(false);

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction');
    }
}
