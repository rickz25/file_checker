<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\filename;

class Logs extends Model
{
    use HasFactory;
    protected $table = 'logs';
    public $timestamps = false;

    public function savelogs($params)
    {
        
        $Logs =  new Logs;
        $Logs->type = isset($params['type']) ? $params['type'] : 0;
        $Logs->error_type = $params['error_type'];
        $Logs->filename = $params['filename'];
        $Logs->merchant_code = $params['merchant_code'];
        $Logs->transaction_date = $params['transaction_date'];
        $Logs->transaction_no = $params['transaction_no'];
        $Logs->terminal_no = $params['terminal_no'];
        $Logs->error_description = $params['error_description'];
        if($Logs->save()){
            $FileN =  new filename;
            $FileN->type = 0;
            $FileN->name = $params['filename'];
            $FileN->save();
        }


    }

    public function checkIsAValidDate($date){
        return (bool)strtotime($date) && date("Y-m-d", strtotime($date)) == $date;
    }
}
