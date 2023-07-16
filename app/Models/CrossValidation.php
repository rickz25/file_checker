<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TransactionValidation;
use App\Models\DailyValidation;
use DB;
use App\Models\ThresholdSettings;
use App\Models\Logs;

class CrossValidation extends Model
{
    use HasFactory;

    public function validateSales($params)
    {
        $Threshold = ThresholdSettings::where('id', 1)->first();
        $Logs = new Logs;
        $CCCODE = $params["merchant_code"];
        $TRN_DATE = $params["transaction_date"];
        $TER_NO = $params["terminal_no"];

        $daily_query = DailyValidation::select('CCCODE',
        'TRN_DATE',
        'TER_NO',
         DB::raw('CAST(FILENAME as varchar) as fileN'),
         DB::raw('printf("%.2f", SUM(CAST(GROSS_SLS AS DECIMAL(10,2)))) as gross'),
         DB::raw('printf("%.2f", SUM(CAST(CARD_SLS AS DECIMAL(10,2)))) as card'),
         DB::raw('printf("%.2f", SUM(CAST(EPAY_SLS AS DECIMAL(10,2)))) as epay'),
         DB::raw('printf("%.2f", SUM(CAST(DCARD_SLS AS DECIMAL(10,2)))) as dcard'),
         DB::raw('printf("%.2f", SUM(CAST(GC_SLS AS DECIMAL(10,2)))) as gc_sls'),
         DB::raw('printf("%.2f", SUM(CAST(OTHER_SLS AS DECIMAL(10,2)))) as other_sls'),
         DB::raw('printf("%.2f", SUM(CAST(CHECK_SLS AS DECIMAL(10,2)))) as check_sls'),
         DB::raw('printf("%.2f", SUM(CAST(GRAB_SLS AS DECIMAL(10,2))))  as grab_sls'),
         DB::raw('printf("%.2f", SUM(CAST(FOODPANDA_SLS AS DECIMAL(10,2)))) as foodpanda_sls'),
         DB::raw('SUM(CAST(NO_TRN AS int)) as notrn'),
         DB::raw('printf("%.2f", SUM(CAST(NEW_GRNTOT - OLD_GRNTOT AS DECIMAL(10,2)))) as sales_total'),
         DB::raw('printf("%.2f", SUM(CAST(abs((CASH_SLS + OTHER_SLS + CHECK_SLS + GC_SLS + MASTERCARD_SLS + VISA_SLS + AMEX_SLS + DINERS_SLS + JCB_SLS + GCASH_SLS + PAYMAYA_SLS + ALIPAY_SLS + WECHAT_SLS + GRAB_SLS + FOODPANDA_SLS + MASTERDEBIT_SLS + VISADEBIT_SLS + PAYPAL_SLS + ONLINE_SLS + OPEN_SALES + OPEN_SALES_2 + OPEN_SALES_3 + OPEN_SALES_4 + OPEN_SALES_5 + OPEN_SALES_6 + OPEN_SALES_7 + OPEN_SALES_8 + OPEN_SALES_9 + OPEN_SALES_10 + OPEN_SALES_11) - GC_EXCESS) AS DECIMAL(10,2)))) as payment')
        )
        ->where(['CCCODE'=>$CCCODE, 'TRN_DATE'=>$TRN_DATE, 'TER_NO' => $TER_NO])
        ->groupBy('CCCODE', 'TRN_DATE', 'TER_NO', 'FILENAME')
        ->get();
    
        $trans_query = TransactionValidation::select('CCCODE',
        'TRN_DATE',
        'TER_NO',
        DB::raw('CAST(FILENAME as varchar) as fileN'),
        DB::raw('printf("%.2f", SUM(CAST(GROSS_SLS AS DECIMAL(10,2)))) as gross'),
        DB::raw('printf("%.2f", SUM(CAST(CARD_SLS AS DECIMAL(10,2)))) as card'),
        DB::raw('printf("%.2f", SUM(CAST(EPAY_SLS AS DECIMAL(10,2)))) as epay'),
        DB::raw('printf("%.2f", SUM(CAST(DCARD_SLS AS DECIMAL(10,2)))) as dcard'),
        DB::raw('printf("%.2f", SUM(CAST(GC_SLS AS DECIMAL(10,2)))) as gc_sls'),
        DB::raw('printf("%.2f", SUM(CAST(OTHERSL_SLS AS DECIMAL(10,2)))) as other_sls'),
        DB::raw('printf("%.2f", SUM(CAST(CHECK_SLS AS DECIMAL(10,2)))) as check_sls'),
        DB::raw('printf("%.2f", SUM(CAST(GRAB_SLS AS DECIMAL(10,2)))) as grab_sls'),
        DB::raw('printf("%.2f", SUM(CAST(FOODPANDA_SLS AS DECIMAL(10,2)))) as foodpanda_sls'),
        DB::raw('count(*) as notrn'),
        DB::raw('printf("%.2f", SUM(CAST(((CASE WHEN SLS_FLAG="S" THEN VAT_AMNT + VATABLE_SLS + NONVAT_SLS + VATEXEMPT_SLS ELSE 0 END) - (CASE WHEN SLS_FLAG="R" THEN VAT_AMNT + VATABLE_SLS + NONVAT_SLS + VATEXEMPT_SLS ELSE 0 END)) AS DECIMAL(10,2)))) as sales_total'),
        DB::raw('printf("%.2f", SUM(CAST(abs((CASH_SLS + OTHERSL_SLS + CHECK_SLS + GC_SLS + MASTERCARD_SLS + VISA_SLS + AMEX_SLS + DINERS_SLS + JCB_SLS + GCASH_SLS + PAYMAYA_SLS + ALIPAY_SLS + WECHAT_SLS + GRAB_SLS + FOODPANDA_SLS + MASTERDEBIT_SLS + VISADEBIT_SLS + PAYPAL_SLS + ONLINE_SLS + OPEN_SALES + OPEN_SALES_2 + OPEN_SALES_3 + OPEN_SALES_4 + OPEN_SALES_5 + OPEN_SALES_6 + OPEN_SALES_7 + OPEN_SALES_8 + OPEN_SALES_9 + OPEN_SALES_10 + OPEN_SALES_11)-GC_EXCESS) AS DECIMAL(10,2)))) as payment')
        )
        ->where(['CCCODE'=>$CCCODE, 'TRN_DATE'=>$TRN_DATE, 'TER_NO' => $TER_NO])
        ->groupBy('CCCODE', 'TRN_DATE', 'TER_NO','FILENAME')
        ->get();

        $gross = $daily_query[0]['gross'] - $trans_query[0]['gross'];
        $card = $daily_query[0]['card'] - $trans_query[0]['card'];
        $epay = $daily_query[0]['epay'] - $trans_query[0]['epay'];
        $dcard = $daily_query[0]['dcard'] - $trans_query[0]['dcard'];
        $gc_sls = $daily_query[0]['gc_sls'] - $trans_query[0]['gc_sls'];
        $other_sls = $daily_query[0]['other_sls'] - $trans_query[0]['other_sls'];
        $check_sls = $daily_query[0]['check_sls'] - $trans_query[0]['check_sls'];
        $grab_sls = $daily_query[0]['grab_sls'] - $trans_query[0]['grab_sls'];
        $foodpanda_sls = $daily_query[0]['foodpanda_sls'] - $trans_query[0]['foodpanda_sls'];
        $notrn = $daily_query[0]['notrn'] - $trans_query[0]['notrn'];
        $sales_total = $daily_query[0]['sales_total'] - $trans_query[0]['sales_total'];
        $payment = $daily_query[0]['payment'] - $trans_query[0]['payment'];

       $error = 0;
    
        $param['error_type'] = 'Cross';
        $param['filename'] = $params['filename'];
        $param['merchant_code'] = $CCCODE;
        $param['transaction_date'] = $TRN_DATE;
        $param['transaction_no'] = null;
        $param['terminal_no'] = $TER_NO;

        $val1 = number_format((float) $Threshold->value_from, 2, '.', '');
        $val2 = number_format((float) $Threshold->value_to, 2, '.', '');

        if (!$this->in_range($gross, $val1, $val2)) {
            $error=1;
            $param['type'] = 0;
            $message = "GROSS discrepancy (" . $this->formatted(abs($gross)) . ")";
            $param['error_description'] = $message;
            $Logs->savelogs($param); /**save logs */
        }
        if (!$this->in_range($card, $val1, $val2)) {
            $error=1;
            $param['type'] = 0;
            $message = "CARD discrepancy (" . $this->formatted(abs($card)) . ")";
            $param['error_description'] = $message;
            $Logs->savelogs($param); /**save logs */
        }
        if (!$this->in_range($epay, $val1, $val2)) {
            $error=1;
            $param['type'] = 0;
            $message = "EPAY discrepancy (" . $this->formatted(abs($epay)) . ")";
            $param['error_description'] = $message;
            $Logs->savelogs($param); /**save logs */
        }
        if (!$this->in_range($dcard, $val1, $val2)) {
            $error=1;
            $param['type'] = 0;
            $message = "DCARD discrepancy (" . $this->formatted(abs($dcard)) . ")";
            $param['error_description'] = $message;
            $Logs->savelogs($param); /**save logs */
        }
        if (!$this->in_range($payment, $val1, $val2)) {
            $error=1;
            $param['type'] = 0;
            $message = "PAYMENT discrepancy (" . $this->formatted(abs($payment)) . ")";
            $param['error_description'] = $message;
            $Logs->savelogs($param); /**save logs */
        }
        if (!$this->in_range($gc_sls, $val1, $val2)) {
            $error=1;
            $param['type'] = 0;
            $message = "GC_SLS discrepancy (" . $this->formatted(abs($gc_sls)) . ")";
            $param['error_description'] = $message;
            $Logs->savelogs($param); /**save logs */
        }
        if (!$this->in_range($other_sls, $val1, $val2)) {
            $error=1;
            $param['type'] = 0;
            $message = "OTHER_SLS discrepancy (" . $this->formatted(abs($other_sls)) . ")";
            $param['error_description'] = $message;
            $Logs->savelogs($param); /**save logs */
        }
        if (!$this->in_range($check_sls, $val1, $val2)) {
            $error=1;
            $param['type'] = 0;
            $message = "CHECK_SLS discrepancy (" . $this->formatted(abs($check_sls)) . ")";
            $param['error_description'] = $message;
            $Logs->savelogs($param); /**save logs */
        }
        if (!$this->in_range($grab_sls, $val1, $val2)) {
            $error=1;
            $param['type'] = 0;
            $message = "GRAB_SLS discrepancy (" . $this->formatted(abs($grab_sls)) . ")";
            $param['error_description'] = $message;
            $Logs->savelogs($param); /**save logs */
        }
        if (!$this->in_range($foodpanda_sls, $val1, $val2)) {
            $error=1;
            $param['type'] = 0;
            $message = "FOODPANDA_SLS discrepancy (" . $this->formatted(abs($foodpanda_sls)) . ")";
            $param['error_description'] = $message;
            $Logs->savelogs($param); /**save logs */
        }
        if (!$this->in_range($sales_total, $val1, $val2)) {
            $error=1;
            $param['type'] = 0;
            $message = "SALES TOTAL discrepancy (" . $this->formatted(abs($sales_total)) . ")";
            $param['error_description'] = $message;
            $Logs->savelogs($param); /**save logs */
        }
        if ($notrn !='0') {
            $error=1;
            $param['type'] = 0;
            $message = "NO_TRN (".$daily_query[0]['notrn'].") not equal to total of transaction (".$trans_query[0]['notrn']."), discrepancy (" . $this->formatted(abs($notrn)) . ")";
            $param['error_description'] = $message;
            $Logs->savelogs($param); /**save logs */
        }

       return $error;

    }
    public function in_range($x, $min, $max)
    {
        $x = abs(number_format((float) $x, 2, '.', ''));
        return ($min <= $x) && ($x <= $max);
    }
    public function formatted($num){
        return number_format($num, 2, '.', ',');
    }
}