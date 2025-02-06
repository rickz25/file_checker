<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Logs;
use App\Models\TransactionValidation;
use App\Models\Items;
use App\Models\ThresholdSettings;

class TransactionController extends Controller
{
    public function insertTransaction($jsondata)
    {
        $data = json_decode($jsondata, true);
        $filename = $data['filename'];
        $filedata = $data['filedata'];
        $errorMessage = "";
        $merchant_code = $filedata['CCCODE'];
        $storeName = $filedata['MERCHANT_NAME'];
        $TRN_DATE = $filedata['TRN_DATE'];
        $NO_TRN = $filedata['NO_TRN'];
        $trans_arr = $filedata['TRANSACTION'];
        
        $param['merchant_code'] = $merchant_code;
        $param['trn_date'] = $TRN_DATE;
        $param['TRN_TIME'] = $trans_arr[0]['TRN_TIME'];
        $param['TER_NO'] = $trans_arr[0]['TER_NO'];
        $Items = new Items;
        $Transaction = new TransactionValidation;
        $Logs = new Logs;
            $Transaction->deleteTransaction($param);
            foreach ($trans_arr as &$values) {
                $TRANS_NO = trim($values['TRANSACTION_NO']);
                $TER_NO = trim($values['TER_NO']);
                // $Items->itemLogs($values, $TRN_DATE, $merchant_code, $filename);
                unset($values["ITEMS"], $values['LDISC'], $values['QTY_SLD']); //remove items in array

                $param['values'] = $values;
                $param['TRANS_NO'] = $TRANS_NO;
                $param['TER_NO'] = $TER_NO;
                $param['merchant_name'] = $storeName;
                $param['NO_TRN'] = $NO_TRN;
                $param['filename'] = $filename;
                $Transaction->saveTransaction($param);
            }
        if ($errorMessage != "") {
            $param['type'] = 0;
            $param['error_type'] = 'Transaction';
            $param['filename'] = $filename;
            $param['merchant_code'] = $merchant_code;
            $param['transaction_date'] = $TRN_DATE;
            $param['transaction_no'] = null;
            $param['terminal_no'] = $param['TER_NO'];
            $param['error_description'] = $errorMessage;
            $Logs->savelogs($param); /**save logs */
        } else {
            $result['status'] = 1;
            $result['message'] = 'success';
            return json_encode($result);
        }
    }

    public function processTransaction(){
        $Threshold = ThresholdSettings::where('id', 1)->first();
        $Logs = new Logs;
        $queryTrans = (new TransactionValidation)->validateTransaction();
        // echo "<pre>"; print_r($queryTrans); "</pre>"; die;
        foreach ($queryTrans as $q) {
            $transaction_no = trim($q->TRANSNO);
            $TER_NO = trim($q->TER_NO);
            $merchant_code = trim($q->CCCODE);
            $transaction_date = trim($q->TRN_DATE);
            $filename = trim($q->fileN);

            $val1 = number_format((float) $Threshold->value_from, 2, '.', '');
            $val2 = number_format((float) $Threshold->value_to, 2, '.', '');

            $param['error_type'] = 'Transaction';
            $param['filename'] = $filename;
            $param['merchant_code'] = $merchant_code;
            $param['transaction_date'] = $transaction_date;
            $param['transaction_no'] = $transaction_no;
            $param['terminal_no'] = $TER_NO;

            if (!$this->in_range($q->gross, $val1, $val2)) {
                
                $message = "Discrepancy (<b style='color:red;'>" . $this->formatted(abs($q->gross)) . "</b>) GROSS_SLS (<b>".$this->formatted($q->gross_sls)."</b>) and GROSS TOTAL (<b>".$this->formatted($q->gross_sum)."</b>) <br>";
                $arr_column = ['VAT_AMNT', 'VATABLE_SLS', 'NONVAT_SLS', 'VATEXEMPT_SLS', 'VATEXEMPT_AMNT', 'LOCAL_TAX', 'PWD_DISC', 'SNRCIT_DISC', 'EMPLO_DISC', 'AYALA_DISC', 'STORE_DISC', 'OTHER_DISC', 'SCHRGE_AMT', 'OTHER_SCHR'];
                $searchQuery  = $Transaction->searchQuery($q->gross, $arr_column, $param);
                foreach($searchQuery as $t){
                    foreach($arr_column as $col){
                        $val = $t->$col;
                        $message .="$col = <b>$val</b> <br>"; 
                    }
                }
                $param['type'] = 0;
                $param['error_description'] = $message;
                $Logs->savelogs($param); /**save logs */
            }
            if (!$this->in_range($q->payment, $val1, $val2)) {
                $message = "Discrepancy (<b style='color:red;'>" . $this->formatted(abs($q->payment)) . "</b>) PAYMENT (<b>".$this->formatted($q->payment_sls)."</b>) and PAYMENT TOTAL (<b>".$this->formatted($q->payment_sum)."</b>) <br>";
                $arr_column = ['CASH_SLS', 'OTHERSL_SLS', 'CHECK_SLS', 'GC_SLS', 'MASTERCARD_SLS', 'VISA_SLS', 'AMEX_SLS', 'DINERS_SLS', 'JCB_SLS', 'GCASH_SLS', 'PAYMAYA_SLS', 'ALIPAY_SLS', 'WECHAT_SLS', 'GRAB_SLS', 'FOODPANDA_SLS', 'MASTERDEBIT_SLS', 'VISADEBIT_SLS', 'PAYPAL_SLS', 'ONLINE_SLS', 'OPEN_SALES', 'OPEN_SALES_2', 'OPEN_SALES_3', 'OPEN_SALES_4', 'OPEN_SALES_5', 'OPEN_SALES_6', 'OPEN_SALES_7', 'OPEN_SALES_8', 'OPEN_SALES_9', 'OPEN_SALES_10', 'OPEN_SALES_11', 'GC_EXCESS'];
                $searchQuery  = $Transaction->searchQuery($q->gross, $arr_column, $param);
                foreach($searchQuery as $t){
                    foreach($arr_column as $col){
                        $val = $t->$col;
                        $message .="$col = <b>$val</b> <br>"; 
                    }
                }
                $param['type'] = 0;
                $param['error_description'] = $message;
                $Logs->savelogs($param); /**save logs */
            }
            if (!$this->in_range($q->card, $val1, $val2)) {
                $message = "Discrepancy (<b style='color:red;'>" . $this->formatted(abs($q->card)) . "</b>) CARD_SLS (<b>".$this->formatted($q->card_sls)."</b>) and CARD TOTAL (<b>".$this->formatted($q->card_sum)."</b>) <br>";
                $arr_column = ['MASTERCARD_SLS', 'VISA_SLS', 'AMEX_SLS', 'DINERS_SLS', 'JCB_SLS'];
                $searchQuery  = $Transaction->searchQuery($q->gross, $arr_column, $param);
                foreach($searchQuery as $t){
                    foreach($arr_column as $col){
                        $val = $t->$col;
                        $message .="$col = <b>$val</b> <br>"; 
                    }
                }
                $param['type'] = 0;
                $param['error_description'] = $message;
                $Logs->savelogs($param); /**save logs */
            }
            if (!$this->in_range($q->epay, $val1, $val2)) {
                $message = "Discrepancy (<b style='color:red;'>" . $this->formatted(abs($q->epay)) . "</b>) EPAY_SLS (<b>".$this->formatted($q->epay_sls)."</b>) and EPAY TOTAL (<b>".$this->formatted($q->epay_sum)."</b>) <br>";
                $arr_column = ['GCASH_SLS', 'PAYMAYA_SLS', 'ALIPAY_SLS', 'WECHAT_SLS'];
                $searchQuery  = $Transaction->searchQuery($q->gross, $arr_column, $param);
                foreach($searchQuery as $t){
                    foreach($arr_column as $col){
                        $val = $t->$col;
                        $message .="$col = <b>$val</b> <br>"; 
                    }
                }
                $param['type'] = 0;
                $param['error_description'] = $message;
                $Logs->savelogs($param); /**save logs */
            }
            if (!$this->in_range($q->dcard, $val1, $val2)) {
                $message = "DCARD discrepancy (<b style='color:red;'>" . $this->formatted(abs($q->dcard)) . "</b>) <br>";
                $arr_column = ['MASTERDEBIT_SLS', 'VISADEBIT_SLS'];
                $searchQuery  = $Transaction->searchQuery($q->gross, $arr_column, $param);
                foreach($searchQuery as $t){
                    foreach($arr_column as $col){
                        $val = $t->$col;
                        $message .="$col = <b>$val</b> <br>"; 
                    }
                }
                $param['type'] = 0;
                $param['error_description'] = $message;
                $Logs->savelogs($param); /**save logs */
            }
        }
    }

    public function formatDateTRANS($filename)
    {
        $merchant_code = substr($filename, 0, 17);
        $m = substr($filename, 17, 2);
        $d = substr($filename, 19, 2);
        $y = substr($filename, 21, 2);
        $trndate = '20' . $y . '-' . $m . '-' . $d;
        $TER_NO = substr($filename, 23, 3);
        return ['code' => $merchant_code, 'trn_date' => $trndate, 'terno' => $TER_NO];
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