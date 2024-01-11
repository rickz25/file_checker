<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Logs;
use App\Models\Formatter;
use DateTime;

class Checker extends Model
{
    use HasFactory;

    public function logs($params)
    {
        $Logs = new Logs;
        if($params['error_type']=='Format'){

            $param['type'] = 0;
            $param['error_type'] = $params['error_type'];
            $param['filename'] = $params['filename'];
            $param['merchant_code'] =null;
            $param['transaction_date'] = null;
            $param['transaction_no'] = null;
            $param['terminal_no'] = null;
            $param['error_description'] = $params['logs'];
            $Logs->savelogs($param); /**save logs */

        }else{
            $param['type'] = 0;
            $param['error_type'] = $params['error_type'];
            $param['filename'] = $params['filename'];
            $param['merchant_code'] = $params['merchant_code'];
            $param['transaction_date'] =$params['trn_date'];
            $param['transaction_no'] =$params['trn_no'];
            $param['terminal_no'] = $params['ter_no'];
            $param['error_description'] = $params['logs'];
            $Logs->savelogs($param); /**save logs */
        }
    }

    public function transaction($tmp, $array, $final, $fi, $filename)
    {
        $final['filename'] = $filename;
        $final['filedata']['CCCODE'] = trim($tmp[0][1]);
        $final['filedata']['MERCHANT_NAME'] = trim($tmp[1][1]);
        $final['filedata']['TRN_DATE'] = $tmp[2][1];
        $final['filedata']['NO_TRN'] = $tmp[3][1];

        $transactions = [];
        $index = 4; //Start in CDATE
        $arrayLength = count($array) - 1;
        $length = true;
        while ($length != false) {
            $count = 0;
            $tempArr = [];
            $subArr = [];
            if ($index == $arrayLength + 1) {
                break;
            }
            $key = array_keys($array[$index])[0];

            if ($key == "CDATE") {
                $length = true;
                $index++;
                $tempArr[$key] = $array[$index - 1][$key];
            }
            while ($index <= $arrayLength && array_keys($array[$index])[0] != "CDATE") {
                $key = array_keys($array[$index])[0];
                if ($key == "QTY" || $key == "ITEMCODE" || $key == "PRICE" || $key == "LDISC") {
                    $subArr[$key] = mb_substr($array[$index][$key], 0, 10, 'utf-8');
                } else {
                    $tempArr[$key] = $array[$index][$key];
                }
                if (count($subArr) == 4) {
                    $tempArr["ITEMS"][$count] = $subArr;
                    $count++;
                    $subArr = [];
                }
                $index++;
            }
            $transactions[] = $tempArr;
        }
        $final['filedata']['TRANSACTION'] = $transactions;

        if (isset($final[0])) {
            return json_encode($final[0]);
        } else {
            $return = json_encode($final);
            return trim($return, '[]');
        }
    }

    ### DAILY / EOD
    public function daily($tmp, $final, $fi, $filename)
    {
    
        $final['filename'] = $filename;
        $final['filedata']['CCCODE'] = trim($tmp[0][1]);
        $final['filedata']['MERCHANT_NAME'] = trim($tmp[1][1]);
        $daily = [];
        $terminals = [];
        if (isset($tmp)) {
            $keys = array_column($tmp, 0);
            foreach ($tmp[0] as $ke => $val) {
                if ($ke == 0) {
                    continue;
                }
                $values = array_column($tmp, $ke);
                $array = array_map(function ($key, $val) {
                    return [$key => $val];
                }, $keys, $values);
                $array = array_slice($array, 2);

                foreach ($array as &$val) {
                    foreach ($val as $k => $v) {
                        if ($k != "") {
                            $daily[$k] = $v;
                        }
                    }
                }
                $terminals[] = $daily;
            }
        }
        $final['filedata']['TERMINALS'] = $terminals;
        $return = json_encode($final);
        return trim($return, '[]');
    }

    ### format validation
    public function format_validation_trans($array, $tmp, $filename)
    {
        $filename1 = substr($filename, 0, -4);
        $merchant_code = substr($filename1, 0, 17);
        $TER_NO = substr($filename1, 23, 3);
        $m = substr($filename, 17, 2);
        $d = substr($filename, 19, 2);
        $y = substr($filename, 21, 2);
        $TRN_DATE = '20' . $y . '-' . $m . '-' . $d;
        $main_message = "";
        ## header transacation validation
        $header_format = config('transaction_format.header');
        for ($i = 0; $i < count($header_format); $i++) {
            ## double and float datatype
            if ($header_format[$i][1] == "d" || $header_format[$i][1] == "f") {
                $num = $tmp[$i][1];
                if (is_numeric($num)) {
                    if (!$this->check_decimal($num)) {
                        $main_message .= "Wrong decimal " . $tmp[$i][0] . ". <br>";
                    }
                } else {
                    $main_message .= "Wrong datatype " . $tmp[$i][0] . ". <br>";
                }
                if ($this->check_space($num)) {
                    $main_message .= "Wrong datatype, there has a space " . $tmp[$i][0] . ". <br>";
                }
            }
            ##
            ## string datatype
            if ($header_format[$i][1] == "s") {
                $data = $tmp[$i][1];

                if ($header_format[$i][0] != "MERCHANT_NAME") {
                    if ($this->check_space($data)) {
                        $main_message .= "Wrong datatype, there has a space " . $tmp[$i][0] . ". <br>";
                    }
                    if ($this->check_quotation($data)) {
                        $main_message .= "Wrong datatype, there has a quotation " . $tmp[$i][0] . ". <br>";
                    }
                    if ($this->check_string($data)) {
                        $main_message .= "Wrong datatype " . $tmp[$i][0] . ". <br>";
                    }
                }
                
            }
            if ($header_format[$i][0] == "TRN_DATE") {
                if ($tmp[$i][1] != $TRN_DATE) {
                    $main_message .= "TRN_DATE in filename not equal to " . $header_format[$i][0] . " inside the file. <br>";
                }
            }
            ##
            ## integer or numeric
            if ($header_format[$i][1] == "i") {
                $num = $tmp[$i][1];
                if (!ctype_digit($num)) {
                    $main_message .= "Wrong datatype " . $tmp[$i][0] . ". <br>";
                }
                if ($this->check_space($num)) {
                    $main_message .= "Wrong datatype, there has a space " . $tmp[$i][0] . ". <br>";
                }
            }
            ##
            if ($header_format[$i][0] == "CCCODE") {
                $str1 = preg_replace("/[^a-zA-Z0-9]+/", "", $tmp[$i][0]);
                $str2 = trim($header_format[$i][0]);
                if ($str1 != $str2) {
                    $main_message .= "Incorrect Format Column " . $tmp[$i][0] . " instead of " . $header_format[$i][0] . ". <br>";
                }
            } else {
                if ($header_format[$i][0] != $tmp[$i][0]) {
                    $main_message .= "Incorrect Format Column " . $tmp[$i][0] . " instead of " . $header_format[$i][0] . ". <br>";
                }
            }
            if ($tmp[$i][1] == "") {
                $main_message .= "Empty Column " . $tmp[$i][0] . ". <br>";
            }
            $format = Formatter::where('id', 1)->first();
            if(strlen(trim($tmp[$i][1])) > $format->merchant_code_length){
                $main_message .= "Incorrect Format CCCODE Length (" . strlen(trim($tmp[$i][1])) . ") instead of (" . $format->merchant_code_length . "). <br>";
            }
        }

        if ($main_message != "") {
            return [true, $main_message];
        } else {

            $messages = [];
            $message = "";
            $NO_TRN = (int) $array[3]['NO_TRN'];
            $incrementing = 0;
            $transno = 0;
            $terno = 0;
            $index = 4; //Start in CDATE
            $arrayLength = count($array) - 1;
            $count = 0;
            $transaction = [];
            $items = [];
            $length = true;
            while ($length != false) {
                $trans = [];
                $item = [];
                if ($index == $arrayLength + 1) {
                    break;
                }
                $key = array_keys($array[$index])[0];
                $val = array_values($array[$index])[0];

                if ($key == "CDATE") {
                    $index++;
                    $trans[] = [$key, $val];
                    $length = true;
                    $incrementing++;
                }
                while ($index <= $arrayLength && array_keys($array[$index])[0] != "CDATE") {
                    $key = array_keys($array[$index])[0];
                    $val = array_values($array[$index])[0];
                    if ($key == "QTY" || $key == "ITEMCODE" || $key == "PRICE" || $key == "LDISC") {
                        $item[] = [$key, $val];
                    } else {
                        $trans[] = [$key, $val];
                        if ($key == "TER_NO") {
                            $terno = $val;
                        }
                        if ($key == "TRANSACTION_NO") {
                            $transno = $val;
                        }
                    }
                    $index++;
                }
                $items[] = $item;
                $transaction[] = $trans;
            }
            #total transaction validation
            if ($NO_TRN != $incrementing) {
                $no_trn_validation = [true, $terno, $transno, $NO_TRN, $incrementing];
            } else {
                $no_trn_validation = [false, $terno, $transno, $NO_TRN, $incrementing];
            }
            ## items validation
            $items_format = config('transaction_format.item');
            for ($r = 0; $r < count($items); $r++) {
                $f = 0;
                for ($i = 0; $i < count($items[$r]); $i++) {
                    if ($f == 4) {
                        $f = 0;
                    } //reset after 4 iteration
                    if ($items[$r][$i][0] != $items_format[$f][0]) {
                        $messages[] = "Incorrect Format Column " . $items[$r][$i][0] . " instead of " . $items_format[$f][0] . ". <br>";
                    }
                    if ($items[$r][$i][1] == "" || $items[$r][$i][1] == null) {
                        $messages[] = "Empty Column " . $items_format[$f][0] . ". <br>";
                    }
                    ## double and float datatype
                    if ($items_format[$f][1] == "d" || $items_format[$f][1] == "f") {
                        $num = $items[$r][$i][1];
                        if (is_numeric($num)) {
                            if (!$this->check_decimal($num)) {
                                $messages[] = "Wrong decimal " . $items[$r][$i][0] . ". <br>";
                            }
                        } else {
                            $messages[] = "Wrong datatype " . $items[$r][$i][0] . ". <br>";
                        }
                        if ($this->check_space($num)) {
                            $messages[] = "Wrong datatype, there has a space " . $items[$r][$i][0] . ". <br>";
                        }
                    }
                    ##
                    ## double 3decimal places
                    if ($items_format[$f][1] == "d3") {
                        $num = $items[$r][$i][1];
                        if (is_numeric($num)) {
                            if (!$this->check_3_decimal($num)) {
                                $messages[] = "Wrong decimal " . $items[$r][$i][0] . ". <br>";
                            }
                        } else {
                            $messages[] = "Wrong datatype " . $items[$r][$i][0] . ". <br>";
                        }
                        if ($this->check_space($num)) {
                            $messages[] = "Wrong datatype, there has a space " . $items[$r][$i][0] . ". <br>";
                        }
                    }
                    ##
                    ## string datatype
                    if ($items_format[$f][1] == "s") {
                        $data = $items[$r][$f][1];
                        if ($this->check_string($data)) {
                            $messages[] = "Wrong datatype " . $items[$r][$i][0] . ". <br>";
                        }
                        if ($this->check_space($data)) {
                            $messages[] = "Wrong datatype, there has a space " . $items[$r][$i][0] . ". <br>";
                        }
                        if ($this->check_quotation($data)) {
                            $message .= "Wrong datatype, there has a quotation " . $items[$r][$i][0] . ". <br>";
                        }
                    }
                    ##
                    $f++;
                }
            }

            if (!empty($messages)) {
                foreach (array_unique($messages) as $m) {
                    $message .= $m;
                }
            }
            ## end items

            ## body transaction validation
            $transaction_format = config('transaction_format.transaction');
            $messages = [];
            $TRANS_NO = [];
            for ($r = 0; $r < count($transaction); $r++) {
                for ($i = 0; $i < count($transaction[$r]); $i++) {
                    if (isset($transaction_format[$i][0])) {
                        if ($transaction[$r][$i][0] != $transaction_format[$i][0]) {
                            $messages[] = "Incorrect Format Column " . $transaction[$r][$i][0] . " instead of " . $transaction_format[$i][0] . ". <br>";
                        }
                        if ($transaction[$r][$i][0] != "MOBILE_NO") {
                            if ($transaction[$r][$i][1] == "") {
                                $messages[] = "Empty Column " . $transaction_format[$i][0] . ". <br>";
                            }
                        }
                        if ($transaction[$r][$i][0] == "TER_NO") {
                            if (trim($transaction[$r][$i][1]) !== $TER_NO) {
                                $messages[] = "TER_NO in filename not equal to " . $transaction_format[$i][0] . " inside the file. <br>";
                            }
                        }
                        if ($transaction[$r][$i][0] == "TRANSACTION_NO") {
                            if (strlen(trim($transaction[$r][$i][1])) > 15) {
                                $messages[] = "TRANSACTION_NO should contain maximum of 15 numbers only (" . $transaction[$r][$i][1] . "). <br>";
                            }
                            if ($this->checkColumn($transaction[$r][$i][1], $TRANS_NO)) {
                                $messages[] = "There are same TRANSACTION_NO (" . $transaction[$r][$i][1] . "). <br>";
                            }
                            $TRANS_NO[] = $transaction[$r][$i][1];
                        }
                        ## double and float datatype
                        if ($transaction_format[$i][1] == "d" || $transaction_format[$i][1] == "f") {
                            $num = $transaction[$r][$i][1];
                            if (is_numeric($num)) {
                                if (!$this->check_decimal($num)) {
                                    $messages[] = "Wrong decimal " . $transaction[$r][$i][0] . ". <br>";
                                }
                            } else {
                                $messages[] = "Wrong datatype " . $transaction[$r][$i][0] . ". <br>";
                            }
                            if ($this->check_space($num)) {
                                $messages[] = "Wrong datatype, there has a space " . $transaction[$r][$i][0] . ". <br>";
                            }
                        }
                        ##
                        ## double 3decimal places
                        if ($transaction_format[$i][1] == "d3") {
                            $num = $transaction[$r][$i][1];
                            if (is_numeric($num)) {
                                if (!$this->check_3_decimal($num)) {
                                    $messages[] = "Wrong decimal " . $transaction[$r][$i][0] . ". <br>";
                                }
                            } else {
                                $messages[] = "Wrong datatype " . $transaction[$r][$i][0] . ". <br>";
                            }
                            if ($this->check_space($num)) {
                                $messages[] = "Wrong datatype, there has a space " . $transaction[$r][$i][0] . ". <br>";
                            }
                        }
                        ##
                        ## string datatype
                        if ($transaction_format[$i][1] == "s") {
                            $data = $transaction[$r][$i][1];
                            if ($this->check_string($data)) {
                                $messages[] = "Wrong datatype " . $transaction[$r][$i][0] . ". <br>";
                            }
                            if ($this->check_space($data)) {
                                $messages[] = "Wrong datatype, there has a space " . $transaction[$r][$i][0] . ". <br>";
                            }
                            if ($this->check_quotation($data)) {
                                $message .= "Wrong datatype, there has a quotation " . $transaction[$r][$i][0] . ". <br>";
                            }
                        }
                        ##
                        ## integer or numeric
                        if ($transaction_format[$i][1] == "i") {
                            $num = $transaction[$r][$i][1];
                            if (!ctype_digit($num)) {
                                $messages[] = "Wrong datatype " . $transaction[$r][$i][0] . ". <br>";
                            }
                            if ($this->check_space($num)) {
                                $messages[] = "Wrong datatype, there has a space " . $transaction[$r][$i][0] . ". <br>";
                            }
                        }
                        ##
                    } else {
                        $messages[] = "Out of format file " . $transaction[$r][$i][0] . ". <br>";
                    }
                }
            }
            if (!empty($messages)) {
                foreach (array_unique($messages) as $m) {
                    $message .= $m;
                }
            }
            ## end
            return ($message != "") ? [true, $message] : [false, $no_trn_validation];
        }
    }

    ### format validation fo daily
    public function format_validation_daily($tmp, $TRN_DATE)
    {
        

        $message = "";
        $daily_format = config('daily_format');
        $messages = [];
        if (isset($tmp)) {
            for ($i = 0; $i < count($daily_format); $i++) {
                for ($r = 0; $r < count($tmp[0]); $r++) {
                    if ($r == 0) {
                        continue;
                    }
                    if (!isset($tmp[$i][$r])) {
                        $messages[] = "Empty in Column " . $daily_format[$i][0] . ". <br>";
                    } else {
                        ## double and float datatype
                        if ($daily_format[$i][1] == "d" || $daily_format[$i][1] == "f") {
                            $num = $tmp[$i][$r];
                            if (is_numeric($num)) {
                                if (!$this->check_decimal($num)) {
                                    $messages[] = "Wrong decimal " . $daily_format[$i][0] . ". <br>";
                                }
                            } else {
                                $messages[] = "Wrong datatype " . $daily_format[$i][0] . ". <br>";
                            }
                            if ($this->check_space($num)) {
                                $messages[] = "Wrong datatype, there has a space " . $daily_format[$i][0] . ". <br>";
                            }
                        }
                        ##
                        
                        ## CCCODE validation
                        if ($daily_format[$i][0] == "CCCODE") {
                            $format = Formatter::where('id', 1)->first();
                            if(strlen(trim($tmp[$i][$r])) > $format->merchant_code_length){
                                $message .= "Incorrect Format CCCODE Length (" . strlen(trim($tmp[$i][$r])) . ") instead of (" . $format->merchant_code_length . "). <br>";
                            }
                        }
                        ##

                        ## string datatype
                        if ($daily_format[$i][1] == "s") {
                            $data = $tmp[$i][$r];
                           
                            if ($daily_format[$i][0] != "MERCHANT_NAME") {
                                if ($this->check_space($data)) {
                                    $messages[] = "Wrong datatype, there has a space " . $daily_format[$i][0] . ". <br>";
                                }
                                if ($this->check_quotation($data)) {
                                    $message .= "Wrong datatype, there has a quotation " . $daily_format[$i][0] . ". <br>";
                                }
                                if ($this->check_string($data)) {
                                    $messages[] = "Wrong datatype " . $daily_format[$i][0] . ". <br>";
                                }
                            }
                        }
                        if ($daily_format[$i][0] == "TRN_DATE") {
                            if ($tmp[$i][$r] != $TRN_DATE) {
                                $message .= "TRN_DATE in filename not equal to " . $daily_format[$i][0] . " inside the file. <br>";
                            }
                        }
                        if ($daily_format[$i][0] == "STRANS" || $daily_format[$i][0] == "ETRANS") {
                            if (strlen(trim($tmp[$i][$r])) > 15) {
                                $messages[] = $daily_format[$i][0] . " should contain maximum of 15 numbers only (" . $tmp[$i][$r] . "). <br>";
                            }
                        }
                        ##

                        ## integer or numeric
                        if ($daily_format[$i][1] == "i") {
                            $num = $tmp[$i][$r];
                            if (!ctype_digit($num)) {
                                $messages[] = "Wrong datatype " . $tmp[$i][0] . ". <br>";
                            }
                            if ($this->check_space($num)) {
                                $messages[] = "Wrong datatype, there has a space " . $tmp[$i][0] . ". <br>";
                            }
                        }
                        ##
                        if ($daily_format[$i][0] == "CCCODE") {
                            $str1 = preg_replace("/[^a-zA-Z0-9]+/", "", $tmp[$i][0]);
                            $str2 = trim($daily_format[$i][0]);
                            if ($str1 != $str2) {
                                $message .= "Incorrect Format Column " . $tmp[$i][0] . " instead of " . $daily_format[$i][0] . ". <br>";
                            }
                        } else {
                            if ($tmp[$i][0] != $daily_format[$i][0]) {
                                $messages[] = "Incorrect Format Column " . $tmp[$i][0] . " instead of " . $daily_format[$i][0] . ". <br>";
                            }
                        }
                    }
                }
            }
            if (!empty($messages)) {
                foreach (array_unique($messages) as $m) {
                    $message .= $m;
                }
            }
            return ($message != "") ? [true, $message] : [false];
        }
    }
    ### End

    ### check decimal 2 places
    public function check_decimal($num)
    {
        $number = ltrim("$num", '-');
        return (preg_match('/^[0-9]+\.[0-9]{2}$/', $number)) ? true : false;
    }
    ### check decimal 2 places
    public function check_3_decimal($num)
    {
        $number = ltrim("$num", '-');
        return (preg_match('/^[0-9]+\.[0-9]{3}$/', $number)) ? true : false;
    }
    ### check if has special character
    public function check_string($data)
    {
        return (preg_match("/([%\$#\*]+)/", $data)) ? true : false;
    }
    ### check if has space
    public function check_space($data)
    {
        return $data != trim($data) ? true : false;
    }
    ### check if has quotation
    public function check_quotation($data)
    {
        return (strpos($data, '"') !== false) ? true : false;
    }
    ### validation of number
    function get_numerics($str)
    {
        preg_match_all('/\d+/', $str, $matches);
        return $matches[0];
    }
    ### validation of date
    function isValidDate(string $date, string $format = 'Y-m-d'): bool
    {
        $dateObj = DateTime::createFromFormat($format, $date);
        return $dateObj && $dateObj->format($format) == $date;
    }
    ### search string to array
    function checkColumn($formatColumn, $arrColumn)
    {
        return array_search(trim($formatColumn), $arrColumn) !== false ? true : false;
    }
}
