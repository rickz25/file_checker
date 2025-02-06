<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyValidation;
use App\Models\Logs;
use App\Models\ThresholdSettings;
use App\Models\CrossValidation;

class DailyController extends Controller
{
    public function insertDaily($jsondata)
    {
        $data = json_decode($jsondata, true);
        $filename = $data['filename'];
        $filedata = $data['filedata'];
        $errorMessage = "";
        $merchant_code = $filedata['CCCODE'];
        $storeName = $filedata['MERCHANT_NAME'];
        $arrFilename = $this->formatDateEOD(trim($filename));
        $TRN_DATE = $arrFilename['trn_date'];
        $DailyMapping = new DailyValidation;
        $Logs = new Logs;
        try {
            foreach ($filedata['TERMINALS'] as $daily) {
                $TER_NO = $daily['TER_NO'];
                $param['daily'] = $daily;
                $param['TER_NO'] = $TER_NO;
                $param['merchant_code'] = $merchant_code;
                $param['merchant_name'] = $storeName;
                $param['TRN_DATE'] = $TRN_DATE;
                $param['filename'] = $filename;
                $DailyMapping->saveDaily($param);
            }
        } catch (\Exception $e) {
            $errorMessage .= $e->getMessage();
        }

        $param['terminal_no'] = $filedata['TERMINALS'][0]['TER_NO'];
        $param['error_type'] = 'Daily';
        $param['filename'] = $filename;
        $param['merchant_code'] = $merchant_code;
        $param['transaction_date'] = $TRN_DATE;
        $param['transaction_no'] = null;

        if ($errorMessage != "") {
            
            $param['error_description'] = $errorMessage;
            $Logs->savelogs($param); /**save logs */

        } else {

            if($filedata['TERMINALS'][0]['NO_TRN'] !=0){
                $log = Logs::count();
                if($log >0){
                    $result['status'] = 0;
                    $result['message'] = 'not tally';
                    return json_encode($result);
                }else{
                    $result['status'] = 1;
                    $result['message'] = 'tally';
                    return json_encode($result);
                }

            }
            
        }
    }

    public function processDaily(){
        $Threshold = ThresholdSettings::where('id', 1)->first();
        $Logs = new Logs;
        $CrossValidation = new CrossValidation;
        $queryDaily = (new DailyValidation)->validateDaily();
        foreach ($queryDaily as $q) {
            $TER_NO = trim($q->TER_NO);
            $merchant_code = trim($q->CCCODE);
            $transaction_date = trim($q->TRN_DATE);
            $filename = trim($q->fileN);

            $val1 = number_format((float) $Threshold->value_from, 2, '.', '');
            $val2 = number_format((float) $Threshold->value_to, 2, '.', '');

            $param['error_type'] = 'Daily';
            $param['filename'] = $filename;
            $param['merchant_code'] = $merchant_code;
            $param['transaction_date'] = $transaction_date;
            $param['transaction_no'] = null;
            $param['terminal_no'] = $TER_NO;
            $error = 0;

            if (!$this->in_range($q->gross, $val1, $val2)) {
                $message = "Discrepancy (<b style='color:red;'>" . $this->formatted(abs($q->gross)) . "</b>) GROSS_SLS (<b>".$this->formatted($q->gross_sls)."</b>) and GROSS TOTAL (<b>".$this->formatted($q->gross_sum)."</b>) </br>";
                $arr_column = ['VAT_AMNT', 'VATABLE_SLS', 'NONVAT_SLS', 'VATEXEMPT_SLS', 'VATEXEMPT_AMNT', 'LOCAL_TAX', 'VOID_AMNT', 'DISCOUNTS', 'REFUND_AMT', 'SCHRGE_AMT'];
                $searchQuery  = $Daily->searchQuery($q->gross, $arr_column, $param);
                foreach($searchQuery as $t){
                    foreach($arr_column as $col){
                        $val = $t->$col;
                        $message .="$col = <b>$val</b> <br>"; 
                    }
                }
                $param['error_description'] = $message;
                $error = 1;
                $param['type'] = 0;
                $Logs->savelogs($param); /**save logs */
            }
            if (!$this->in_range($q->discount, $val1, $val2)) {
                $message = "Discrepancy (<b style='color:red;'>" . $this->formatted(abs($q->DSICOUNT)) . "</b>) DISCOUNT_SLS (<b>".$this->formatted($q->DSICOUNT_sls)."</b>) and DSICOUNT TOTAL (<b>".$this->formatted($q->DSICOUNT_sum)."</b>) </br>";
                $arr_column = ['SNRCIT_DISC', 'PWD_DISC', 'EMPLO_DISC', 'AYALA_DISC', 'STORE_DISC', 'OTHER_DISC'];
                $searchQuery  = $Daily->searchQuery($q->gross, $arr_column, $param);
                foreach($searchQuery as $t){
                    foreach($arr_column as $col){
                        $val = $t->$col;
                        $message .="$col = <b>$val</b> <br>"; 
                    }
                }
                $param['error_description'] = $message;
                $error = 1;
                $param['type'] = 0;
                $Logs->savelogs($param); /**save logs */
            }
            if (!$this->in_range($q->no_disc, $val1, $val2)) {
                $message = "Discrepancy (<b style='color:red;'>" . $this->formatted(abs($q->no_disc)) . "</b>) NO_DISC_SLS (<b>".$this->formatted($q->no_disc_sls)."</b>) and NO_DISC TOTAL (<b>".$this->formatted($q->no_disc_sum)."</b>) </br>";
                $arr_column = ['NO_SNRCIT', 'NO_PWD', 'NO_EMPLO', 'NO_AYALA', 'NO_STORE', 'NO_OTHER_DISC'];
                $searchQuery  = $Daily->searchQuery($q->gross, $arr_column, $param);
                foreach($searchQuery as $t){
                    foreach($arr_column as $col){
                        $val = $t->$col;
                        $message .="$col = <b>$val</b> <br>"; 
                    }
                }
                $param['error_description'] = $message;
                $error = 1;
                $param['type'] = 0;
                $Logs->savelogs($param); /**save logs */
            }
            if (!$this->in_range($q->card, $val1, $val2)) {
                $message = "Discrepancy (<b style='color:red;'>" . $this->formatted(abs($q->card)) . "</b>) CARD_SLS (<b>".$this->formatted($q->card_sls)."</b>) and CARD TOTAL (<b>".$this->formatted($q->card_sum)."</b>) </br>";
                $arr_column = ['MASTERCARD_SLS', 'VISA_SLS', 'AMEX_SLS', 'DINERS_SLS', 'JCB_SLS'];
                $searchQuery  = $Daily->searchQuery($q->gross, $arr_column, $param);
                foreach($searchQuery as $t){
                    foreach($arr_column as $col){
                        $val = $t->$col;
                        $message .="$col = <b>$val</b> <br>"; 
                    }
                }
                $param['error_description'] = $message;
                $error = 1;
                $param['type'] = 0;
                $Logs->savelogs($param); /**save logs */
            }
            if (!$this->in_range($q->epay, $val1, $val2)) {
                $message = "Discrepancy (<b style='color:red;'>" . $this->formatted(abs($q->epay)) . "</b>) EPAY_SLS (<b>".$this->formatted($q->epay_sls)."</b>) and EPAY TOTAL (<b>".$this->formatted($q->epay_sum)."</b>) </br>";
                $arr_column = ['GCASH_SLS', 'PAYMAYA_SLS', 'ALIPAY_SLS', 'WECHAT_SLS'];
                $searchQuery  = $Daily->searchQuery($q->gross, $arr_column, $param);
                foreach($searchQuery as $t){
                    foreach($arr_column as $col){
                        $val = $t->$col;
                        $message .="$col = <b>$val</b> <br>"; 
                    }
                }
                $param['error_description'] = $message;
                $error = 1;
                $param['type'] = 0;
                $Logs->savelogs($param); /**save logs */
            }
            if (!$this->in_range($q->dcard, $val1, $val2)) {
                $message = "Discrepancy (<b style='color:red;'>" . $this->formatted(abs($q->dcard)) . "</b>) DCARD_SLS (<b>".$this->formatted($q->dcard_sls)."</b>) and DCARD TOTAL (<b>".$this->formatted($q->dcard_sum)."</b>) </br>";
                $arr_column = ['MASTERDEBIT_SLS', 'VISADEBIT_SLS'];
                $searchQuery  = $Daily->searchQuery($q->gross, $arr_column, $param);
                foreach($searchQuery as $t){
                    foreach($arr_column as $col){
                        $val = $t->$col;
                        $message .="$col = <b>$val</b> <br>"; 
                    }
                }
                $param['error_description'] = $message;
                $error = 1;
                $param['type'] = 0;
                $Logs->savelogs($param); /**save logs */
            }
            if (!$this->in_range($q->new_grand_total, $val1, $val2)) {
                $message = "Discrepancy (<b style='color:red;'>" . $this->formatted(abs($q->new_grand_total)) . "</b>) NEW_GRNTOT (<b>".$this->formatted($q->new_grand_total_sls)."</b>) and NEW_GRNTOT TOTAL (<b>".$this->formatted($q->new_grand_total_sum)."</b>) </br>";
                $arr_column = ['VAT_AMNT', 'VATABLE_SLS', 'NONVAT_SLS', 'VATEXEMPT_SLS', 'OLD_GRNTOT', 'LOCAL_TAX'];
                $searchQuery  = $Daily->searchQuery($q->gross, $arr_column, $param);
                foreach($searchQuery as $t){
                    foreach($arr_column as $col){
                        $val = $t->$col;
                        $message .="$col = <b>$val</b> <br>"; 
                    }
                }
                $param['error_description'] = $message;
                $error = 1;
                $param['type'] = 0;
                $Logs->savelogs($param); /**save logs */
            }
            if (!$this->in_range($q->netsales, $val1, $val2)) {
                $message = "Discrepancy (<b style='color:red;'>" . $this->formatted(abs($q->netsales)) . "</b>) NETSALES (<b>".$this->formatted($q->netsales_sls)."</b>) and NETSALES TOTAL (<b>".$this->formatted($q->netsales_sum)."</b>) </br>";
                $arr_column = ['VATABLE_SLS', 'NONVAT_SLS', 'VATEXEMPT_SLS'];
                $searchQuery  = $Daily->searchQuery($q->gross, $arr_column, $param);
                foreach($searchQuery as $t){
                    foreach($arr_column as $col){
                        $val = $t->$col;
                        $message .="$col = <b>$val</b> <br>"; 
                    }
                }
                $param['error_description'] = $message;
                $error = 1;
                $param['type'] = 0;
                $Logs->savelogs($param); /**save logs */
            }
            
            // Cross Validation

            if ($error == 0) {
                $count_log = Logs::where(['type' => 0, 'merchant_code' => $merchant_code, 'transaction_date' => $transaction_date, 'terminal_no' => $TER_NO])->count();
                if ($count_log == 0) {
                    $params['filename'] = $filename;
                    $params['transaction_date'] = $transaction_date;
                    $params['merchant_code'] = $merchant_code;
                    $params['terminal_no'] = $TER_NO;
                    $cross = $CrossValidation->validateSales($params);
                    //echo "<pre>"; print_r($cross); "</pre>"; die;

                    $param['error_type'] = 'Success';
                    $param['filename'] = $filename;
                    $param['merchant_code'] = $merchant_code;
                    $param['transaction_date'] = $transaction_date;
                    $param['transaction_no'] = null;
                    $param['terminal_no'] = $TER_NO;
                    if ($cross == 0) {
                        $param['type'] = 1;
                        $param['error_description'] = 'Tally';
                        $Logs->savelogs($param); /**save logs */
                    }
                }
            }
        }
    }
    public function formatDateEOD($filename)
    {
        $merchant_code = substr($filename, 3, 17);
        $m = substr($filename, 20, 2);
        $d = substr($filename, 22, 2);
        $y = '20' . substr($filename, 24, 2);
        $trndate = $y . '-' . $m . '-' . $d;
        return ['code' => $merchant_code, 'trn_date' => $trndate];
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