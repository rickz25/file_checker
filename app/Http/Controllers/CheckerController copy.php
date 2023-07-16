<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Checker;
use App\Models\TransactionValidation;
use App\Models\DailyValidation;
use App\Models\Logs;
use App\Models\ThresholdSettings;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DailyController;
use Illuminate\Http\Client\ConnectionException;
use File;
use DateTime;

class CheckerController extends Controller
{
    //
    public function index()
    {
        $Threshold = ThresholdSettings::where('id',1)->first();
        return view('index')->with([
            'threshold' => $Threshold,
          ]);

    }
    public function checkFile(Request $request)
    {
        TransactionValidation::truncate();
        DailyValidation::truncate();
        Logs::truncate();
        $CheckerModel = new Checker;
       $request->validate([
            'files' => 'required',
            'files.*' => 'mimes:csv,txt,xlx,xls'
        ]);

        $final = [];
        $logs = '';
        if ($request->TotalFiles > 0) {
            $arrError = [];
            for ($x = 0; $x < $request->TotalFiles; $x++) {
                if ($request->hasFile('files' . $x)) {
                    $file = $request->file('files' . $x);
                    $filename = $file->getClientOriginalName();

                    $start3 = substr($filename, 0, 3);
                    $errlogs = "";
                    foreach (file($file) as $y) {
                        $arr = explode(',', $y);
                        $col = $arr[0];
                        $val = $arr[1];
                        $trim_col = str_replace("'", "", str_replace('"', '', $col));
                        if (preg_match('/"/', $col) == 1) {
                            $errlogs .= "There's a quotation in Column " . $trim_col . ".<br>";
                        } else if (preg_match("/'/", $col) == 1) {
                            $errlogs .= "There's a quotation in Column " . $trim_col . ".<br>";
                        }
                        if (preg_match('/"/', $val) == 1) {
                            $errlogs .= "There's a quotation in value of " . $trim_col . ".<br>";
                        } else if (preg_match("/'/", $val) == 1) {
                            $errlogs .= "There's a quotation in value of " . $trim_col . ".<br>";
                        }
                    }

                    if ($errlogs != "") {
                        $params['type'] = 'format';
                        $params['filename'] = $filename;
                        $params['logs'] = $errlogs;
                        $params['error_type'] = 1;
                        $arrError[] = $CheckerModel->logs($params);

                    }
                    $tmp = array_map('str_getcsv', file($file));

                    $arrKeys = array_column($tmp, 0);
                    $arrVals = array_column($tmp, 1);
                    $array = array_map(function ($key, $val) {
                        return [$key => $val];
                    }, $arrKeys, $arrVals);
                    $CCCODE = isset($tmp[0][1]) ? trim($tmp[0][1]) : '';
                    ## for null sales
                    if (empty($tmp)) {

                        $params['type'] = 'format';
                        $params['filename'] = $filename;
                        $params['logs'] = 'Null Files';
                        $params['error_type'] = 1;
                        $arrError[] = $CheckerModel->logs($params);

                    } else {
                        
                        ### START DAILY
                        if ($start3 == "EOD") {
                            $error = 0;
                            $filename1 = substr($filename, 0, -4);
                            $merchant_code = substr($filename1, 3, 17);
                            $TRN_DATE = substr($filename1, 20, 6);
                            $m = substr($filename, 20, 2);
                            $d = substr($filename, 22, 2);
                            $y = '20' . substr($filename, 24, 2);
                            $DATE = $y . '-' . $m . '-' . $d;

                            ### start format validate
                            $validate = $CheckerModel->format_validation_daily($tmp, $DATE);
                            if ($validate[0] == true) {
                                $error =1;
                                $logs = $validate[1];
                                $params['type'] = 'format';
                                $params['filename'] = $filename;
                                $params['logs'] = $logs;
                                $params['error_type'] = 1;
                                $arrError[] = $CheckerModel->logs($params);
                            }
                            ### End format validate
                            if($error==0){
                                $daily = $CheckerModel->daily($tmp, $final, $x, $filename);
                               $rr = ((new DailyController)->validateDaily($daily));
                                echo "<pre>";print_r($rr); echo "<pre>"; die;
                            }

                        } else {
                            ### START TRANSACTION
                            $error = 0;
                            $filename1 = substr($filename, 0, -4);
                            $merchant_code = substr($filename1, 0, 17);
                            $TRN_DATE = substr($filename1, 17, 6);
                            $TER_NO = substr($filename1, 23, 3);

                            ### start format validate
                            $validate = $CheckerModel->format_validation_trans($array, $tmp, $filename);

                            if ($validate[0] == true) {
                                $error =1;
                                $logs = $validate[1];
                                $params['error_type'] = 'Format';
                                $params['filename'] = $filename;
                                $params['logs'] = $logs;
                                $arrError[] = $CheckerModel->logs($params);

                            } else {
                                if ($validate[1][0]) {
                                    ## Number of transaction validation
                                    $m = substr($TRN_DATE, 0,2);
                                    $d = substr($TRN_DATE, 2,2);
                                    $y = substr($TRN_DATE, 4,2);
                                    $trndate = '20'.$y.'-'.$m.'-'.$d;
    
                                    $error =1;
                                    $terno = $validate[1][1];
                                    $transno = $validate[1][2];
                                    $logs = 'NO_TRN not equal to total transaction.';
                                    $params['error_type'] = 'Transaction';
                                    $params['filename'] = $filename;
                                    $params['trn_date'] = $trndate;
                                    $params['trn_no'] = $transno;
                                    $params['ter_no'] = $terno;
                                    $params['logs'] = $logs;
                                    $arrError[] = $CheckerModel->logs($params);
                                }
                            }

                            ### end format validate
                            if($error==0){
                                $transaction = $CheckerModel->transaction($tmp, $array, $final, $x, $filename);
                                $result = (new TransactionController)->validateTransaction($transaction);
                                echo "<pre>";print_r($result); echo "<pre>"; die;
                                // SELECT sum(CAST(amount AS DECIMAL(10,2))) FROM tbl1
                            }
                        }
                    }

                    ### END TRANSACTION
                }
            }

            return json_encode($arrError);
        }
    }

    public function thresholdSettings(Request $request){
   
        $id=1;
        $this->validate($request, [
            'value_from' => 'required',
            'value_to' => 'required'
        ]);

        $Threshold = ThresholdSettings::find($id);
        
        $Threshold->value_from = $request->input('value_from');
        $Threshold->value_to = $request->input('value_to');
        $Threshold->save();
        return redirect('/')->with('success', 'Threshold Updated!');;
    }

   


}