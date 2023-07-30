<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TransactionValidation;
use App\Models\DailyValidation;
use DB;

class CrossValidation extends Model
{
    use HasFactory;

    public function validateSales($params)
    {
        $Threshold = ThresholdSettings::where('id', 1)->first();
        $Transaction = new TransactionValidation;
        $Daily = new DailyValidation;
        $Logs = new Logs;
        $CCCODE = $params["merchant_code"];
        $TRN_DATE = $params["transaction_date"];
        $TER_NO = $params["terminal_no"];

        try {
            $cross_query = DailyValidation::select(
                'CCCODE',
                'TRN_DATE',
                'TER_NO',
                DB::raw("CAST(FILENAME as varchar) as fileN"),

                DB::raw("(SELECT printf('%.2f', SUM(CAST((CASE WHEN SLS_FLAG = 'S' THEN GROSS_SLS ELSE 0 END) AS DECIMAL(10,2)))) from transaction_validations WHERE CCCODE='$CCCODE' AND TRN_DATE='$TRN_DATE' AND TER_NO ='$TER_NO') as gross_trans"),
                DB::raw("(SELECT printf('%.2f', SUM(CAST(CARD_SLS AS DECIMAL(10,2)))) from transaction_validations WHERE CCCODE='$CCCODE' AND TRN_DATE='$TRN_DATE' AND TER_NO ='$TER_NO') as card_trans"),
                DB::raw("(SELECT printf('%.2f', SUM(CAST(EPAY_SLS AS DECIMAL(10,2)))) from transaction_validations WHERE CCCODE='$CCCODE' AND TRN_DATE='$TRN_DATE' AND TER_NO ='$TER_NO') as epay_trans"),
                DB::raw("(SELECT printf('%.2f', SUM(CAST(DCARD_SLS AS DECIMAL(10,2)))) from transaction_validations WHERE CCCODE='$CCCODE' AND TRN_DATE='$TRN_DATE' AND TER_NO ='$TER_NO') as dcard_trans"),
                DB::raw("(SELECT printf('%.2f', SUM(CAST(GC_SLS AS DECIMAL(10,2)))) from transaction_validations WHERE CCCODE='$CCCODE' AND TRN_DATE='$TRN_DATE' AND TER_NO ='$TER_NO') as gc_sls_trans"),
                DB::raw("(SELECT printf('%.2f', SUM(CAST(OTHERSL_SLS AS DECIMAL(10,2)))) from transaction_validations WHERE CCCODE='$CCCODE' AND TRN_DATE='$TRN_DATE' AND TER_NO ='$TER_NO') as other_sls_trans"),
                DB::raw("(SELECT printf('%.2f', SUM(CAST(CHECK_SLS AS DECIMAL(10,2)))) from transaction_validations WHERE CCCODE='$CCCODE' AND TRN_DATE='$TRN_DATE' AND TER_NO ='$TER_NO') as check_sls_trans"),
                DB::raw("(SELECT printf('%.2f', SUM(CAST(GRAB_SLS AS DECIMAL(10,2)))) from transaction_validations WHERE CCCODE='$CCCODE' AND TRN_DATE='$TRN_DATE' AND TER_NO ='$TER_NO') as grab_sls_trans"),
                DB::raw("(SELECT printf('%.2f', SUM(CAST(FOODPANDA_SLS AS DECIMAL(10,2)))) from transaction_validations WHERE CCCODE='$CCCODE' AND TRN_DATE='$TRN_DATE' AND TER_NO ='$TER_NO') as foodpanda_sls_trans"),
                DB::raw("(SELECT printf('%.2f', SUM(CAST(((CASE WHEN SLS_FLAG='S' THEN VAT_AMNT + VATABLE_SLS + NONVAT_SLS + VATEXEMPT_SLS ELSE 0 END) - (CASE WHEN SLS_FLAG='R' THEN VAT_AMNT + VATABLE_SLS + NONVAT_SLS + VATEXEMPT_SLS ELSE 0 END)) AS DECIMAL(10,2)))) from transaction_validations WHERE CCCODE='$CCCODE' AND TRN_DATE='$TRN_DATE' AND TER_NO ='$TER_NO') as sales_total_trans"),
                DB::raw("(SELECT printf('%.2f', SUM(CAST(((CASH_SLS + OTHERSL_SLS + CHECK_SLS + GC_SLS + MASTERCARD_SLS + VISA_SLS + AMEX_SLS + DINERS_SLS + JCB_SLS + GCASH_SLS + PAYMAYA_SLS + ALIPAY_SLS + WECHAT_SLS + GRAB_SLS + FOODPANDA_SLS + MASTERDEBIT_SLS + VISADEBIT_SLS + PAYPAL_SLS + ONLINE_SLS + OPEN_SALES + OPEN_SALES_2 + OPEN_SALES_3 + OPEN_SALES_4 + OPEN_SALES_5 + OPEN_SALES_6 + OPEN_SALES_7 + OPEN_SALES_8 + OPEN_SALES_9 + OPEN_SALES_10 + OPEN_SALES_11)-GC_EXCESS) AS DECIMAL(10,2)))) from transaction_validations WHERE CCCODE='$CCCODE' AND TRN_DATE='$TRN_DATE' AND TER_NO ='$TER_NO') as payment_trans"),
 
                DB::raw("printf('%.2f', SUM(CAST(GROSS_SLS AS DECIMAL(10,2)))) as gross_daily"),
                DB::raw("printf('%.2f', SUM(CAST(CARD_SLS AS DECIMAL(10,2)))) as card_daily"),
                DB::raw("printf('%.2f', SUM(CAST(EPAY_SLS AS DECIMAL(10,2)))) as epay_daily"),
                DB::raw("printf('%.2f', SUM(CAST(DCARD_SLS AS DECIMAL(10,2)))) as dcard_daily"),
                DB::raw("printf('%.2f', SUM(CAST(GC_SLS AS DECIMAL(10,2)))) as gc_sls_daily"),
                DB::raw("printf('%.2f', SUM(CAST(OTHER_SLS AS DECIMAL(10,2)))) as other_sls_daily"),
                DB::raw("printf('%.2f', SUM(CAST(CHECK_SLS AS DECIMAL(10,2)))) as check_sls_daily"),
                DB::raw("printf('%.2f', SUM(CAST(GRAB_SLS AS DECIMAL(10,2))))  as grab_sls_daily"),
                DB::raw("printf('%.2f', SUM(CAST(FOODPANDA_SLS AS DECIMAL(10,2)))) as foodpanda_sls_daily"),
                DB::raw("printf('%.2f', SUM(CAST(NEW_GRNTOT - OLD_GRNTOT AS DECIMAL(10,2)))) as sales_total_daly"),
                DB::raw("printf('%.2f', SUM(CAST(((CASH_SLS + OTHER_SLS + CHECK_SLS + GC_SLS + MASTERCARD_SLS + VISA_SLS + AMEX_SLS + DINERS_SLS + JCB_SLS + GCASH_SLS + PAYMAYA_SLS + ALIPAY_SLS + WECHAT_SLS + GRAB_SLS + FOODPANDA_SLS + MASTERDEBIT_SLS + VISADEBIT_SLS + PAYPAL_SLS + ONLINE_SLS + OPEN_SALES + OPEN_SALES_2 + OPEN_SALES_3 + OPEN_SALES_4 + OPEN_SALES_5 + OPEN_SALES_6 + OPEN_SALES_7 + OPEN_SALES_8 + OPEN_SALES_9 + OPEN_SALES_10 + OPEN_SALES_11) - GC_EXCESS) AS DECIMAL(10,2)))) as payment_daily"),

                DB::raw("printf('%.2f', SUM(CAST(GROSS_SLS AS DECIMAL(10,2))) - (SELECT SUM(CAST((CASE WHEN SLS_FLAG = 'S' THEN GROSS_SLS ELSE 0 END) AS DECIMAL(10,2))) from transaction_validations WHERE CCCODE='$CCCODE' AND TRN_DATE='$TRN_DATE' AND TER_NO ='$TER_NO')) as gross"),
                DB::raw("printf('%.2f', SUM(CAST(CARD_SLS AS DECIMAL(10,2))) - (SELECT SUM(CAST(CARD_SLS AS DECIMAL(10,2))) from transaction_validations WHERE CCCODE='$CCCODE' AND TRN_DATE='$TRN_DATE' AND TER_NO ='$TER_NO')) as card"),
                DB::raw("printf('%.2f', SUM(CAST(EPAY_SLS AS DECIMAL(10,2))) - (SELECT SUM(CAST(EPAY_SLS AS DECIMAL(10,2))) from transaction_validations WHERE CCCODE='$CCCODE' AND TRN_DATE='$TRN_DATE' AND TER_NO ='$TER_NO')) as epay"),
                DB::raw("printf('%.2f', SUM(CAST(DCARD_SLS AS DECIMAL(10,2))) - (SELECT SUM(CAST(DCARD_SLS AS DECIMAL(10,2))) from transaction_validations WHERE CCCODE='$CCCODE' AND TRN_DATE='$TRN_DATE' AND TER_NO ='$TER_NO')) as dcard"),
                DB::raw("printf('%.2f', SUM(CAST(GC_SLS AS DECIMAL(10,2))) - (SELECT SUM(CAST(GC_SLS AS DECIMAL(10,2))) from transaction_validations WHERE CCCODE='$CCCODE' AND TRN_DATE='$TRN_DATE' AND TER_NO ='$TER_NO')) as gc_sls"),
                DB::raw("printf('%.2f', SUM(CAST(OTHER_SLS AS DECIMAL(10,2))) - (SELECT SUM(CAST(OTHERSL_SLS AS DECIMAL(10,2))) from transaction_validations WHERE CCCODE='$CCCODE' AND TRN_DATE='$TRN_DATE' AND TER_NO ='$TER_NO')) as other_sls"),
                DB::raw("printf('%.2f', SUM(CAST(CHECK_SLS AS DECIMAL(10,2))) - (SELECT SUM(CAST(CHECK_SLS AS DECIMAL(10,2))) from transaction_validations WHERE CCCODE='$CCCODE' AND TRN_DATE='$TRN_DATE' AND TER_NO ='$TER_NO')) as check_sls"),
                DB::raw("printf('%.2f', SUM(CAST(GRAB_SLS AS DECIMAL(10,2))) - (SELECT SUM(CAST(GRAB_SLS AS DECIMAL(10,2))) from transaction_validations WHERE CCCODE='$CCCODE' AND TRN_DATE='$TRN_DATE' AND TER_NO ='$TER_NO'))  as grab_sls"),
                DB::raw("printf('%.2f', SUM(CAST(FOODPANDA_SLS AS DECIMAL(10,2))) - (SELECT SUM(CAST(FOODPANDA_SLS AS DECIMAL(10,2))) from transaction_validations WHERE CCCODE='$CCCODE' AND TRN_DATE='$TRN_DATE' AND TER_NO ='$TER_NO')) as foodpanda_sls"),
                DB::raw("SUM(CAST(NO_TRN AS int)) - (SELECT SUM(CAST(NO_TRN AS int)) from transaction_validations WHERE CCCODE='$CCCODE' AND TRN_DATE='$TRN_DATE' AND TER_NO ='$TER_NO') as notrn"),
                DB::raw("CAST(NO_TRN AS int) as daily_notrn"),
                DB::raw("(SELECT count(*) from transaction_validations WHERE CCCODE='$CCCODE' AND TRN_DATE='$TRN_DATE' AND TER_NO ='$TER_NO') as trans_notrn"),
                DB::raw("printf('%.2f', SUM(CAST(NEW_GRNTOT - OLD_GRNTOT AS DECIMAL(10,2))) - (SELECT SUM(CAST(((CASE WHEN SLS_FLAG='S' THEN VAT_AMNT + VATABLE_SLS + NONVAT_SLS + VATEXEMPT_SLS ELSE 0 END) - (CASE WHEN SLS_FLAG='R' THEN VAT_AMNT + VATABLE_SLS + NONVAT_SLS + VATEXEMPT_SLS ELSE 0 END)) AS DECIMAL(10,2))) from transaction_validations WHERE CCCODE='$CCCODE' AND TRN_DATE='$TRN_DATE' AND TER_NO ='$TER_NO')) as sales_total"),
                DB::raw("printf('%.2f', SUM(CAST(((CASH_SLS + OTHER_SLS + CHECK_SLS + GC_SLS + MASTERCARD_SLS + VISA_SLS + AMEX_SLS + DINERS_SLS + JCB_SLS + GCASH_SLS + PAYMAYA_SLS + ALIPAY_SLS + WECHAT_SLS + GRAB_SLS + FOODPANDA_SLS + MASTERDEBIT_SLS + VISADEBIT_SLS + PAYPAL_SLS + ONLINE_SLS + OPEN_SALES + OPEN_SALES_2 + OPEN_SALES_3 + OPEN_SALES_4 + OPEN_SALES_5 + OPEN_SALES_6 + OPEN_SALES_7 + OPEN_SALES_8 + OPEN_SALES_9 + OPEN_SALES_10 + OPEN_SALES_11) - GC_EXCESS) AS DECIMAL(10,2))) - (SELECT SUM(CAST(((CASH_SLS + OTHERSL_SLS + CHECK_SLS + GC_SLS + MASTERCARD_SLS + VISA_SLS + AMEX_SLS + DINERS_SLS + JCB_SLS + GCASH_SLS + PAYMAYA_SLS + ALIPAY_SLS + WECHAT_SLS + GRAB_SLS + FOODPANDA_SLS + MASTERDEBIT_SLS + VISADEBIT_SLS + PAYPAL_SLS + ONLINE_SLS + OPEN_SALES + OPEN_SALES_2 + OPEN_SALES_3 + OPEN_SALES_4 + OPEN_SALES_5 + OPEN_SALES_6 + OPEN_SALES_7 + OPEN_SALES_8 + OPEN_SALES_9 + OPEN_SALES_10 + OPEN_SALES_11)-GC_EXCESS) AS DECIMAL(10,2))) from transaction_validations WHERE CCCODE='$CCCODE' AND TRN_DATE='$TRN_DATE' AND TER_NO ='$TER_NO')) as payment")
            )
                ->where(['CCCODE' => $CCCODE, 'TRN_DATE' => $TRN_DATE, 'TER_NO' => $TER_NO])
                ->groupBy('CCCODE', 'TRN_DATE', 'TER_NO', 'FILENAME')
                ->get();

            $error = 0;
            foreach ($cross_query as $k => $q) {

                $param['error_type'] = 'Cross';
                $param['filename'] = $params['filename'];
                $param['merchant_code'] = $CCCODE;
                $param['transaction_date'] = $TRN_DATE;
                $param['transaction_no'] = null;
                $param['terminal_no'] = $TER_NO;

                $val1 = number_format((float) $Threshold->value_from, 2, '', '');
                $val2 = number_format((float) $Threshold->value_to, 2, '', '');

                if (!$this->in_range($q->gross, $val1, $val2)) {
                    $error++;
                    $param['type'] = 0;
                    $message = "Discrepancy (<b style='color:red;'>" . $this->formatted(abs($q->gross)) . "</b>) GROSS DAILY (<b>".$this->formatted($q->gross_daily)."</b>) and GROSS TRANSACTION (<b>".$this->formatted($q->gross_trans)."</b>)";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->card, $val1, $val2)) {
                    $error++;
                    $param['type'] = 0;
                    $message = "Discrepancy (<b style='color:red;'>" . $this->formatted(abs($q->card)) . "</b>) CARD DAILY (<b>".$this->formatted($q->card_daily)."</b>) and CARD TRANSACTION (<b>".$this->formatted($q->card_trans)."</b>)";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->epay, $val1, $val2)) {
                    $error++;
                    $param['type'] = 0;
                    $message = "Discrepancy (<b style='color:red;'>" . $this->formatted(abs($q->epay)) . "</b>) EPAY DAILY (<b>".$this->formatted($q->epay_daily)."</b>) and EPAY TRANSACTION (<b>".$this->formatted($q->epay_trans)."</b>)";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->dcard, $val1, $val2)) {
                    $error++;
                    $param['type'] = 0;
                    $message = "Discrepancy (<b style='color:red;'>" . $this->formatted(abs($q->dcard)) . "</b>) DCARD DAILY (<b>".$this->formatted($q->dcard_daily)."</b>) and DCARD TRANSACTION (<b>".$this->formatted($q->dcard_trans)."</b>)";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->payment, $val1, $val2)) {
                    $error++;
                    $message = "Discrepancy (<b style='color:red;'>" . $this->formatted(abs($q->payment)) . "</b>) PAYMENT DAILY (<b>".$this->formatted($q->payment_daily)."</b>) and PAYMENT TRANSACTION (<b>".$this->formatted($q->payment_trans)."</b> )<br>";
                   
                    $ter_no = $param['terminal_no'];
                    $trn_date = $param['transaction_date'];
                    $cccode = $param['merchant_code'];
                    $searchQueryTrans  = DB::select("SELECT 
                    SUM(CASH_SLS) CASH_SLS, SUM(OTHERSL_SLS) OTHERSL_SLS, SUM(CHECK_SLS) CHECK_SLS, SUM(GC_SLS) GC_SLS, SUM(MASTERCARD_SLS) MASTERCARD_SLS, SUM(VISA_SLS) VISA_SLS, SUM(AMEX_SLS) AMEX_SLS, SUM(DINERS_SLS) DINERS_SLS, SUM(JCB_SLS) JCB_SLS, SUM(GCASH_SLS) GCASH_SLS, SUM(PAYMAYA_SLS) PAYMAYA_SLS, SUM(ALIPAY_SLS) ALIPAY_SLS, SUM(WECHAT_SLS) WECHAT_SLS, SUM(GRAB_SLS) GRAB_SLS, SUM(FOODPANDA_SLS) FOODPANDA_SLS, SUM(MASTERDEBIT_SLS) MASTERDEBIT_SLS, SUM(VISADEBIT_SLS) VISADEBIT_SLS, SUM(PAYPAL_SLS) PAYPAL_SLS, SUM(ONLINE_SLS) ONLINE_SLS, SUM(OPEN_SALES) OPEN_SALES, SUM(OPEN_SALES_2) OPEN_SALES_2, SUM(OPEN_SALES_3) OPEN_SALES_3, SUM(OPEN_SALES_4) OPEN_SALES_4, SUM(OPEN_SALES_5) OPEN_SALES_5, SUM(OPEN_SALES_6) OPEN_SALES_6, SUM(OPEN_SALES_7) OPEN_SALES_7, SUM(OPEN_SALES_8) OPEN_SALES_8, SUM(OPEN_SALES_9) OPEN_SALES_9, SUM(OPEN_SALES_10) OPEN_SALES_10, SUM(OPEN_SALES_11) OPEN_SALES_11, SUM(GC_EXCESS) GC_EXCESS
                    FROM transaction_validations
                    WHERE CCCODE ='$cccode'
                    AND TRN_DATE = '$trn_date'
                    AND TER_NO = '$ter_no'
                    ;");
                    $message .="<b>Transaction:  </b><br>";
                    $arr_column_trans = ['CASH_SLS', 'OTHERSL_SLS', 'CHECK_SLS', 'GC_SLS', 'MASTERCARD_SLS', 'VISA_SLS', 'AMEX_SLS', 'DINERS_SLS', 'JCB_SLS', 'GCASH_SLS', 'PAYMAYA_SLS', 'ALIPAY_SLS', 'WECHAT_SLS', 'GRAB_SLS', 'FOODPANDA_SLS', 'MASTERDEBIT_SLS', 'VISADEBIT_SLS', 'PAYPAL_SLS', 'ONLINE_SLS', 'OPEN_SALES', 'OPEN_SALES_2', 'OPEN_SALES_3', 'OPEN_SALES_4', 'OPEN_SALES_5', 'OPEN_SALES_6', 'OPEN_SALES_7', 'OPEN_SALES_8', 'OPEN_SALES_9', 'OPEN_SALES_10', 'OPEN_SALES_11','GC_EXCESS'];
                    foreach($searchQueryTrans as $t){
                        foreach($arr_column_trans as $col){
                            $val = $t->$col;
                            $message .="$col = <b>$val</b> <br>"; 
                        }
                    }
               
                    $searchQueryDaily  = DB::select("SELECT 
                    SUM(CASH_SLS) CASH_SLS, SUM(OTHER_SLS) OTHER_SLS, SUM(CHECK_SLS) CHECK_SLS, SUM(GC_SLS) GC_SLS, SUM(MASTERCARD_SLS) MASTERCARD_SLS, SUM(VISA_SLS) VISA_SLS, SUM(AMEX_SLS) AMEX_SLS, SUM(DINERS_SLS) DINERS_SLS, SUM(JCB_SLS) JCB_SLS, SUM(GCASH_SLS) GCASH_SLS, SUM(PAYMAYA_SLS) PAYMAYA_SLS, SUM(ALIPAY_SLS) ALIPAY_SLS, SUM(WECHAT_SLS) WECHAT_SLS, SUM(GRAB_SLS) GRAB_SLS, SUM(FOODPANDA_SLS) FOODPANDA_SLS, SUM(MASTERDEBIT_SLS) MASTERDEBIT_SLS, SUM(VISADEBIT_SLS) VISADEBIT_SLS, SUM(PAYPAL_SLS) PAYPAL_SLS, SUM(ONLINE_SLS) ONLINE_SLS, SUM(OPEN_SALES) OPEN_SALES, SUM(OPEN_SALES_2) OPEN_SALES_2, SUM(OPEN_SALES_3) OPEN_SALES_3, SUM(OPEN_SALES_4) OPEN_SALES_4, SUM(OPEN_SALES_5) OPEN_SALES_5, SUM(OPEN_SALES_6) OPEN_SALES_6, SUM(OPEN_SALES_7) OPEN_SALES_7, SUM(OPEN_SALES_8) OPEN_SALES_8, SUM(OPEN_SALES_9) OPEN_SALES_9, SUM(OPEN_SALES_10) OPEN_SALES_10, SUM(OPEN_SALES_11) OPEN_SALES_11, SUM(GC_EXCESS) GC_EXCESS
                    FROM daily_validations
                    WHERE CCCODE ='$cccode'
                    AND TRN_DATE = '$trn_date'
                    AND TER_NO = '$ter_no'
                    ;");

                    $message .="<br><b>Daily:  </b><br>";
                    $arr_column_daily = ['CASH_SLS', 'OTHER_SLS', 'CHECK_SLS', 'GC_SLS', 'MASTERCARD_SLS', 'VISA_SLS', 'AMEX_SLS', 'DINERS_SLS', 'JCB_SLS', 'GCASH_SLS', 'PAYMAYA_SLS', 'ALIPAY_SLS', 'WECHAT_SLS', 'GRAB_SLS', 'FOODPANDA_SLS', 'MASTERDEBIT_SLS', 'VISADEBIT_SLS', 'PAYPAL_SLS', 'ONLINE_SLS', 'OPEN_SALES', 'OPEN_SALES_2', 'OPEN_SALES_3', 'OPEN_SALES_4', 'OPEN_SALES_5', 'OPEN_SALES_6', 'OPEN_SALES_7', 'OPEN_SALES_8', 'OPEN_SALES_9', 'OPEN_SALES_10', 'OPEN_SALES_11','GC_EXCESS'];
                    foreach($searchQueryDaily as $t){
                        foreach($arr_column_daily as $col){
                            $val = $t->$col;
                            $message .="$col = <b>$val</b> <br>"; 
                        }
                    }

                    $param['type'] = 0;
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->gc_sls, $val1, $val2)) {
                    $error++;
                    $param['type'] = 0;
                    $message = "Discrepancy (<b style='color:red;'>" . $this->formatted(abs($q->gc_sls)) . "</b>) GC_SLS DAILY (<b>".$this->formatted($q->gc_sls_daily)."</b>) and GC_SLS TRANSACTION (<b>".$this->formatted($q->gc_sls_trans)."</b>)";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->other_sls, $val1, $val2)) {
                    $error++;
                    $param['type'] = 0;
                    $message = "Discrepancy (<b style='color:red;'>" . $this->formatted(abs($q->other_sls)) . "</b>) OTHER_SLS DAILY (<b>".$this->formatted($q->other_sls_daily)."</b>) and OTHER_SLS TRANSACTION (<b>".$this->formatted($q->other_sls_trans)."</b>)";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->check_sls, $val1, $val2)) {
                    $error++;
                    $param['type'] = 0;
                    $message = "Discrepancy (<b style='color:red;'>" . $this->formatted(abs($q->check_sls)) . "</b>) CHECK_SLS DAILY (<b>".$this->formatted($q->check_sls_daily)."</b>) and CHECK_SLS TRANSACTION (<b>".$this->formatted($q->check_sls_trans)."</b>)";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->grab_sls, $val1, $val2)) {
                    $error++;
                    $param['type'] = 0;
                    $message = "Discrepancy (<b style='color:red;'>" . $this->formatted(abs($q->grab_sls)) . "</b>) GRAB_SLS DAILY (<b>".$this->formatted($q->grab_sls_daily)."</b>) and GRAB_SLS TRANSACTION (<b>".$this->formatted($q->grab_sls_trans)."</b>)";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->foodpanda_sls, $val1, $val2)) {
                    $error++;
                    $param['type'] = 0;
                    $message = "Discrepancy (<b style='color:red;'>" . $this->formatted(abs($q->foodpanda_sls)) . "</b>) FOODPANDA_SLS DAILY (<b>".$this->formatted($q->foodpanda_sls_daily)."</b>) and FOODPANDA_SLS TRANSACTION (<b>".$this->formatted($q->foodpanda_sls_trans)."</b>)";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->sales_total, $val1, $val2)) {
                    $error++;
                    $message = "Discrepancy (<b style='color:red;'>" . $this->formatted(abs($q->sales_total)) . "</b>) SALES_TOTAL DAILY (<b>".$this->formatted($q->sales_total_daily)."</b>) and SALES_TOTAL TRANSACTION (<b>".$this->formatted($q->sales_total_trans)."</b>) <br>";
                    
                    $arr_column_trans = ['VAT_AMNT', 'VATABLE_SLS', 'NONVAT_SLS', 'VATEXEMPT_SLS'];
                    $ter_no = $param['terminal_no'];
                    $trn_date = $param['transaction_date'];
                    $cccode = $param['merchant_code'];
                    $searchQuery  = DB::select("SELECT SUM(VAT_AMNT) VAT_AMNT, SUM(VATABLE_SLS) VATABLE_SLS, SUM(NONVAT_SLS) NONVAT_SLS, SUM(VATEXEMPT_SLS) VATEXEMPT_SLS
                    FROM transaction_validations
                    WHERE CCCODE ='$cccode'
                    AND TRN_DATE = '$trn_date'
                    AND TER_NO = '$ter_no'
                    AND SLS_FLAG = 'S'
                    ;");
                    $message .="<b>Transaction:  </b><br>";
                    $message .="( <b>SLS_FLAG=S </b>)  <br>";
                    foreach($searchQuery as $t){
                        foreach($arr_column_trans as $col){
                            $val = $t->$col;
                            $message .="$col = <b>$val</b> <br>"; 
                        }
                    }
                    $searchQuery  = DB::select("SELECT SUM(VAT_AMNT) VAT_AMNT, SUM(VATABLE_SLS) VATABLE_SLS, SUM(NONVAT_SLS) NONVAT_SLS, SUM(VATEXEMPT_SLS) VATEXEMPT_SLS
                    FROM transaction_validations
                    WHERE CCCODE ='$cccode'
                    AND TRN_DATE = '$trn_date'
                    AND TER_NO = '$ter_no'
                    AND SLS_FLAG = 'R'
                    ;");
                    $message .="Transaction:  <br>";
                    $message .="( <b>SLS_FLAG=R </b>)  <br>";
                    foreach($searchQuery as $t){
                        foreach($arr_column_trans as $col){
                            $val = $t->$col;
                            $message .="$col = <b>$val</b> <br>"; 
                        }
                    }

                    $arr_column_daily = ['NEW_GRNTOT', 'OLD_GRNTOT'];
                    $searchQuery  = $this->searchQuery('daily_validations', $arr_column_daily, $param);
                    $message .="<br><b>Daily:  </b><br>";
                    foreach($searchQuery as $t){
                        foreach($arr_column_daily as $col){
                            $val = $t->$col;
                            $message .="$col = <b>$val</b> <br>"; 
                        }
                    }


                    $param['type'] = 0;
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if ($q->notrn > 0) {
                    $error++;
                    $param['type'] = 0;
                    $message = "Discrepancy (<b style='color:red;'>" . $this->formatted(abs($q->notrn)) . "</b>) NO_TRN (<b>$q->daily_notrn</b>) not equal to total of transaction (<b>$q->daily_notrn</b>)";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $param['error_type'] = 'Cross';
            $param['filename'] = $params['filename'];
            $param['merchant_code'] = $CCCODE;
            $param['transaction_date'] = $TRN_DATE;
            $param['transaction_no'] = null;
            $param['terminal_no'] = $TER_NO;
            $param['type'] = 0;
            $param['error_description'] = $errorMessage;
            $Logs->savelogs($param); /**save logs */
        }

        return $error;

    }
    public function in_range($x, $min, $max)
    {
        $x = abs(number_format((float) $x, 2, '', ''));
        return ($min <= $x) && ($x <= $max);
    }
    public function formatted($num)
    {
        return number_format($num, 2, ".", ",");
    }
    public function searchQuery($tablename, $arr_column, $param){
        $ter_no = $param['terminal_no'];
        $trn_date = $param['transaction_date'];
        $cccode = $param['merchant_code'];
        return DB::select("SELECT " . implode(',', $arr_column) . "
        FROM $tablename
        WHERE CCCODE ='$cccode'
        AND TRN_DATE = '$trn_date'
        AND TER_NO = '$ter_no'
        ;");
    }
}