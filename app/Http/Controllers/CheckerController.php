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
            $arrError = [];
            for ($x = 0; $x < $request->TotalFiles; $x++) {
                if ($request->hasFile('files' . $x)) {
                    $file = $request->file('files' . $x);
                    $filename = $file->getClientOriginalName();
                    $filename1 = substr($filename, 0, -4);
                    $merchant_code = substr($filename1, 0, 17);
                    $TRN_DATE = substr($filename1, 17, 6);
                    $TER_NO = substr($filename1, 23, 3);

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
                        $params['error_type'] = 'Format';
                        $params['filename'] = $filename;
                        $params['logs'] = $errlogs;
                        $params['merchant_code'] = $merchant_code;
                        $CheckerModel->logs($params);
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

                        $params['error_type'] = 'format';
                        $params['filename'] = $filename;
                        $params['merchant_code'] = $merchant_code;
                        $params['logs'] = 'Null Files';
                        $CheckerModel->logs($params);

                    } else {

                        if ($start3 != "EOD") {
                            ### START TRANSACTION
                            $error = 0;
                            ### start format validate
                            $validate = $CheckerModel->format_validation_trans($array, $tmp, $filename);

                            if ($validate[0] == true) {
                                $error = 1;
                                $logs = $validate[1];
                                $params['error_type'] = 'Format';
                                $params['filename'] = $filename;
                                $params['merchant_code'] = $merchant_code;
                                $params['logs'] = $logs;
                                $CheckerModel->logs($params);

                            } else {

                                if ($validate[1][0]) {
                                    ## Number of transaction validation
                                    $m = substr($TRN_DATE, 0, 2);
                                    $d = substr($TRN_DATE, 2, 2);
                                    $y = substr($TRN_DATE, 4, 2);
                                    $trndate = '20' . $y . '-' . $m . '-' . $d;

                                    $error = 1;
                                    $terno = $validate[1][1];
                                    $transno = $validate[1][2];
                                    $no_trn = $validate[1][3];
                                    $total_trn = $validate[1][4];
                                    $logs = "NO_TRN ($no_trn) not equal to total transaction ($total_trn).";
                                    $params['error_type'] = 'Transaction';
                                    $params['filename'] = $filename;
                                    $params['merchant_code'] = $merchant_code;
                                    $params['trn_date'] = $trndate;
                                    $params['trn_no'] = $transno;
                                    $params['ter_no'] = $terno;
                                    $params['logs'] = $logs;
                                    $CheckerModel->logs($params);
                                }
                            }

                            ### end format validate
                            if ($error == 0) {
                                $transaction = $CheckerModel->transaction($tmp, $array, $final, $x, $filename);
                                $res = (new TransactionController)->insertTransaction($transaction);
                            }
                        }
                    }
                }
            }

            $queryTrans = $Transaction->validateTransaction();
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
                    $param['type'] = 0;
                    $message = "GROSS discrepancy (" . $this->formatted(abs($q->gross)) . ")";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->payment, $val1, $val2)) {
                    $param['type'] = 0;
                    $message = "PAYMENT discrepancy (" . $this->formatted(abs($q->payment)) . ")";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->card, $val1, $val2)) {
                    $param['type'] = 0;
                    $message = "CARD discrepancy (" . $this->formatted(abs($q->card)) . ")";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->epay, $val1, $val2)) {
                    $param['type'] = 0;
                    $message = "EPAY discrepancy (" . $this->formatted(abs($q->epay)) . ")";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->dcard, $val1, $val2)) {
                    $param['type'] = 0;
                    $message = "DCARD discrepancy (" . $this->formatted(abs($q->dcard)) . ")";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
            }

            ### START DAILY
            for ($x = 0; $x < $request->TotalFiles; $x++) {
                if ($request->hasFile('files' . $x)) {
                    $file = $request->file('files' . $x);
                    $filename = $file->getClientOriginalName();

                    $filename1 = substr($filename, 0, -4);
                    $merchant_code = substr($filename1, 3, 17);
                    $TRN_DATE = substr($filename1, 20, 6);
                    $m = substr($filename, 20, 2);
                    $d = substr($filename, 22, 2);
                    $y = '20' . substr($filename, 24, 2);
                    $DATE = $y . '-' . $m . '-' . $d;

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
                        $params['error_type'] = 'Format';
                        $params['filename'] = $filename;
                        $params['merchant_code'] = $merchant_code;
                        $params['logs'] = $errlogs;
                        $CheckerModel->logs($params);
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

                        $params['error_type'] = 'Format';
                        $params['filename'] = $filename;
                        $params['merchant_code'] = $merchant_code;
                        $params['logs'] = 'Null Files';
                        $CheckerModel->logs($params);

                    } else {

                        ### START DAILY
                        if ($start3 == "EOD") {
                            $error = 0;

                            ### start format validate
                            $validate = $CheckerModel->format_validation_daily($tmp, $DATE);
                            if ($validate[0] == true) {
                                $error = 1;
                                $logs = $validate[1];
                                $params['error_type'] = 'Format';
                                $params['filename'] = $filename;
                                $params['logs'] = $logs;
                                $params['merchant_code'] = $merchant_code;
                                $CheckerModel->logs($params);
                            }
                            ### End format validate
                            if ($error == 0) {
                                $daily = $CheckerModel->daily($tmp, $final, $x, $filename);
                                $res = ((new DailyController)->insertDaily($daily));

                            }
                        }
                    }
                }
            }
            $queryDaily = $Daily->validateDaily();
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
                    $error = 1;
                    $param['type'] = 0;
                    $message = "GROSS discrepancy (" . $this->formatted(abs($q->gross)) . ")";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->discount, $val1, $val2)) {
                    $error = 1;
                    $param['type'] = 0;
                    $message = "DISCOUNT discrepancy (" . $this->formatted(abs($q->discount)) . ")";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->no_disc, $val1, $val2)) {
                    $error = 1;
                    $param['type'] = 0;
                    $message = "NO_DISC discrepancy (" . $this->formatted(abs($q->no_disc)) . ")";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->card, $val1, $val2)) {
                    $error = 1;
                    $param['type'] = 0;
                    $message = "CARD discrepancy (" . $this->formatted(abs($q->card)) . ")";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->epay, $val1, $val2)) {
                    $error = 1;
                    $param['type'] = 0;
                    $message = "EPAY discrepancy (" . $this->formatted(abs($q->epay)) . ")";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->dcard, $val1, $val2)) {
                    $error = 1;
                    $param['type'] = 0;
                    $message = "DCARD discrepancy (" . $this->formatted(abs($q->dcard)) . ")";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->new_grand_total, $val1, $val2)) {
                    $error = 1;
                    $param['type'] = 0;
                    $message = "NEW GRAND TOTAL discrepancy (" . $this->formatted(abs($q->new_grand_total)) . ")";
                    $param['error_description'] = $message;
                    $Logs->savelogs($param); /**save logs */
                }
                if (!$this->in_range($q->netsales, $val1, $val2)) {
                    $error = 1;
                    $param['type'] = 0;
                    $message = "NETSALES discrepancy (" . $this->formatted(abs($q->netsales)) . ")";
                    $param['error_description'] = $message;
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
                        // echo "<pre>"; print_r($cross); "</pre>"; die;

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
            $logs = Logs::get();
            $result['status'] = 0;
            $result['message'] = 'Not tally';
            $result['logs'] = $logs;
            return json_encode($result);
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