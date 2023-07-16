<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Logs;
use DB;

class DailyValidation extends Model
{
    use HasFactory;
    protected $table = 'daily';
    public $timestamps = false;

    public function saveDaily($param){
     
        $merchant_code = $param['merchant_code'];
        $TER_NO = $param['TER_NO'];
        $merchant_name = $param['merchant_name']; 
        $TRN_DATE = $param['TRN_DATE'];
        $daily = $param['daily'];
        $filename = $param['filename']; 
        DailyValidation::where(['CCCODE'=>$merchant_code,'TRN_DATE'=>$TRN_DATE, 'TER_NO'=>$TER_NO])->delete();
        $DailyMapping =  new DailyValidation;             
        $DailyMapping->CCCODE = $merchant_code;
        $DailyMapping->MERCHANT_NAME = $merchant_name;
        $DailyMapping->FILENAME = $filename;
        foreach($daily as $key => $val){
            $DailyMapping->$key = $val;
        }
        $DailyMapping->save();
    }

    public function validateDaily(){
        return DailyValidation::select('CCCODE',
        'TRN_DATE',
        'TER_NO',
        DB::raw('CAST(FILENAME as varchar) as fileN'),
        DB::raw('printf("%.2f", SUM(CAST(((VAT_AMNT + VATABLE_SLS + NONVAT_SLS + VATEXEMPT_SLS + VATEXEMPT_AMNT + LOCAL_TAX + VOID_AMNT + DISCOUNTS + REFUND_AMT + SCHRGE_AMT) - GROSS_SLS) AS DECIMAL(10,2)))) as gross'),
        DB::raw('printf("%.2f", SUM(CAST(((SNRCIT_DISC + PWD_DISC + EMPLO_DISC + AYALA_DISC + STORE_DISC + OTHER_DISC) - DISCOUNTS) AS DECIMAL(10,2)))) as discount'),
        DB::raw('printf("%.2f", SUM(CAST(((NO_SNRCIT + NO_PWD + NO_EMPLO + NO_AYALA + NO_STORE + NO_OTHER_DISC) - NO_DISC) AS DECIMAL(10,2)))) as no_disc'),
        DB::raw('printf("%.2f", SUM(CAST(((MASTERCARD_SLS + VISA_SLS + AMEX_SLS + DINERS_SLS + JCB_SLS) - CARD_SLS) AS DECIMAL(10,2)))) as card'),
        DB::raw('printf("%.2f", SUM(CAST(((GCASH_SLS + PAYMAYA_SLS + ALIPAY_SLS + WECHAT_SLS) - EPAY_SLS) AS DECIMAL(10,2)))) as epay'),
        DB::raw('printf("%.2f", SUM(CAST(((MASTERDEBIT_SLS + VISADEBIT_SLS) - DCARD_SLS) AS DECIMAL(10,2)))) as dcard'),
        DB::raw('printf("%.2f", SUM(CAST(((VAT_AMNT + VATABLE_SLS + NONVAT_SLS + VATEXEMPT_SLS + OLD_GRNTOT + LOCAL_TAX) - NEW_GRNTOT) AS DECIMAL(10,2)))) as new_grand_total'),
        DB::raw('printf("%.2f", SUM(CAST(((VATABLE_SLS + NONVAT_SLS + VATEXEMPT_SLS) - (NEW_GRNTOT - OLD_GRNTOT - VAT_AMNT)) AS DECIMAL(10,2)))) as netsales'),
        )
        ->groupBy('CCCODE', 'TRN_DATE', 'TER_NO', 'FILENAME')
        ->get();
    }

}
