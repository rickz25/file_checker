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


            // foreach($trans_query as $k=>$q){

            //     CrossValidation::where(['id'=>1, 'cccode'=>$CCCODE,'trn_date'=>$TRN_DATE,'ter_no'=>$TER_NO])->delete();
            //     $Cross =  new CrossValidation;
            //     $Cross->id = 1;
            //     $Cross->cccode = $q->CCCODE;
            //     $Cross->trn_date = $q->TRN_DATE;
            //     $Cross->ter_no = $q->TER_NO;
            //     $Cross->filename = $q->fileN;
            //     $Cross->gross = $q->gross;
            //     $Cross->card = $q->card;
            //     $Cross->epay = $q->epay;
            //     $Cross->dcard = $q->dcard;
            //     $Cross->gc_sls = $q->gc_sls;
            //     $Cross->other_sls = $q->other_sls;
            //     $Cross->check_sls = $q->check_sls;
            //     $Cross->grab_sls = $q->grab_sls;
            //     $Cross->foodpanda_sls = $q->foodpanda_sls;
            //     $Cross->notrn = $q->notrn;
            //     $Cross->sales_total = $q->sales_total;
            //     $Cross->payment = $q->payment;

            //     $Cross->save();

            // }

            // foreach($daily_query as $k=>$q){

            //     CrossValidation::where(['id'=>2, 'cccode'=>$CCCODE,'trn_date'=>$TRN_DATE,'ter_no'=>$TER_NO])->delete();
            //     $Cross =  new CrossValidation;
            //     $Cross->id = 2;
            //     $Cross->cccode = $q->CCCODE;
            //     $Cross->trn_date = $q->TRN_DATE;
            //     $Cross->ter_no = $q->TER_NO;
            //     $Cross->filename = $q->fileN;
            //     $Cross->gross = $q->gross;
            //     $Cross->card = $q->card;
            //     $Cross->epay = $q->epay;
            //     $Cross->dcard = $q->dcard;
            //     $Cross->gc_sls = $q->gc_sls;
            //     $Cross->other_sls = $q->other_sls;
            //     $Cross->check_sls = $q->check_sls;
            //     $Cross->grab_sls = $q->grab_sls;
            //     $Cross->foodpanda_sls = $q->foodpanda_sls;
            //     $Cross->notrn = $q->notrn;
            //     $Cross->sales_total = $q->sales_total;
            //     $Cross->payment = $q->payment;
            //     $Cross->save();
            // }


            // $cross_query = CrossValidation::select('cccode',
            // 'trn_date',
            // 'ter_no',
            //  DB::raw("printf('%.2f', SUM(CAST(gross AS DECIMAL(10,2))) - (SELECT SUM(CAST(gross AS DECIMAL(10,2))) from cross_validations WHERE id=1 AND cccode='$CCCODE' AND trn_date='$TRN_DATE' AND ter_no ='$TER_NO')) as gross_cross"),
            //  DB::raw("printf('%.2f', SUM(CAST(card AS DECIMAL(10,2))) - (SELECT SUM(CAST(card AS DECIMAL(10,2))) from cross_validations WHERE id=1 AND cccode='$CCCODE' AND trn_date='$TRN_DATE' AND ter_no ='$TER_NO')) as card"),
            //  DB::raw("printf('%.2f', SUM(CAST(epay AS DECIMAL(10,2))) - (SELECT SUM(CAST(epay AS DECIMAL(10,2))) from cross_validations WHERE id=1 AND cccode='$CCCODE' AND trn_date='$TRN_DATE' AND ter_no ='$TER_NO')) as epay"),
            //  DB::raw("printf('%.2f', SUM(CAST(dcard AS DECIMAL(10,2))) - (SELECT SUM(CAST(dcard AS DECIMAL(10,2))) from cross_validations WHERE id=1 AND cccode='$CCCODE' AND trn_date='$TRN_DATE' AND ter_no ='$TER_NO')) as dcard"),
            //  DB::raw("printf('%.2f', SUM(CAST(gc_sls AS DECIMAL(10,2))) - (SELECT SUM(CAST(gc_sls AS DECIMAL(10,2))) from cross_validations WHERE id=1 AND cccode='$CCCODE' AND trn_date='$TRN_DATE' AND ter_no ='$TER_NO')) as gc_sls"),
            //  DB::raw("printf('%.2f', SUM(CAST(other_sls AS DECIMAL(10,2))) - (SELECT SUM(CAST(other_sls AS DECIMAL(10,2))) from cross_validations WHERE id=1 AND cccode='$CCCODE' AND trn_date='$TRN_DATE' AND ter_no ='$TER_NO')) as other_sls"),
            //  DB::raw("printf('%.2f', SUM(CAST(check_sls AS DECIMAL(10,2))) - (SELECT SUM(CAST(check_sls AS DECIMAL(10,2))) from cross_validations WHERE id=1 AND cccode='$CCCODE' AND trn_date='$TRN_DATE' AND ter_no ='$TER_NO')) as check_sls"),
            //  DB::raw("printf('%.2f', SUM(CAST(grab_sls AS DECIMAL(10,2))) - (SELECT SUM(CAST(grab_sls AS DECIMAL(10,2))) from cross_validations WHERE id=1 AND cccode='$CCCODE' AND trn_date='$TRN_DATE' AND ter_no ='$TER_NO'))  as grab_sls"),
            //  DB::raw("printf('%.2f', SUM(CAST(foodpanda_sls AS DECIMAL(10,2))) - (SELECT SUM(CAST(foodpanda_sls AS DECIMAL(10,2))) from cross_validations WHERE id=1 AND cccode='$CCCODE' AND trn_date='$TRN_DATE' AND ter_no ='$TER_NO')) as foodpanda_sls"),
            //  DB::raw("SUM(CAST(notrn AS int)) - (SELECT sum(CAST(notrn AS int)) from cross_validations WHERE id=1 AND cccode='$CCCODE' AND trn_date='$TRN_DATE' AND ter_no ='$TER_NO') as notrn"),
            //  DB::raw("printf('%.2f', SUM(CAST(sales_total AS DECIMAL(10,2))) - (SELECT SUM(CAST(sales_total AS DECIMAL(10,2))) from cross_validations WHERE id=1 AND cccode='$CCCODE' AND trn_date='$TRN_DATE' AND ter_no ='$TER_NO')) as sales_total"),
            //  DB::raw("printf('%.2f', SUM(CAST(payment AS DECIMAL(10,2))) - (SELECT SUM(CAST(payment AS DECIMAL(10,2))) from cross_validations WHERE id=1 AND cccode='$CCCODE' AND trn_date='$TRN_DATE' AND ter_no ='$TER_NO')) as payment"),
            //  DB::raw("CAST(notrn AS int) as daily_notrn"),
            //  DB::raw("(SELECT CAST(notrn AS int) from cross_validations WHERE id=1 AND cccode='$CCCODE' AND trn_date='$TRN_DATE' AND ter_no ='$TER_NO') as trans_notrn")
            // )
            // ->where(['id'=>2,'cccode'=>$CCCODE, 'trn_date'=>$TRN_DATE, 'ter_no' => $TER_NO])
            // ->groupBy('cccode', 'trn_date', 'ter_no')
            // ->get();

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
                    $message = "Discrepancy (" . $this->formatted(abs($q->gross)) . ") GROSS DAILY (".$this->formatted($q->gross_daily).") and GROSS TRANSACTION (".$this->formatted($q->gross_trans).")";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->card, $val1, $val2)) {
                    $error++;
                    $param['type'] = 0;
                    $message = "Discrepancy (" . $this->formatted(abs($q->card)) . ") CARD DAILY (".$this->formatted($q->card_daily).") and CARD TRANSACTION (".$this->formatted($q->card_trans).")";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->epay, $val1, $val2)) {
                    $error++;
                    $param['type'] = 0;
                    $message = "Discrepancy (" . $this->formatted(abs($q->epay)) . ") EPAY DAILY (".$this->formatted($q->epay_daily).") and EPAY TRANSACTION (".$this->formatted($q->epay_trans).")";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->dcard, $val1, $val2)) {
                    $error++;
                    $param['type'] = 0;
                    $message = "Discrepancy (" . $this->formatted(abs($q->dcard)) . ") DCARD DAILY (".$this->formatted($q->dcard_daily).") and DCARD TRANSACTION (".$this->formatted($q->dcard_trans).")";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->payment, $val1, $val2)) {
                    $error++;
                    $param['type'] = 0;
                    $message = "Discrepancy (" . $this->formatted(abs($q->payment)) . ") PAYMENT DAILY (".$this->formatted($q->payment_daily).") and PAYMENT TRANSACTION (".$this->formatted($q->payment_trans).")";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->gc_sls, $val1, $val2)) {
                    $error++;
                    $param['type'] = 0;
                    $message = "Discrepancy (" . $this->formatted(abs($q->gc_sls)) . ") GC_SLS DAILY (".$this->formatted($q->gc_sls_daily).") and GC_SLS TRANSACTION (".$this->formatted($q->gc_sls_trans).")";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->other_sls, $val1, $val2)) {
                    $error++;
                    $param['type'] = 0;
                    $message = "Discrepancy (" . $this->formatted(abs($q->other_sls)) . ") OTHER_SLS DAILY (".$this->formatted($q->other_sls_daily).") and OTHER_SLS TRANSACTION (".$this->formatted($q->other_sls_trans).")";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->check_sls, $val1, $val2)) {
                    $error++;
                    $param['type'] = 0;
                    $message = "Discrepancy (" . $this->formatted(abs($q->check_sls)) . ") CHECK_SLS DAILY (".$this->formatted($q->check_sls_daily).") and CHECK_SLS TRANSACTION (".$this->formatted($q->check_sls_trans).")";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->grab_sls, $val1, $val2)) {
                    $error++;
                    $param['type'] = 0;
                    $message = "Discrepancy (" . $this->formatted(abs($q->grab_sls)) . ") GRAB_SLS DAILY (".$this->formatted($q->grab_sls_daily).") and GRAB_SLS TRANSACTION (".$this->formatted($q->grab_sls_trans).")";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->foodpanda_sls, $val1, $val2)) {
                    $error++;
                    $param['type'] = 0;
                    $message = "Discrepancy (" . $this->formatted(abs($q->foodpanda_sls)) . ") FOODPANDA_SLS DAILY (".$this->formatted($q->foodpanda_sls_daily).") and FOODPANDA_SLS TRANSACTION (".$this->formatted($q->foodpanda_sls_trans).")";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->sales_total, $val1, $val2)) {
                    $error++;
                    $param['type'] = 0;
                    $message = "Discrepancy (" . $this->formatted(abs($q->sales_total)) . ") SALES_TOTAL DAILY (".$this->formatted($q->sales_total_daily).") and SALES_TOTAL TRANSACTION (".$this->formatted($q->sales_total_trans).")";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if ($q->notrn > 0) {
                    $error++;
                    $param['type'] = 0;
                    $message = "NO_TRN ($q->daily_notrn) not equal to total of transaction ($q->daily_notrn), discrepancy (" . $this->formatted(abs($q->notrn)) . ")";
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
}