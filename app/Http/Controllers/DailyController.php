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
    public function formatDateEOD($filename)
    {
        $merchant_code = substr($filename, 3, 17);
        $m = substr($filename, 20, 2);
        $d = substr($filename, 22, 2);
        $y = '20' . substr($filename, 24, 2);
        $trndate = $y . '-' . $m . '-' . $d;
        return ['code' => $merchant_code, 'trn_date' => $trndate];
    }
}