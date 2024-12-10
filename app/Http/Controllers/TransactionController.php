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
}