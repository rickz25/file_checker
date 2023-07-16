<?php

namespace App\Console\Commands;

ini_set('max_execution_time', 0);

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Client\ConnectionException;
use File;
use DateTime;

class TenantDataSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:TenantSync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        set_time_limit(0);
        if(!File::exists(storage_path('app/LOGS'))) {
            File::makeDirectory(storage_path('app/LOGS'), 0777, true, true);
        }
        if(!File::exists(storage_path('app/OUTGOING'))) {
            File::makeDirectory(storage_path('app/OUTGOING'), 0777, true, true);
        }
        if(!File::exists(storage_path('app/PROCESSED'))) {
            File::makeDirectory(storage_path('app/PROCESSED'), 0777, true, true);
        }
        if(!File::exists(storage_path('app/UNPROCESSED'))) {
            File::makeDirectory(storage_path('app/UNPROCESSED'), 0777, true, true);
        }
        $uri = storage_path('app/OUTGOING');
        $files = File::allFiles($uri);

        $final = [];
        $logs = '';
        if (strtolower(substr(PHP_OS, 0, 5)) === 'linux')
        {
            $vars = array();
            $_files = glob('/etc/*-release');

            foreach ($_files as $file)
            {
                $lines = array_filter(array_map(function($line) {

                    // split value from key
                    $parts = explode('=', $line);

                    // makes sure that "useless" lines are ignored (together with array_filter)
                    if (count($parts) !== 2) return false;

                    // remove quotes, if the value is quoted
                    $parts[1] = str_replace(array('"', "'"), '', $parts[1]);
                    return $parts;

                }, file($file)));

                foreach ($lines as $line)
                    $vars[$line[0]] = $line[1];
            }

            if ((float) $vars['VERSION_ID'] < 12.04) {
                $logs = "Application is not compatible with lower version of operating system.\r\n
                    Minimum Requirement: Ubuntu 12.04 or higher. \r\n";
                $name = "Error_" . date('mdY');
                $this->logs($name, $logs, 1);
                return;
            }
        }else{

            if ((int) php_uname('r') < 10) {
                $logs = "Application is not compatible with lower version of operating system.\r\n
                    Minimum Requirement: Windows 10 or higher. \r\n";
                $name = "Error_" . date('mdY');
                $this->logs($name, $logs, 1);
                return;
            }

        }

        if (count($files) > 0) {

            foreach ($files as $fi => $file) {

                $file_extension = File::extension($file);
                $filename = basename($file);
                $contract_no = config('settings.contract_no');
                $jwt_token = config('settings.jwt_token');

                if (strlen($filename) > 25) {
                    if ($file_extension == 'csv' || $file_extension == 'CSV') {
                        $start3 = substr($filename, 0, 3);
                        $tmp = array_map('str_getcsv', file($file));
                        $arrKeys = array_column($tmp, 0);
                        $arrVals = array_column($tmp, 1);
                        $array = array_map(function ($key, $val) {
                            return [$key => $val];
                        }, $arrKeys, $arrVals);
                        $CCCODE = isset($tmp[0][1]) ? trim($tmp[0][1]) : '';
                        ## for null sales
                        if (empty($tmp)) {
                            $this->staticDataJson('null', $filename, $jwt_token);
                            return;
                        }

                        ### START DAILY
                        if ($start3 == "EOD") {
                            $filename1 = substr($filename, 0, -4);
                            $merchant_code = substr($filename1, 3, 17);
                            $TRN_DATE = substr($filename1, 20, 6);
                            $m = substr($filename, 20, 2);
                            $d = substr($filename, 22, 2);
                            $y = '20' . substr($filename, 24, 2);
                            $DATE = $y . '-' . $m . '-' . $d;
                            ### start format validate
                            $validate = $this->format_validation_daily($tmp, $DATE);
                            if ($validate[0] == true) {
                                $logs = $validate[1];
                                $this->logs($filename, $logs, 1);
                                $this->move_to_unprocessed_folder($filename); //MOVE TO UNPROCESSED FOLDER
                                return;
                            }
                            ### End format validate
                            if (!is_numeric($TRN_DATE)) {
                                $logs = "Incorrect Filename. Non-numeric TRN_DATE (" . $filename . "). \r\n";
                                $this->logs($filename, $logs, 1);
                                $this->move_to_unprocessed_folder($filename); //MOVE TO UNPROCESSED FOLDER
                                return;
                            }
                            if ($CCCODE != $contract_no) {
                                $logs = "Incorrect CCCODE (" . $filename . "). \r\n";
                                $this->logs($filename, $logs, 1);
                                $this->move_to_unprocessed_folder($filename); //MOVE TO UNPROCESSED FOLDER
                                return;
                            }
                            $daily = $this->daily($tmp, $final, $fi, $filename);
                            $result = $this->apiPost($jwt_token, $daily);
                            return $this->moveFile($result, $filename);
                            ### END DAILY

                            ### START TRANSACTION
                        } else {

                            $filename1 = substr($filename, 0, -4);
                            $merchant_code = substr($filename1, 0, 17);
                            $TRN_DATE = substr($filename1, 17, 6);
                            $TER_NO = substr($filename1, 23, 3);

                            ### start format validate
                            $validate = $this->format_validation_trans($array, $tmp, $filename);
                            if ($validate[0] == true) {
                                $logs = $validate[1];
                                $this->logs($filename, $logs, 1);
                                $this->move_to_unprocessed_folder($filename); //MOVE TO UNPROCESSED FOLDER
                                return;
                            } else {
                                if ($validate[1][0]) {
                                    ## Number of transaction validation
                                    $this->staticDataJson('no_trn_validation', $filename, $jwt_token, $validate[1][1], $validate[1][2]);
                                    return;
                                }
                            }

                            ### end format validate

                            if (!is_numeric($TRN_DATE)) {
                                $logs = "Incorrect Filename. Non-numeric TRN_DATE (" . $filename . "). \r\n";
                                $this->logs($filename, $logs, 1);
                                $this->move_to_unprocessed_folder($filename); //MOVE TO UNPROCESSED FOLDER
                                return;
                            }
                            if (!is_numeric($TER_NO)) {
                                $logs = "Incorrect Filename. Non-numeric TER_NO (" . $filename . "). \r\n";
                                $this->logs($filename, $logs, 1);
                                $this->move_to_unprocessed_folder($filename); //MOVE TO UNPROCESSED FOLDER
                                return;
                            }
                            if ($CCCODE != $contract_no) {
                                $logs = "Incorrect CCCODE (" . $filename . "). \r\n";
                                $this->logs($filename, $logs, 1);
                                $this->move_to_unprocessed_folder($filename); //MOVE TO UNPROCESSED FOLDER
                                return;
                            }
                            $transaction = $this->transaction($tmp, $array, $final, $fi, $filename);
                            $result = $this->apiPost($jwt_token, $transaction);
                            return $this->moveFile($result, $filename);
                        }

                        ### END TRANSACTION
                    } else {
                        $logs = "Accept only (.csv) file extension (" . $filename . "). \r\n";
                        $this->logs($filename, $logs, 1);
                        $this->move_to_unprocessed_folder($filename); //MOVE TO UNPROCESSED FOLDER
                        return;
                    }

                    ######################################### Mapping Format ##############################################################################

                } else {

                    $file = $uri . "/" . $filename;
                    $csv = array_map("str_getcsv", file($file, FILE_SKIP_EMPTY_LINES));
                    $keys = array_shift($csv);
                    foreach ($csv as $i => $row) {
                        $csv[$i] = array_combine($keys, $row);
                    }

                    $mapping['filename'] = $filename;
                    $mapping['datafile'] = substr($filename, -5, 1) == 'H' ? 'transaction' : 'daily';
                    $mapping['cccode'] = config('settings.contract_no');
                    $mapping['posvendorcode'] = config('settings.pos_vendor_code');
                    $mapping['token'] = config('settings.bearer_token');
                    $mapping['transaction_no'] = date("mdyhis");
                    $mapping['columns'] = $keys;
                    $mapping['data'] = $csv;

                    $data = json_encode($mapping);
                    // $auth = "Authorization: Bearer " . $jwt_token;
                    // $url = 'http://' . config('settings.server_ip') . '/api/post-data';
                    // $json = $this->HTTPPost($auth, $url, ['data' => $data]);
                    $fileMapping = $mapping['filename'];
                    $result = $this->apiPost($jwt_token, $data);
                    return $this->moveFile($result, $fileMapping);
                }
            }
        }
    }
    ### Connection API
    public function apiPost($token, $data)
    {
        try {
            $response = Http::timeout(1000)->retry(2, 1000)->withToken($token)->post('http://' . config('settings.server_ip') . '/api/post-data', ['data' => $data]); //send to autopoll
            return $response->ok() ? $response->json() : $response->status();
        } catch (ConnectionException $e) {
            return '599';
        }
    }
    public function moveFile($result, $filename)
    {
        if (isset($result['status'])) {
            if ($result['status'] == 1) {
                $logs = $result['success'] . "(" . $filename . "). \r\n";
                $this->logs($filename, $logs, 0);
                $this->move_to_processed_folder($filename); //MOVE TO PROCESSED FOLDER
                return;
            } else if ($result['status'] == 0) {
                $error = isset($result['error']) ? $result['error'] : "Error File,";
                $error = explode(".", $error);
                $logs = "Check csv file. (" . $filename . "). \r\n";
                foreach ($error as $er) {
                    $logs .= $er . "\r\n";
                }
                $this->logs($filename, $logs, 1);
                $this->move_to_unprocessed_folder($filename); //MOVE TO UNPROCESSED FOLDER
                return;
            } else {
                $logs .= $result . "(" . $filename . "). \r\n";
                $this->logs($filename, $logs, 1);
                $this->move_to_unprocessed_folder($filename); //MOVE TO UNPROCESSED FOLDER
                return;
            }
        } else {
            if ($result == '599') {
                $logs = $this->http_code_status($result) . "(" . $filename . "). \r\n";
                $this->logs($filename, $logs, 1);
                return;
            }
            $logs = $this->http_code_status($result) . "(" . $filename . "). \r\n";
            $this->logs($filename, $logs, 1);
            $this->move_to_unprocessed_folder($filename); //MOVE TO UNPROCESSED FOLDER
            return;
        }
    }

    ### TRANSACTION / HOURLY
    public function transaction($tmp, $array, $final, $fi, $filename)
    {
        $tenant_code = config('settings.tenant_code');
        $terminal_code = config('settings.terminal_code');
        $contract_no = config('settings.contract_no');
        $pos_vendor_code = config('settings.pos_vendor_code');
        $model_platform_code = config('settings.model_platform_code');
        $token = config('settings.bearer_token');

        $final[$fi]['filename'] = $filename;
        $final[$fi]['contract'] = $contract_no;
        $final[$fi]['modelplatform'] = $model_platform_code;
        $final[$fi]['posvendorcode'] = $pos_vendor_code;
        $final[$fi]['tenantno'] = $tenant_code;
        $final[$fi]['terminalno'] = $terminal_code;
        $final[$fi]['token'] = $token;
        $final[$fi]['filedata']['CCCODE'] = trim($tmp[0][1]);
        $final[$fi]['filedata']['MERCHANT_NAME'] = trim($tmp[1][1]);
        $final[$fi]['filedata']['TRN_DATE'] = $tmp[2][1];
        $final[$fi]['filedata']['NO_TRN'] = $tmp[3][1];

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
        $final[$fi]['filedata']['TRANSACTION'] = $transactions;

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
        $server_ip = config('settings.server_ip');
        $tenant_code = config('settings.tenant_code');
        $terminal_code = config('settings.terminal_code');
        $contract_no = config('settings.contract_no');
        $pos_vendor_code = config('settings.pos_vendor_code');
        $model_platform_code = config('settings.model_platform_code');
        $token = config('settings.bearer_token');

        $final[$fi]['filename'] = $filename;
        $final[$fi]['contract'] = $contract_no;
        $final[$fi]['modelplatform'] = $model_platform_code;
        $final[$fi]['posvendorcode'] = $pos_vendor_code;
        $final[$fi]['tenantno'] = $tenant_code;
        $final[$fi]['terminalno'] = $terminal_code;
        $final[$fi]['token'] = $token;
        $final[$fi]['filedata']['CCCODE'] = trim($tmp[0][1]);
        $final[$fi]['filedata']['MERCHANT_NAME'] = trim($tmp[1][1]);
        $daily = [];
        $terminals = [];
        if (isset($tmp)) {
            $keys = array_column($tmp, 0);
            foreach ($tmp[0] as $ke => $val) {
                try {
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
                } catch (\Throwable $e) {
                    continue;
                }
            }
        }
        $final[$fi]['filedata']['TERMINALS'] = $terminals;
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
                        $main_message .= "Wrong decimal " . $tmp[$i][0] . ". \r\n";
                    }
                } else {
                    $main_message .= "Wrong datatype " . $tmp[$i][0] . ". \r\n";
                }
                if ($this->check_space($num)) {
                    $main_message .= "Wrong datatype, there has a space " . $tmp[$i][0] . ". \r\n";
                }
            }
            ##
            ## string datatype
            if ($header_format[$i][1] == "s") {
                $data = $tmp[$i][1];

                if ($this->check_string($data)) {
                    $main_message .= "Wrong datatype " . $tmp[$i][0] . ". \r\n";
                }
                if ($header_format[$i][0] != "MERCHANT_NAME") {
                    if ($this->check_space($data)) {
                        $main_message .= "Wrong datatype, there has a space " . $tmp[$i][0] . ". \r\n";
                    }
                }
                if ($this->check_quotation($data)) {
                    $main_message .= "Wrong datatype, there has a quotation " . $tmp[$i][0] . ". \r\n";
                }
            }
            if ($header_format[$i][0] == "TRN_DATE") {
                if ($tmp[$i][1] != $TRN_DATE) {
                    $main_message .= "TRN_DATE in filename not equal to " . $header_format[$i][0] . " inside the file. \r\n";
                }
            }
            ##
            ## integer or numeric
            if ($header_format[$i][1] == "i") {
                $num = $tmp[$i][1];
                if (!ctype_digit($num)) {
                    $main_message .= "Wrong datatype " . $tmp[$i][0] . ". \r\n";
                }
                if ($this->check_space($num)) {
                    $main_message .= "Wrong datatype, there has a space " . $tmp[$i][0] . ". \r\n";
                }
            }
            ##
            if ($header_format[$i][0] == "CCCODE") {
                $str1 = preg_replace("/[^a-zA-Z0-9]+/", "", $tmp[$i][0]);
                $str2 = trim($header_format[$i][0]);
                if ($str1 != $str2) {
                    $main_message .= "Incorrect Format Column " . $tmp[$i][0] . " instead of " . $header_format[$i][0] . ". \r\n";
                }
            } else {
                if ($header_format[$i][0] != $tmp[$i][0]) {
                    $main_message .= "Incorrect Format Column " . $tmp[$i][0] . " instead of " . $header_format[$i][0] . ". \r\n";
                }
            }
            if ($tmp[$i][1] == "") {
                $main_message .= "Empty Column " . $tmp[$i][0] . ". \r\n";
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
                $no_trn_validation = [true, $terno, $transno];
            } else {
                $no_trn_validation = [false, $terno, $transno];
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
                        $messages[] = "Incorrect Format Column " . $items[$r][$i][0] . " instead of " . $items_format[$f][0] . ". \r\n";
                    }
                    if ($items[$r][$i][1] == "" || $items[$r][$i][1] == null) {
                        $messages[] = "Empty Column " . $items_format[$f][0] . ". \r\n";
                    }
                    ## double and float datatype
                    if ($items_format[$f][1] == "d" || $items_format[$f][1] == "f") {
                        $num = $items[$r][$i][1];
                        if (is_numeric($num)) {
                            if (!$this->check_decimal($num)) {
                                $messages[] = "Wrong decimal " . $items[$r][$i][0] . ". \r\n";
                            }
                        } else {
                            $messages[] = "Wrong datatype " . $items[$r][$i][0] . ". \r\n";
                        }
                        if ($this->check_space($num)) {
                            $messages[] = "Wrong datatype, there has a space " . $items[$r][$i][0] . ". \r\n";
                        }
                    }
                    ##
                    ## double 3decimal places
                    if ($items_format[$f][1] == "d3") {
                        $num = $items[$r][$i][1];
                        if (is_numeric($num)) {
                            if (!$this->check_3_decimal($num)) {
                                $messages[] = "Wrong decimal " . $items[$r][$i][0] . ". \r\n";
                            }
                        } else {
                            $messages[] = "Wrong datatype " . $items[$r][$i][0] . ". \r\n";
                        }
                        if ($this->check_space($num)) {
                            $messages[] = "Wrong datatype, there has a space " . $items[$r][$i][0] . ". \r\n";
                        }
                    }
                    ##
                    ## string datatype
                    if ($items_format[$f][1] == "s") {
                        $data = $items[$r][$f][1];
                        if ($this->check_string($data)) {
                            $messages[] = "Wrong datatype " . $items[$r][$i][0] . ". \r\n";
                        }
                        if ($this->check_space($data)) {
                            $messages[] = "Wrong datatype, there has a space " . $items[$r][$i][0] . ". \r\n";
                        }
                        if ($this->check_quotation($data)) {
                            $message .= "Wrong datatype, there has a quotation " . $items[$r][$i][0] . ". \r\n";
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
                            $messages[] = "Incorrect Format Column " . $transaction[$r][$i][0] . " instead of " . $transaction_format[$i][0] . ". \r\n";
                        }
                        if ($transaction[$r][$i][0] != "MOBILE_NO") {
                            if ($transaction[$r][$i][1] == "") {
                                $messages[] = "Empty Column " . $transaction_format[$i][0] . ". \r\n";
                            }
                        }
                        if ($transaction[$r][$i][0] == "TER_NO") {
                            if (trim($transaction[$r][$i][1]) !== $TER_NO) {
                                $messages[] = "TER_NO in filename not equal to " . $transaction_format[$i][0] . " inside the file. \r\n";
                            }
                        }
                        if ($transaction[$r][$i][0] == "TRANSACTION_NO") {
                            if (strlen(trim($transaction[$r][$i][1])) > 15) {
                                $messages[] = "TRANSACTION_NO should contain maximum of 15 numbers only (" . $transaction[$r][$i][1] . "). \r\n";
                            }
                            if ($this->checkColumn($transaction[$r][$i][1], $TRANS_NO)) {
                                $messages[] = "There are same TRANSACTION_NO (" . $transaction[$r][$i][1] . "). \r\n";
                            }
                            $TRANS_NO[] = $transaction[$r][$i][1];
                        }
                        ## double and float datatype
                        if ($transaction_format[$i][1] == "d" || $transaction_format[$i][1] == "f") {
                            $num = $transaction[$r][$i][1];
                            if (is_numeric($num)) {
                                if (!$this->check_decimal($num)) {
                                    $messages[] = "Wrong decimal " . $transaction[$r][$i][0] . ". \r\n";
                                }
                            } else {
                                $messages[] = "Wrong datatype " . $transaction[$r][$i][0] . ". \r\n";
                            }
                            if ($this->check_space($num)) {
                                $messages[] = "Wrong datatype, there has a space " . $transaction[$r][$i][0] . ". \r\n";
                            }
                        }
                        ##
                        ## double 3decimal places
                        if ($transaction_format[$i][1] == "d3") {
                            $num = $transaction[$r][$i][1];
                            if (is_numeric($num)) {
                                if (!$this->check_3_decimal($num)) {
                                    $messages[] = "Wrong decimal " . $transaction[$r][$i][0] . ". \r\n";
                                }
                            } else {
                                $messages[] = "Wrong datatype " . $transaction[$r][$i][0] . ". \r\n";
                            }
                            if ($this->check_space($num)) {
                                $messages[] = "Wrong datatype, there has a space " . $transaction[$r][$i][0] . ". \r\n";
                            }
                        }
                        ##
                        ## string datatype
                        if ($transaction_format[$i][1] == "s") {
                            $data = $transaction[$r][$i][1];
                            if ($this->check_string($data)) {
                                $messages[] = "Wrong datatype " . $transaction[$r][$i][0] . ". \r\n";
                            }
                            if ($this->check_space($data)) {
                                $messages[] = "Wrong datatype, there has a space " . $transaction[$r][$i][0] . ". \r\n";
                            }
                            if ($this->check_quotation($data)) {
                                $message .= "Wrong datatype, there has a quotation " . $transaction[$r][$i][0] . ". \r\n";
                            }
                        }
                        ##
                        ## integer or numeric
                        if ($transaction_format[$i][1] == "i") {
                            $num = $transaction[$r][$i][1];
                            if (!ctype_digit($num)) {
                                $messages[] = "Wrong datatype " . $transaction[$r][$i][0] . ". \r\n";
                            }
                            if ($this->check_space($num)) {
                                $messages[] = "Wrong datatype, there has a space " . $transaction[$r][$i][0] . ". \r\n";
                            }
                        }
                        ##
                    } else {
                        $messages[] = "Out of format file " . $transaction[$r][$i][0] . ". \r\n";
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
                        $messages[] = "Empty in Column " . $daily_format[$i][0] . ". \r\n";
                    } else {
                        ## double and float datatype
                        if ($daily_format[$i][1] == "d" || $daily_format[$i][1] == "f") {
                            $num = $tmp[$i][$r];
                            if (is_numeric($num)) {
                                if (!$this->check_decimal($num)) {
                                    $messages[] = "Wrong decimal " . $daily_format[$i][0] . ". \r\n";
                                }
                            } else {
                                $messages[] = "Wrong datatype " . $daily_format[$i][0] . ". \r\n";
                            }
                            if ($this->check_space($num)) {
                                $messages[] = "Wrong datatype, there has a space " . $daily_format[$i][0] . ". \r\n";
                            }
                        }
                        ##

                        ## string datatype
                        if ($daily_format[$i][1] == "s") {
                            $data = $tmp[$i][$r];
                            if ($this->check_string($data)) {
                                $messages[] = "Wrong datatype " . $daily_format[$i][0] . ". \r\n";
                            }
                            if ($daily_format[$i][0] != "MERCHANT_NAME") {
                                if ($this->check_space($data)) {
                                    $messages[] = "Wrong datatype, there has a space " . $daily_format[$i][0] . ". \r\n";
                                }
                                if ($this->check_quotation($data)) {
                                    $message .= "Wrong datatype, there has a quotation " . $daily_format[$i][0] . ". \r\n";
                                }
                            }
                        }
                        if ($daily_format[$i][0] == "TRN_DATE") {
                            if ($tmp[$i][$r] != $TRN_DATE) {
                                $message .= "TRN_DATE in filename not equal to " . $daily_format[$i][0] . " inside the file. \r\n";
                            }
                        }
                        if ($daily_format[$i][0] == "STRANS" || $daily_format[$i][0] == "ETRANS") {
                            if (strlen(trim($tmp[$i][$r])) > 15) {
                                $messages[] = $daily_format[$i][0] . " should contain maximum of 15 numbers only (" . $tmp[$i][$r] . "). \r\n";
                            }
                        }
                        ##

                        ## integer or numeric
                        if ($daily_format[$i][1] == "i") {
                            $num = $tmp[$i][$r];
                            if (!ctype_digit($num)) {
                                $messages[] = "Wrong datatype " . $tmp[$i][0] . ". \r\n";
                            }
                            if ($this->check_space($num)) {
                                $messages[] = "Wrong datatype, there has a space " . $tmp[$i][0] . ". \r\n";
                            }
                        }
                        ##
                        if ($daily_format[$i][0] == "CCCODE") {
                            $str1 = preg_replace("/[^a-zA-Z0-9]+/", "", $tmp[$i][0]);
                            $str2 = trim($daily_format[$i][0]);
                            if ($str1 != $str2) {
                                $message .= "Incorrect Format Column " . $tmp[$i][0] . " instead of " . $daily_format[$i][0] . ". \r\n";
                            }
                        } else {
                            if ($tmp[$i][0] != $daily_format[$i][0]) {
                                $messages[] = "Incorrect Format Column " . $tmp[$i][0] . " instead of " . $daily_format[$i][0] . ". \r\n";
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

    public function HTTPPost($auth, $url, array $params)
    {
        $query = http_build_query($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 500);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
    public function http_code_status($code)
    {
        $http_status_codes = array(100 => "Continue", 101 => "Switching Protocols", 102 => "Processing", 200 => "OK", 201 => "Created", 202 => "Accepted", 203 => "Non-Authoritative Information", 204 => "No Content", 205 => "Reset Content", 206 => "Partial Content", 207 => "Multi-Status", 300 => "Multiple Choices", 301 => "Moved Permanently", 302 => "Found", 303 => "See Other", 304 => "Not Modified", 305 => "Use Proxy", 306 => "(Unused)", 307 => "Temporary Redirect", 308 => "Permanent Redirect", 400 => "Bad Request", 401 => "Unauthorized", 402 => "Payment Required", 403 => "Forbidden", 404 => "Not Found", 405 => "Method Not Allowed", 406 => "Not Acceptable", 407 => "Proxy Authentication Required", 408 => "Request Timeout", 409 => "Conflict", 410 => "Gone", 411 => "Length Required", 412 => "Precondition Failed", 413 => "Request Entity Too Large", 414 => "Request-URI Too Long", 415 => "Unsupported Media Type", 416 => "Requested Range Not Satisfiable", 417 => "Expectation Failed", 418 => "I'm a teapot", 419 => "Authentication Timeout", 420 => "Enhance Your Calm", 422 => "Unprocessable Entity", 423 => "Locked", 424 => "Failed Dependency", 424 => "Method Failure", 425 => "Unordered Collection", 426 => "Upgrade Required", 428 => "Precondition Required", 429 => "Too Many Requests", 431 => "Request Header Fields Too Large", 444 => "No Response", 449 => "Retry With", 450 => "Blocked by Windows Parental Controls", 451 => "Unavailable For Legal Reasons", 494 => "Request Header Too Large", 495 => "Cert Error", 496 => "No Cert", 497 => "HTTP to HTTPS", 499 => "Client Closed Request", 500 => "Error not found or Incorrect file format (check csv file)", 501 => "Not Implemented", 502 => "Bad Gateway", 503 => "Service Unavailable", 504 => "Gateway Timeout", 505 => "HTTP Version Not Supported", 506 => "Variant Also Negotiates", 507 => "Insufficient Storage", 508 => "Loop Detected", 509 => "Bandwidth Limit Exceeded", 510 => "Not Extended", 511 => "Network Authentication Required", 598 => "Network read timeout error", 599 => "Network connect timeout error");
        foreach ($http_status_codes as $key => $status) {
            if ($key == $code) {
                return $status;
            }
        }
        return 'Server Error.';
    }
    public function staticDataJson($data, $filename, $jwt_token, $terno = 0, $transno = 0)
    {
        $tenant_code = config('settings.tenant_code');
        $pos_vendor_code = config('settings.pos_vendor_code');
        $model_platform_code = config('settings.model_platform_code');
        $token = config('settings.bearer_token');
        $filename1 = substr($filename, 0, -4);
        $filedata['filename'] = $filename;
        $filedata['contract'] = substr($filename1, 0, 17);
        $filedata['modelplatform'] = $model_platform_code;
        $filedata['posvendorcode'] = $pos_vendor_code;
        $filedata['tenantno'] = $tenant_code;
        $filedata['terminalno'] = substr($filename1, 23, 3);
        $filedata['trndate'] = substr($filename1, 17, 6);
        $filedata['token'] = $token;
        $filedata['filedata'] = $data;
        if ($transno != 0) {
            $filedata['transno'] = $transno;
            $filedata['terno'] = $terno;
        }
        // return $transno;

        $this->apiPost($jwt_token, json_encode($filedata));
        $logs = "Successfully processed. (" . $filename . "). \r\n";
        $this->logs($filename, $logs, 0);
        $this->move_to_processed_folder($filename); //MOVE TO PROCESSED FOLDER
    }
    ### MOVE TO PROCESSED FOLDER
    public function move_to_processed_folder($filename)
    {
        $path = 'OUTGOING/' . $filename;
        $processed = "PROCESSED/" . $filename;
        Storage::delete($processed);
        Storage::move($path, $processed);
    }
    ### MOVE TO PROCESSED FOLDER
    public function move_to_unprocessed_folder($filename)
    {
        $path = 'OUTGOING/' . $filename;
        $processed = "UNPROCESSED/" . $filename;
        Storage::delete($processed);
        Storage::move($path, $processed);
    }
    public function logs($filename, $logs, $error)
    {
        if ($error == 0) {
            $txt_file = "LOGS/" . $filename . " success.txt";
            Storage::disk('local')->put($txt_file, $logs);
        } else {
            $txt_file = "LOGS/" . $filename . " Error.txt";
            Storage::disk('local')->put($txt_file, $logs);
        }
    }
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