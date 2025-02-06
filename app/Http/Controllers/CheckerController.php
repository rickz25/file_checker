<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Checker;
use App\Models\TransactionValidation;
use App\Models\DailyValidation;
use App\Models\CrossValidation;
use App\Models\Logs;
use App\Models\ThresholdSettings;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DailyController;
use App\Models\filename;
use App\Models\Formatter;
use Illuminate\Http\Client\ConnectionException;
use File;
use DateTime;
ini_set('max_file_uploads', '100000');
set_time_limit(0);
ini_set('max_input_time', '30000');

class CheckerController extends Controller
{
    //
    public function index()
    {
        $Threshold = ThresholdSettings::where('id', 1)->first();
        $format = Formatter::where('id', 1)->first();
        return view('index')->with([
            'threshold' => $Threshold,
            'format'=>$format
        ]);

    }
    public function checkFile(Request $request)
    {
        TransactionValidation::truncate();
        DailyValidation::truncate();
        CrossValidation::truncate();
        Logs::truncate();
        filename::truncate();
        $CheckerModel = new Checker;
        $Transaction = new TransactionValidation;
        $Daily = new DailyValidation;
        $CrossValidation = new CrossValidation;
        $request->validate([
            'files' => 'required',
            'files.*' => 'mimes:csv,txt,xlx,xls'
        ]);
        $Threshold = ThresholdSettings::where('id', 1)->first();
        $Logs = new Logs;
        $final = [];
        $logs = '';
        if ($request->TotalFiles > 0) {
            $error_tag=0;
            $totalFileSize = array_sum($_FILES['files']['size']);
            $maxFileSize = 512 * 1024 * 1024; /* 512MB */
            if ($totalFileSize > $maxFileSize) {
                $$error_desc = 'Your files exceed the limit of 512MB capacity.';
                $error_tag=1;
            }
            if($request->TotalFiles >5000){
                $error_desc = 'Your files exceed the limit of 5000 files per upload.';
                $error_tag=1;
            }
            if($error_tag==1){
                $param['type'] = 0;
                $param['error_description'] = $error_desc;
                $Logs->savelogs($param); /**save logs */
    
                $logs = Logs::get();
                $result['status'] = 0;
                $result['message'] = 'Not tally';
                $result['logs'] = $logs;
                return response()->json($result, 200);
            }
            
           
            $arrError = [];
            $files = collect($request->file('files'))->sort()->all();
            foreach ($files as $x => $file) {
                    $filename = $file->getClientOriginalName();
                    $filename1 = substr($filename, 0, -4);
                    $merchant_code = substr($filename1, 0, 17);
                    $TRN_DATE = substr($filename1, 17, 6);
                    $TER_NO = substr($filename1, 23, 3);
                   
                    $start3 = substr($filename, 0, 3);
                    $errlogs = "";
                    $file_content = file_get_contents($file);
                    $tmp = array_map("str_getcsv", preg_split('/\r*\n+|\r+/', $file_content));
                    foreach ($tmp as $arr) {
                        $col =  isset($arr[0]) ? $arr[0] : null;
                        if($col != 'MOBILE_NO' && $col != 'MERCHANT_NAME' && $col != 'ITEMCODE'){
                            $val = isset($arr[1]) ? $arr[1] : null;
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
                    }

                    if ($errlogs != "") {
                        $params['error_type'] = 'Format';
                        $params['filename'] = $filename;
                        $params['logs'] = $errlogs;
                        $params['merchant_code'] = $merchant_code;
                        $CheckerModel->logs($params);
                    }

                    $arrKeys = array_column($tmp, 0);
                    $arrVals = array_column($tmp, 1);
                    $array = array_map(function ($key, $val) {
                        return [$key => $val];
                    }, $arrKeys, $arrVals);
                    $CCCODE = isset($tmp[0][1]) ? trim($tmp[0][1]) : '';

                    ## for null sales
                    if (empty($tmp)) {

                        $params['error_type'] = 'format';
                        $params['filename'] = $filename;
                        $params['merchant_code'] = $merchant_code;
                        $params['logs'] = 'Null Files';
                        $CheckerModel->logs($params);

                    } else {

                        ### START TRANSACTION
                        if ($start3 != "EOD") {
                            ### start format validate
                            $validate = $CheckerModel->format_validation_trans($array, $tmp, $filename);
                        
                            if ($validate[0] == true) {
                                $logs = $validate[1]['logs'];
                                $params['error_type'] = 'Format';
                                $params['filename'] = $filename;
                                $params['merchant_code'] = $merchant_code;
                                $params['logs'] = $logs;
                                $CheckerModel->logs($params);
                            ### end format validate
                            }else{
                                $transaction = $CheckerModel->transaction($tmp, $array, $final, $x, $filename);
                                $res = (new TransactionController)->insertTransaction($transaction);
                                 //  echo "<pre>"; print_r($res); "</pre>";
                            }
                        }else{
                            ### START DAILY
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
                                    $error = 1;
                                    $logs = $validate[1]['logs'];
                                    $params['error_type'] = 'Format';
                                    $params['filename'] = $filename;
                                    $params['logs'] = $logs;
                                    $params['merchant_code'] = $merchant_code;
                                    $CheckerModel->logs($params);
                                ### End format validate
                                }else{
                                    $daily = $CheckerModel->daily($tmp, $final, $x, $filename);
                                    $res = ((new DailyController)->insertDaily($daily));
                                }
                        }
                    }
                // }
            }

            (new TransactionController)->processTransaction();
            (new DailyController)->processDaily();

           
            $logs = Logs::get();
            $result['status'] = 0;
            $result['message'] = 'Not tally';
            $result['logs'] = $logs;
            // return json_encode($result);
            return response()->json($result, 200);
        }
    }

    public function thresholdSettings(Request $request)
    {

        $id = 1;
        $this->validate($request, [
            'value_from' => 'required',
            'value_to' => 'required'
        ]);

        $Threshold = ThresholdSettings::find($id);

        $Threshold->value_from = $request->input('value_from');
        $Threshold->value_to = $request->input('value_to');
        $Threshold->save();
        return redirect('/')->with('success', 'Threshold Updated!');
        ;
    }
    public function formatSettings(Request $request)
    {

        $id = 1;
        $this->validate($request, [
            'merchant_code' => ['required', 'integer', 'max:50', 'min:1']
        ]);
 

        $format = Formatter::find($id);
       
        $format->merchant_code_length = $request->input('merchant_code');
        $format->save();
        return redirect('/')->with('success', 'Format Setting Updated!');
        ;
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