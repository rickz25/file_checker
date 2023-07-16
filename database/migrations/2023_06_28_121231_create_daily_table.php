<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily', function (Blueprint $table) {
            // $table->integer('POS_AGENT_MAPPING_HEADER')->nullable(false);
            $table->string('CCCODE')->nullable(false);
            $table->string('MERCHANT_NAME')->nullable(false);
            $table->string('TER_NO')->nullable(false);
            $table->date('TRN_DATE')->nullable(false);
            $table->string('STRANS')->nullable(false);
            $table->string('ETRANS')->nullable(false);
            $table->string('GROSS_SLS')->nullable(false)->default('0.00');
            $table->string('VAT_AMNT')->nullable(false)->default('0.00');
            $table->string('VATABLE_SLS')->nullable(false)->default('0.00');
            $table->string('NONVAT_SLS')->nullable(false)->default('0.00');
            $table->string('VATEXEMPT_SLS')->nullable(false)->default('0.00');
            $table->string('VATEXEMPT_AMNT')->nullable(false)->default('0.00');
            $table->string('OLD_GRNTOT')->nullable(false)->default('0.00');
            $table->string('NEW_GRNTOT')->nullable(false)->default('0.00');
            $table->string('LOCAL_TAX')->nullable(false)->default('0.00');
            $table->string('VOID_AMNT')->nullable(false)->default('0.00');
            $table->integer('NO_VOID')->nullable(false)->default('0');
            $table->string('DISCOUNTS')->nullable(false)->default('0.00');
            $table->integer('NO_DISC')->nullable(false)->default('0');
            $table->string('REFUND_AMT')->nullable(false)->default('0.00');
            $table->integer('NO_REFUND')->nullable(false)->default('0');
            $table->string('SNRCIT_DISC')->nullable(false)->default('0.00');
            $table->integer('NO_SNRCIT')->nullable(false)->default('0');
            $table->string('PWD_DISC')->nullable(false)->default('0.00');
            $table->integer('NO_PWD')->nullable(false)->default('0');
            $table->string('EMPLO_DISC')->nullable(false)->default('0.00');
            $table->integer('NO_EMPLO')->nullable(false)->default('0');
            $table->string('AYALA_DISC')->nullable(false)->default('0.00');
            $table->integer('NO_AYALA')->nullable(false)->default('0');
            $table->string('STORE_DISC')->nullable(false)->default('0.00');
            $table->integer('NO_STORE')->nullable(false)->default('0');
            $table->string('OTHER_DISC')->nullable(false)->default('0.00');
            $table->integer('NO_OTHER_DISC')->nullable(false)->default('0');
            $table->string('SCHRGE_AMT')->nullable(false)->default('0.00');
            $table->string('OTHER_SCHR')->nullable(false)->default('0.00');
            $table->string('CASH_SLS')->nullable(false)->default('0.00');
            $table->string('CARD_SLS')->nullable(false)->default('0.00');
            $table->string('EPAY_SLS')->nullable(false)->default('0.00');
            $table->string('DCARD_SLS')->nullable(false)->default('0.00');
            $table->string('OTHER_SLS')->nullable(false)->default('0.00');
            $table->string('CHECK_SLS')->nullable(false)->default('0.00');
            $table->string('GC_SLS')->nullable(false)->default('0.00');
            $table->string('MASTERCARD_SLS')->nullable(false)->default('0.00');
            $table->string('VISA_SLS')->nullable(false)->default('0.00');
            $table->string('AMEX_SLS')->nullable(false)->default('0.00');
            $table->string('DINERS_SLS')->nullable(false)->default('0.00');
            $table->string('JCB_SLS')->nullable(false)->default('0.00');
            $table->string('GCASH_SLS')->nullable(false)->default('0.00');
            $table->string('PAYMAYA_SLS')->nullable(false)->default('0.00');
            $table->string('ALIPAY_SLS')->nullable(false)->default('0.00');
            $table->string('WECHAT_SLS')->nullable(false)->default('0.00');
            $table->string('GRAB_SLS')->nullable(false)->default('0.00');
            $table->string('FOODPANDA_SLS')->nullable(false)->default('0.00');
            $table->string('OPEN_SALES')->nullable(false)->default('0.00');
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
            $table->string('MASTERDEBIT_SLS')->nullable()->default('0.00');
            $table->string('VISADEBIT_SLS')->nullable()->default('0.00');
            $table->string('PAYPAL_SLS')->nullable()->default('0.00');
            $table->string('ONLINE_SLS')->nullable()->default('0.00');
            $table->string('GC_EXCESS')->nullable()->default('0.00');
            $table->integer('NO_VATEXEMT')->nullable(false)->default('0');
            $table->integer('NO_SCHRGE')->nullable(false)->default('0');
            $table->integer('NO_OTHER_SUR')->nullable(false)->default('0');
            $table->integer('NO_CASH')->nullable(false)->default('0');
            $table->integer('NO_CARD')->nullable(false)->default('0');
            $table->integer('NO_EPAY')->nullable(false)->default('0');
            $table->integer('NO_DCARD_SLS')->nullable(false)->default('0');
            $table->integer('NO_OTHER_SLS')->nullable(false)->default('0');
            $table->integer('NO_CHECK')->nullable(false)->default('0');
            $table->integer('NO_GC')->nullable(false)->default('0');
            $table->integer('NO_MASTERCARD_SLS')->nullable(false)->default('0');
            $table->integer('NO_VISA_SLS')->nullable(false)->default('0');
            $table->integer('NO_AMEX_SLS')->nullable(false)->default('0');
            $table->integer('NO_DINERS_SLS')->nullable(false)->default('0');
            $table->integer('NO_JCB_SLS')->nullable(false)->default('0');
            $table->integer('NO_GCASH_SLS')->nullable(false)->default('0');
            $table->integer('NO_PAYMAYA_SLS')->nullable(false)->default('0');
            $table->integer('NO_ALIPAY_SLS')->nullable(false)->default('0');
            $table->integer('NO_WECHAT_SLS')->nullable(false)->default('0');
            $table->integer('NO_GRAB_SLS')->nullable(false)->default('0');
            $table->integer('NO_FOODPANDA_SLS')->nullable(false)->default('0');
            $table->integer('NO_OPEN_SALES')->nullable(false)->default('0');
            $table->integer('NO_OPEN_SALES_2')->nullable();
            $table->integer('NO_OPEN_SALES_3')->nullable();
            $table->integer('NO_OPEN_SALES_4')->nullable();
            $table->integer('NO_OPEN_SALES_5')->nullable();
            $table->integer('NO_OPEN_SALES_6')->nullable();
            $table->integer('NO_OPEN_SALES_7')->nullable();
            $table->integer('NO_OPEN_SALES_8')->nullable();
            $table->integer('NO_OPEN_SALES_9')->nullable();
            $table->integer('NO_OPEN_SALES_10')->nullable();
            $table->integer('NO_OPEN_SALES_11')->nullable();
            $table->integer('NO_MASTERDEBIT_SLS')->nullable();
            $table->integer('NO_VISADEBIT_SLS')->nullable();
            $table->integer('NO_PAYPAL_SLS')->nullable();
            $table->integer('NO_ONLINE_SLS')->nullable();
            $table->integer('NO_NOSALE')->nullable();
            $table->integer('NO_CUST')->nullable();
            $table->integer('NO_TRN')->nullable();
            $table->integer('PREV_EODCTR')->nullable();
            $table->integer('EODCTR')->nullable(false);
            $table->string('NETSALES')->nullable(false)->default('0.00');
            $table->string('FILENAME')->nullable();

        });
    }

    // $table->string('CCCODE')->nullable(false);
    // $table->string('MERCHANT_NAME')->nullable(false);
    // $table->string('TER_NO')->nullable(false);
    // $table->date('TRN_DATE')->nullable(false);
    // $table->string('STRANS')->nullable(false);
    // $table->string('ETRANS')->nullable(false);
    // $table->decimal('GROSS_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('VAT_AMNT', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('VATABLE_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('NONVAT_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('VATEXEMPT_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('VATEXEMPT_AMNT', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('OLD_GRNTOT', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('NEW_GRNTOT', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('LOCAL_TAX', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('VOID_AMNT', 12, 2)->nullable(false)->default('0.00');
    // $table->integer('NO_VOID')->nullable(false)->default('0');
    // $table->decimal('DISCOUNTS', 12, 2)->nullable(false)->default('0.00');
    // $table->integer('NO_DISC')->nullable(false)->default('0');
    // $table->decimal('REFUND_AMT', 12, 2)->nullable(false)->default('0.00');
    // $table->integer('NO_REFUND', 12, 2)->nullable(false)->default('0');
    // $table->decimal('SNRCIT_DISC', 12, 2)->nullable(false)->default('0.00');
    // $table->integer('NO_SNRCIT')->nullable(false)->default('0');
    // $table->decimal('PWD_DISC', 12, 2)->nullable(false)->default('0.00');
    // $table->integer('NO_PWD')->nullable(false)->default('0');
    // $table->decimal('EMPLO_DISC', 12, 2)->nullable(false)->default('0.00');
    // $table->integer('NO_EMPLO')->nullable(false)->default('0');
    // $table->decimal('AYALA_DISC', 12, 2)->nullable(false)->default('0.00');
    // $table->integer('NO_AYALA')->nullable(false)->default('0');
    // $table->decimal('STORE_DISC', 12, 2)->nullable(false)->default('0.00');
    // $table->integer('NO_STORE')->nullable(false)->default('0');
    // $table->decimal('OTHER_DISC', 12, 2)->nullable(false)->default('0.00');
    // $table->integer('NO_OTHER_DISC')->nullable(false)->default('0');
    // $table->decimal('SCHRGE_AMT', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('OTHER_SCHR', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('CASH_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('CARD_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('EPAY_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('DCARD_SLS', 12, 2)->nullable(false)->default('0.00');
    // $table->decimal('OTHER_SLS', 12, 2)->nullable(false)->default('0.00');
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
    // $table->decimal('MASTERDEBIT_SLS', 12, 2)->nullable()->default('0.00');
    // $table->decimal('VISADEBIT_SLS', 12, 2)->nullable()->default('0.00');
    // $table->decimal('PAYPAL_SLS', 12, 2)->nullable()->default('0.00');
    // $table->decimal('ONLINE_SLS', 12, 2)->nullable()->default('0.00');
    // $table->decimal('GC_EXCESS', 12, 2)->nullable()->default('0.00');
    // $table->integer('NO_VATEXEMT')->nullable(false)->default('0');
    // $table->integer('NO_SCHRGE')->nullable(false)->default('0');
    // $table->integer('NO_OTHER_SUR')->nullable(false)->default('0');
    // $table->integer('NO_CASH')->nullable(false)->default('0');
    // $table->integer('NO_CARD')->nullable(false)->default('0');
    // $table->integer('NO_EPAY')->nullable(false)->default('0');
    // $table->integer('NO_DCARD_SLS')->nullable(false)->default('0');
    // $table->integer('NO_OTHER_SLS')->nullable(false)->default('0');
    // $table->integer('NO_CHECK')->nullable(false)->default('0');
    // $table->integer('NO_GC')->nullable(false)->default('0');
    // $table->integer('NO_MASTERCARD_SLS')->nullable(false)->default('0');
    // $table->integer('NO_VISA_SLS')->nullable(false)->default('0');
    // $table->integer('NO_AMEX_SLS')->nullable(false)->default('0');
    // $table->integer('NO_DINERS_SLS')->nullable(false)->default('0');
    // $table->integer('NO_JCB_SLS')->nullable(false)->default('0');
    // $table->integer('NO_GCASH_SLS')->nullable(false)->default('0');
    // $table->integer('NO_PAYMAYA_SLS')->nullable(false)->default('0');
    // $table->integer('NO_ALIPAY_SLS')->nullable(false)->default('0');
    // $table->integer('NO_WECHAT_SLS')->nullable(false)->default('0');
    // $table->integer('NO_GRAB_SLS')->nullable(false)->default('0');
    // $table->integer('NO_FOODPANDA_SLS')->nullable(false)->default('0');
    // $table->integer('NO_OPEN_SALES')->nullable(false)->default('0');
    // $table->integer('NO_OPEN_SALES_2')->nullable();
    // $table->integer('NO_OPEN_SALES_3')->nullable();
    // $table->integer('NO_OPEN_SALES_4')->nullable();
    // $table->integer('NO_OPEN_SALES_5')->nullable();
    // $table->integer('NO_OPEN_SALES_6')->nullable();
    // $table->integer('NO_OPEN_SALES_7')->nullable();
    // $table->integer('NO_OPEN_SALES_8')->nullable();
    // $table->integer('NO_OPEN_SALES_9')->nullable();
    // $table->integer('NO_OPEN_SALES_10')->nullable();
    // $table->integer('NO_OPEN_SALES_11')->nullable();
    // $table->integer('NO_MASTERDEBIT_SLS')->nullable();
    // $table->integer('NO_VISADEBIT_SLS')->nullable();
    // $table->integer('NO_PAYPAL_SLS')->nullable();
    // $table->integer('NO_ONLINE_SLS')->nullable();
    // $table->integer('NO_NOSALE')->nullable();
    // $table->integer('NO_CUST')->nullable();
    // $table->integer('NO_TRN')->nullable();
    // $table->integer('PREV_EODCTR')->nullable();
    // $table->integer('EODCTR')->nullable(false);
    // $table->decimal('NETSALES', 12, 2)->nullable(false)->default('0.00');

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daily');
    }
}
