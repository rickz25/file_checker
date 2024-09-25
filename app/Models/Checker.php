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
        $final['filedata']['MERCHANT_NAME'] = trim(iconv(mb_detect_encoding($tmp[1][1], mb_detect_order(), true), "UTF-8", $tmp[1][1]));
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
                    $val = mb_substr($array[$index][$key], 0, 10, 'utf-8');
                    $subArr[$key] = iconv(mb_detect_encoding($val, mb_detect_order(), true), "UTF-8", $val);
                } else {
                    if($key!="" || $key!=null){
                        $tempArr[$key] = $array[$index][$key];
                    }
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
            return json_encode($final[0], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        } else {
            $return = json_encode($final, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
            return trim($return, '[]');
        }
    }

    ### DAILY / EOD
    public function daily($tmp, $final, $fi, $filename)
    {
    
        $final['filename'] = $filename;
        $final['filedata']['CCCODE'] = trim($tmp[0][1]);
        $final['filedata']['MERCHANT_NAME'] = trim(iconv(mb_detect_encoding($tmp[1][1], mb_detect_order(), true), "UTF-8", $tmp[1][1]));
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

    public function format_validation_trans($array, $tmp, $filename)
    {
        $filename1 = substr($filename, 0, -4);
        $merchant_code = substr($filename1, 0, 17);
        $TER_NO = substr($filename1, 23, 3);
        $m = substr($filename, 17, 2);
        $d = substr($filename, 19, 2);
        $y = substr($filename, 21, 2);
        $TRN_DATE = '20' . $y . '-' . $m . '-' . $d;
        $main_message = [];
        $messages = [];
        ## header transacation validation
        $header_format = config('transaction_format.header');

        for ($i = 0; $i < count($header_format); $i++) {
            $field = $header_format[$i][0];
            $datatype_header = $header_format[$i][1];
            $length = $header_format[$i][2];

            $field_in_file = $tmp[$i][0];
            $value = $tmp[$i][1];

            if ($value == "") {
                $messages[] = "Empty Column " . $field_in_file . ". \r\n";
            }
            ## double and float datatype
            if ($datatype_header == "d" || $datatype_header == "f") {
                if (is_numeric($value)) {
                    if (!$this->check_decimal($value)) {
                        $messages[] = "Wrong decimal " . $field . ". \r\n";
                    }
                } else {
                    $messages[] = "Wrong datatype " . $field . ". \r\n";
                }
                if ($this->check_space($value)) {
                    $messages[] = "Wrong datatype, there has a space " . $field . ". \r\n";
                }
            }

            ##
            ## string datatype
            if ($datatype_header == "s") {
                $data = iconv(mb_detect_encoding($value, mb_detect_order(), true), "UTF-8", $value);
                if ($field != "MERCHANT_NAME") {
                    if ($this->check_space($data)) {
                        $messages[] = "Wrong datatype, there has a space " . $field_in_file . ". \r\n";
                    }
                    if ($this->check_quotation($data)) {
                        $messages[] = "Wrong datatype, there has a quotation " . $field_in_file . ". \r\n";
                    }
                    if ($this->check_string($data)) {
                        $messages[] = "Wrong datatype " . $field_in_file . ". \r\n";
                    }
                }
            }
            if ($field == "TRN_DATE") {
                if ($value != $TRN_DATE) {
                    $messages[] = "TRN_DATE in filename not equal to " . $field . " inside the file. \r\n";
                }
            }
            ##
            ## integer or numeric
            if ($datatype_header == "i") {
                $num = $value;
                if (!ctype_digit($num)) {
                    $messages[] = "Wrong datatype " . $field_in_file . ". \r\n";
                }
                if ($this->check_space($num)) {
                    $messages[] = "Wrong datatype, there has a space " . $field_in_file . ". \r\n";
                }
            }
            ##
            if ($field == "CCCODE") {
                $str1 = preg_replace("/[^a-zA-Z0-9]+/", "", $field_in_file);
                $str2 = trim($field);
                if ($str1 != $str2) {
                    $messages[] = "Incorrect Format Column " . $field_in_file . " instead of " . $field . ". \r\n";
                }
            } else {
                if ($field != $field_in_file) {
                    $messages[] = "Incorrect Format Column " . $field_in_file . " instead of " . $field . ". \r\n";
                }
            }
            
            ## Check Length
            if ($datatype_header == "d") {
                if($this->checkLength($value, $length)){
                    $messages[] = $field." digit exceeded, maximum allowed digit is ".($length-3)." with 2 decimal place". ". \r\n";
                }
            }else if($datatype_header == "d3"){
                if($this->checkLength($value, $length)){
                    $messages[] = $field." digit exceeded, maximum allowed digit is ".($length-4)." with 3 decimal place ". ". \r\n";
                }
            }else if($datatype_header == "i"){
                if($this->checkLength($value, $length)){
                    $messages[] = $field." digit exceeded, maximum allowed digit is ".$length. ". \r\n";
                }
            }else{
                if($this->checkLength($value, $length)){
                    $messages[] = $field." length exceeded, maximum allowed length is ".$length .". \r\n";
                }
            }
            ##
        }
 
            $param=[];
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
                $val = iconv(mb_detect_encoding(array_values($array[$index])[0], mb_detect_order(), true), "UTF-8", array_values($array[$index])[0]);

                if ($key == "CDATE") {
                    $index++;
                    $trans[] = [$key, $val];
                    $length = true;
                    $incrementing++;
                }
                while ($index <= $arrayLength && array_keys($array[$index])[0] != "CDATE") {
                    $key = array_keys($array[$index])[0];
                    $val = iconv(mb_detect_encoding(array_values($array[$index])[0], mb_detect_order(), true), "UTF-8", array_values($array[$index])[0]);
                    if ($key == "QTY" || $key == "ITEMCODE" || $key == "PRICE" || $key == "LDISC") {
                        $item[] = [$key, $val];
                    } else {
                        if($key!="" || $key!=null){
                            $trans[] = [$key, $val];
                        }
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
                // $no_trn_validation = [true, $terno, $transno];
                $messages[] ="NO_TRN not equal to total transaction. TRANSACTION NO ( $transno ), TERMINAL NO ( $terno ). \r\n";
            }
            // } else {
            //     $no_trn_validation = [false, $terno, $transno];
            // }

            ## items validation
            $items_format = config('transaction_format.item');
            for ($r = 0; $r < count($items); $r++) {
                $f = 0;
                for ($i = 0; $i < count($items[$r]); $i++) {
                    if ($f == 4) {
                        $f = 0;
                    } //reset after 4 iteration
                    $item_datatype = $items_format[$f][1];
                    $item_field = $items_format[$f][0];
                    $item_length = $items_format[$f][2];
                    $item_field_in_file = $items[$r][$i][0];
                    $item_value = $items[$r][$i][1];

                    if ($item_field_in_file != $item_field) {
                        $messages[] = "Incorrect Format Column " . $item_field_in_file . " instead of " . $item_field . ". \r\n";
                    }
                    if ($item_value == "" || $item_value == null) {
                        $messages[] = "Empty Column " . $item_field . ". \r\n";
                    }
                    
                    // ## double and float datatype
                    // if ($item_datatype == "d" || $item_datatype == "f") {
                    //     $num = $item_value;
                    //     if (is_numeric($num)) {
                    //         if (!$this->check_decimal($num)) {
                    //             $messages[] = "Wrong decimal " . $item_field_in_file . ". \r\n";
                    //         }
                    //     } else {
                    //         $messages[] = "Wrong datatype " . $item_field_in_file . ". \r\n";
                    //     }
                    //     if ($this->check_space($num)) {
                    //         $messages[] = "Wrong datatype, there has a space " . $item_field_in_file . ". \r\n";
                    //     }
                    // }
                    // ##
                    // ## double 3decimal places
                    // if ($item_datatype == "d3") {
                    //     $num = $item_value;
                    //     if (is_numeric($num)) {
                    //         if (!$this->check_3_decimal($num)) {
                    //             $messages[] = "Wrong decimal " . $item_field_in_file . ". \r\n";
                    //         }
                    //     } else {
                    //         $messages[] = "Wrong datatype " . $item_field_in_file . ". \r\n";
                    //     }
                    //     if ($this->check_space($num)) {
                    //         $messages[] = "Wrong datatype, there has a space " . $item_field_in_file . ". \r\n";
                    //     }
                    // }
                    // ##
                    // ## string datatype
                    // if ($item_datatype == "s") {
                    //     $data = iconv(mb_detect_encoding($items[$r][$f][1], mb_detect_order(), true), "UTF-8", $items[$r][$f][1]);
                    //     if($item_field !='ITEMCODE'){
                    //         if ($this->check_space($data)) {
                    //             $messages[] = "Wrong datatype, there has a space " . $item_field_in_file . ". <br>";
                    //         }
                    //         if ($this->check_string($data)) {
                    //             $messages[] = "Wrong datatype " . $item_field_in_file . ". \r\n";
                    //         }
                    //         if ($this->check_quotation($data)) {
                    //             $messages[] = "Wrong datatype, there has a quotation " . $item_field_in_file . ". \r\n";
                    //         }
                    //     }
                    // }
                    // ##
                    // ## Check Length
                    // if ($item_datatype == "d") {
                    //     if($this->checkLength($item_value, $item_length)){
                    //         $messages[] = $item_field." digit exceeded, maximum allowed digit is ".($item_length-3)." with 2 decimal place". ". \r\n";
                    //     }
                    // }else if($item_datatype == "d3"){
                    //     if($this->checkLength($item_value, $item_length)){
                    //         $messages[] = $item_field." digit exceeded, maximum allowed digit is ".($item_length-4)." with 3 decimal place ". ". \r\n";
                    //     }
                    // }else if($item_datatype == "i"){
                    //     if($this->checkLength($item_value, $item_length)){
                    //         $messages[] = $item_field." digit exceeded, maximum allowed digit is ".$item_length. ". \r\n";
                    //     }
                    // }else{
                    //     if($this->checkLength($item_value, $item_length)){
                    //         $messages[] = $item_field." length exceeded, maximum allowed length is ".$item_length .". \r\n";
                    //     }
                    // }
                    // ##
                    $f++;
                }
            }

            ## end items

            
            ## body transaction validation
            $transaction_format = config('transaction_format.transaction');
            $message="";
            $TRANS_NO = [];
            for ($r = 0; $r < count($transaction); $r++) {
                for ($i = 0; $i < count($transaction[$r]); $i++) {

                    $transaction_field = $transaction_format[$i][0];
                    $transaction_datatype = $transaction_format[$i][1];
                    $transaction_length = $transaction_format[$i][2];
                    $transaction_field_in_file = $transaction[$r][$i][0];
                    $transaction_value = $transaction[$r][$i][1];

                    if (isset($transaction_field)) {
                        if ($transaction_field_in_file != $transaction_field) {
                            $messages[] = "Incorrect Format Column " . $transaction_field_in_file . " instead of " . $transaction_field . ". \r\n";
                        }
                        if ($transaction_field_in_file != "MOBILE_NO") {
                            if ($transaction_value == "") {
                                $messages[] = "Empty Column " . $transaction_field . ". \r\n";
                            }
                        }
                        if ($transaction_field_in_file == "TER_NO") {
                            if (trim($transaction_value) !== $TER_NO) {
                                $messages[] = "TER_NO in filename not equal to " . $transaction_field . " inside the file. \r\n";
                            }
                        }
                        if ($transaction_field_in_file == "TRANSACTION_NO") {
                            if (strlen(trim($transaction_value)) > 15) {
                                $messages[] = "TRANSACTION_NO should contain maximum of 15 numbers only (" . $transaction_value . "). \r\n";
                            }
                            if ($this->checkColumn($transaction_value, $TRANS_NO)) {
                                $messages[] = "There are same TRANSACTION_NO (" . $transaction_value . "). \r\n";
                            }
                            $TRANS_NO[] = $transaction_value;
                        }
                        if ($transaction_field_in_file == "TRN_TYPE") {
                            if (strlen(trim($transaction_value)) > 1) {
                                $messages[] = "TRN_TYPE should contain maximum of 1 Character only (" . $transaction_value . "). <br>";
                            }
                        }
                        ## double and float datatype
                        if ($transaction_datatype == "d" || $transaction_datatype == "f") {
                            $num = $transaction_value;
                            if (is_numeric($num)) {
                                if (!$this->check_decimal($num)) {
                                    $messages[] = "Wrong decimal " . $transaction_field_in_file . ". \r\n";
                                }
                            } else {
                                $messages[] = "Wrong datatype " . $transaction_field_in_file . ". \r\n";
                            }
                            if ($this->check_space($num)) {
                                $messages[] = "Wrong datatype, there has a space " . $transaction_field_in_file . ". \r\n";
                            }
                        }
                        ##
                        ## double 3decimal places
                        if ($transaction_datatype == "d3") {
                            $num = $transaction_value;
                            if (is_numeric($num)) {
                                if (!$this->check_3_decimal($num)) {
                                    $messages[] = "Wrong decimal " . $transaction_field_in_file . ". \r\n";
                                }
                            } else {
                                $messages[] = "Wrong datatype " . $transaction_field_in_file . ". \r\n";
                            }
                            if ($this->check_space($num)) {
                                $messages[] = "Wrong datatype, there has a space " . $transaction_field_in_file . ". \r\n";
                            }
                        }
                        ##
                        ## string datatype
                        if ($transaction_datatype == "s") {
                            $data = iconv(mb_detect_encoding($transaction_value, mb_detect_order(), true), "UTF-8", $transaction_value);
                            if ($this->check_string($data)) {
                                $messages[] = "Wrong datatype " . $transaction_field_in_file . ". \r\n";
                            }
                            if ($this->check_space($data)) {
                                $messages[] = "Wrong datatype, there has a space " . $transaction_field_in_file . ". \r\n";
                            }
                            if ($this->check_quotation($data)) {
                                $messages[] = "Wrong datatype, there has a quotation " . $transaction_field_in_file . ". \r\n";
                            }
                        }
                        ##
                        ## integer or numeric
                        if ($transaction_datatype == "i") {
                            $num = $transaction_value;
                            if (!ctype_digit($num)) {
                                $messages[] = "Wrong datatype " . $transaction_field_in_file . ". \r\n";
                            }
                            if ($this->check_space($num)) {
                                $messages[] = "Wrong datatype, there has a space " . $transaction_field_in_file . ". \r\n";
                            }
                        }
                        ##
                        ## Check Length
                        if ($transaction_datatype == "d") {
                            if($this->checkLength($transaction_value, $transaction_length)){
                                $messages[] = $transaction_field." digit exceeded, maximum allowed digit is ".($transaction_length-3)." with 2 decimal place". ". \r\n";
                            }
                        }else if($transaction_datatype == "d3"){
                            if($this->checkLength($transaction_value, $transaction_length)){
                                $messages[] = $transaction_field." digit exceeded, maximum allowed digit is ".($transaction_length-4)." with 3 decimal place ". ". \r\n";
                            }
                        }else if($transaction_datatype == "i"){
                            if($this->checkLength($transaction_value, $transaction_length)){
                                $messages[] = $transaction_field." digit exceeded, maximum allowed digit is ".$transaction_length. ". \r\n";
                            }
                        }else{
                            if($this->checkLength($transaction_value, $transaction_length)){
                                $messages[] = $transaction_field." length exceeded, maximum allowed length is ".$transaction_length .". \r\n";
                            }
                        }
                        ##
                    } else {
                        $messages[] = "Out of format file " . $transaction_field_in_file . ". \r\n";
                    }
                }
            }
            if (!empty($messages)) {
                foreach (array_unique($messages) as $m) {
                    $message .= $m;
                }
            }
            ## end
            $param['logs']=$message;
            $param['terno']=$terno;
            $param['transno']=$transno;
            return ($message != "") ? [true, $param] : [false];
    }


    ### format validation fo daily
    public function format_validation_daily($tmp, $TRN_DATE)
    {
        $message = "";
        $terno="";
        $no_trn="";
        $daily_format = config('daily_format');
        $messages = [];
        if (isset($tmp)) {
            for ($i = 0; $i < count($daily_format); $i++) {
                for ($r = 0; $r < count($tmp[0]); $r++) {
                    if ($r == 0) {
                        continue;
                    }

                    $daily_field = $daily_format[$i][0];
                    $daily_datatype = $daily_format[$i][1];
                    $daily_length = $daily_format[$i][2];
                    $daily_value = $tmp[$i][$r];
                    $daily_field_file = $tmp[$i][0];

                    if($daily_field_file=='NO_TRN'){
                        $no_trn = $daily_value;
                    }
                    if($daily_field_file=='TER_NO'){
                        $terno = $daily_value;
                    }

                    if (!isset($daily_value)) {
                        $messages[] = "Empty in Column " . $daily_field . ". \r\n";
                    } else {
                        ## double and float datatype
                        if ($daily_datatype == "d" || $daily_datatype == "f") {
                            $num = $daily_value;
                            if (is_numeric($num)) {
                                if (!$this->check_decimal($num)) {
                                    $messages[] = "Wrong decimal " . $daily_field . ". \r\n";
                                }
                            } else {
                                $messages[] = "Wrong datatype " . $daily_field . ". \r\n";
                            }
                            if ($this->check_space($num)) {
                                $messages[] = "Wrong datatype, there has a space " . $daily_field . ". \r\n";
                            }
                        }
                        ##

                        ## string datatype
                        if ($daily_datatype == "s") {
                            $data = iconv(mb_detect_encoding($daily_value, mb_detect_order(), true), "UTF-8", $daily_value);
                           
                            if ($daily_field != "MERCHANT_NAME") {
                                if ($this->check_space($data)) {
                                    $messages[] = "Wrong datatype, there has a space " . $daily_field . ". \r\n";
                                }
                                if ($this->check_quotation($data)) {
                                    $messages[] = "Wrong datatype, there has a quotation " . $daily_field . ". \r\n";
                                }
                                if ($this->check_string($data)) {
                                    $messages[] = "Wrong datatype " . $daily_field . ". \r\n";
                                }
                            }
                        }
                        if ($daily_field == "TRN_DATE") {
                            if ($daily_value != $TRN_DATE) {
                                $messages[] = "TRN_DATE in filename not equal to " . $daily_field . " inside the file. \r\n";
                            }
                        }
                        if ($daily_field == "STRANS" || $daily_field == "ETRANS") {
                            if (strlen(trim($daily_value)) > 15) {
                                $messages[] = $daily_field . " should contain maximum of 15 numbers only (" . $daily_value . "). \r\n";
                            }
                        }
                        ##

                        ## integer or numeric
                        if ($daily_datatype == "i") {
                            $num = $daily_value;
                            if (!ctype_digit($num)) {
                                $messages[] = "Wrong datatype " . $daily_field_file . ". \r\n";
                            }
                            if ($this->check_space($num)) {
                                $messages[] = "Wrong datatype, there has a space " . $daily_field_file . ". \r\n";
                            }
                        }
                        ##
                        ## Check Length
                        if ($daily_datatype == "d") {
                            if($this->checkLength($daily_value, $daily_length)){
                                 $messages[] = $daily_field." digit exceeded, maximum allowed digit is ".($daily_length-3)." with 2 decimal place". ". \r\n";
                            }
                        }else if($daily_datatype == "d3"){
                            if($this->checkLength($daily_value, $daily_length)){
                                 $messages[] = $daily_field." digit exceeded, maximum allowed digit is ".($daily_length-4)." with 3 decimal place ". ". \r\n";
                            }
                        }else if($daily_datatype == "i"){
                            if($this->checkLength($daily_value, $daily_length)){
                                 $messages[] = $daily_field." digit exceeded, maximum allowed digit is ".$daily_length. ". \r\n";
                            }
                        }else{
                            if($this->checkLength($daily_value, $daily_length)){
                                 $messages[] = $daily_field." length exceeded, maximum allowed length is ".$daily_length .". \r\n";
                            }
                        }
                        ##

                        if ($daily_field == "CCCODE") {
                            $str1 = preg_replace("/[^a-zA-Z0-9]+/", "", $daily_field_file);
                            $str2 = trim($daily_field);
                            if ($str1 != $str2) {
                                $messages[] = "Incorrect Format Column " . $daily_field_file . " instead of " . $daily_field . ". \r\n";
                            }
                        } else {
                            if ($daily_field_file != $daily_field) {
                                $messages[] = "Incorrect Format Column " . $daily_field_file . " instead of " . $daily_field . ". \r\n";
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
            $param['logs']=$message;
            $param['terno']=$terno;
            $param['no_trn']=$no_trn;
            return ($message != "") ? [true, $param] : [false];
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
    function checkLength($str, $len){
        return strlen(trim($str)) > $len ? true : false;
    }
}
