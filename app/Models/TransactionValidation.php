<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class TransactionValidation extends Model
{
    use HasFactory;

    public function deleteTransaction($param){
        $merchant_code = $param['merchant_code'];
        $TRN_DATE = $param['trn_date'];
        $TER_NO = $param['TER_NO'];
        $TRN_TIME = substr($param['TRN_TIME'],0,2);
        TransactionValidation::where(['CCCODE'=>$merchant_code,'TRN_DATE'=>$TRN_DATE,'TER_NO'=>$TER_NO])->where('TRN_TIME', 'LIKE', $TRN_TIME.'%')->delete();
        // Logs::where(['merchant_code'=>$merchant_code,'transaction_date'=>$TRN_DATE,'terminal_no'=>$TER_NO])->delete();
    }

    public function saveTransaction($param){
        $merchant_code = $param['merchant_code'];
        $TRN_DATE = $param['trn_date'];
        $TRANS_NO = $param['TRANS_NO']; 
        $TER_NO = $param['TER_NO'];
        $merchant_name = $param['merchant_name']; 
        $NO_TRN = $param['NO_TRN']; 
        $filename = $param['filename']; 
        $values = $param['values'];
        
       TransactionValidation::where(['CCCODE'=>$merchant_code,'TRN_DATE'=>$TRN_DATE,'TER_NO'=>$TER_NO,'TRANSACTION_NO'=>$TRANS_NO])->delete();
        $Transaction =  new TransactionValidation;
        $Transaction->CCCODE = $merchant_code;
        $Transaction->MERCHANT_NAME = $merchant_name;
        $Transaction->TRN_DATE = $TRN_DATE;
        $Transaction->NO_TRN = $NO_TRN;
        $Transaction->FILENAME = $filename;
        foreach($values as $key => $val){
            $Transaction->$key = trim($val);
        }
        $Transaction->save();   
    }
    public function validateTransaction(){
        return TransactionValidation::select('CCCODE',
        'TRN_DATE',
        'TER_NO',
        DB::raw('CAST(TRANSACTION_NO as varchar) as TRANSNO'),
        DB::raw('CAST(FILENAME as varchar) as fileN'),

        DB::raw('printf("%.2f", SUM(CAST(VAT_AMNT + VATABLE_SLS + NONVAT_SLS + VATEXEMPT_SLS + VATEXEMPT_AMNT + LOCAL_TAX + PWD_DISC + SNRCIT_DISC + EMPLO_DISC + AYALA_DISC + STORE_DISC + OTHER_DISC + SCHRGE_AMT + OTHER_SCHR AS DECIMAL(10,2)))) as gross_sum'),
        DB::raw('printf("%.2f", SUM(CAST(abs((CASH_SLS + OTHERSL_SLS + CHECK_SLS + GC_SLS + MASTERCARD_SLS + VISA_SLS + AMEX_SLS + DINERS_SLS + JCB_SLS + GCASH_SLS + PAYMAYA_SLS + ALIPAY_SLS + WECHAT_SLS + GRAB_SLS + FOODPANDA_SLS + MASTERDEBIT_SLS + VISADEBIT_SLS + PAYPAL_SLS + ONLINE_SLS + OPEN_SALES + OPEN_SALES_2 + OPEN_SALES_3 + OPEN_SALES_4 + OPEN_SALES_5 + OPEN_SALES_6 + OPEN_SALES_7 + OPEN_SALES_8 + OPEN_SALES_9 + OPEN_SALES_10 + OPEN_SALES_11)- GC_EXCESS) AS DECIMAL(10,2)))) as payment_sum'),
        DB::raw('printf("%.2f", SUM(CAST(MASTERCARD_SLS + VISA_SLS + AMEX_SLS + DINERS_SLS + JCB_SLS AS DECIMAL(10,2)))) as card_sum'),
        DB::raw('printf("%.2f", SUM(CAST(GCASH_SLS + PAYMAYA_SLS + ALIPAY_SLS + WECHAT_SLS AS DECIMAL(10,2)))) as epay_sum'),
        DB::raw('printf("%.2f", SUM(CAST(MASTERDEBIT_SLS + VISADEBIT_SLS AS DECIMAL(10,2)))) as dcard_sum'),

        DB::raw('printf("%.2f", SUM(CAST(GROSS_SLS AS DECIMAL(10,2)))) as gross_sls'),
        DB::raw('printf("%.2f", SUM(CAST(abs(VAT_AMNT + VATABLE_SLS + NONVAT_SLS + VATEXEMPT_SLS + SCHRGE_AMT + OTHER_SCHR) AS DECIMAL(10,2)))) as payment_sls'),
        DB::raw('printf("%.2f", SUM(CAST(CARD_SLS AS DECIMAL(10,2)))) as card_sls'),
        DB::raw('printf("%.2f", SUM(CAST(EPAY_SLS AS DECIMAL(10,2)))) as epay_sls'),
        DB::raw('printf("%.2f", SUM(CAST(DCARD_SLS AS DECIMAL(10,2)))) as dcard_sls'),

        DB::raw('printf("%.2f", SUM(CAST(((VAT_AMNT + VATABLE_SLS + NONVAT_SLS + VATEXEMPT_SLS + VATEXEMPT_AMNT + LOCAL_TAX + PWD_DISC + SNRCIT_DISC + EMPLO_DISC + AYALA_DISC + STORE_DISC + OTHER_DISC + SCHRGE_AMT + OTHER_SCHR) - GROSS_SLS) AS DECIMAL(10,2)))) as gross'),
        DB::raw('printf("%.2f", SUM(CAST(abs((CASH_SLS + OTHERSL_SLS + CHECK_SLS + GC_SLS + MASTERCARD_SLS + VISA_SLS + AMEX_SLS + DINERS_SLS + JCB_SLS + GCASH_SLS + PAYMAYA_SLS + ALIPAY_SLS + WECHAT_SLS + GRAB_SLS + FOODPANDA_SLS + MASTERDEBIT_SLS + VISADEBIT_SLS + PAYPAL_SLS + ONLINE_SLS + OPEN_SALES + OPEN_SALES_2 + OPEN_SALES_3 + OPEN_SALES_4 + OPEN_SALES_5 + OPEN_SALES_6 + OPEN_SALES_7 + OPEN_SALES_8 + OPEN_SALES_9 + OPEN_SALES_10 + OPEN_SALES_11)- GC_EXCESS) - abs(VAT_AMNT + VATABLE_SLS + NONVAT_SLS + VATEXEMPT_SLS + SCHRGE_AMT + OTHER_SCHR) AS DECIMAL(10,2)))) as payment'),
        DB::raw('printf("%.2f", SUM(CAST(((MASTERCARD_SLS + VISA_SLS + AMEX_SLS + DINERS_SLS + JCB_SLS) - CARD_SLS) AS DECIMAL(10,2)))) as card'),
        DB::raw('printf("%.2f", SUM(CAST(((GCASH_SLS + PAYMAYA_SLS + ALIPAY_SLS + WECHAT_SLS) - EPAY_SLS) AS DECIMAL(10,2)))) as epay'),
        DB::raw('printf("%.2f", SUM(CAST(((MASTERDEBIT_SLS + VISADEBIT_SLS) - DCARD_SLS) AS DECIMAL(10,2)))) as dcard'),
        )
        ->groupBy('CCCODE', 'TRN_DATE', 'TER_NO', 'TRANSACTION_NO','FILENAME')
        ->get();
    }
    public function searchQuery($search_value, $arr_column, $param){
        $ter_no = $param['terminal_no'];
        $transaction_no = $param['transaction_no'];
        $trn_date = $param['transaction_date'];
        $cccode = $param['merchant_code'];
        $search_value = abs($search_value);
        return DB::select("SELECT " . implode(',', $arr_column) . "
        FROM transaction_validations
        WHERE CCCODE ='$cccode'
        AND TRN_DATE = '$trn_date'
        AND TER_NO = '$ter_no'
        AND TRANSACTION_NO = '$transaction_no'
        ;");
    }
}
