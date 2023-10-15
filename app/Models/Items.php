<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Logs;

class Items extends Model
{
    use HasFactory;

    public function itemLogs($data, $TRN_DATE, $merchant_code, $filename){
        $Logs = new Logs;
        $TRANSACTION_NO = isset($data['TRANSACTION_NO']) ? $data['TRANSACTION_NO'] : '';
        $QTY_SLD = isset($data['QTY_SLD']) ? trim($data['QTY_SLD']) : '';
        $TER_NO = isset($data['TER_NO']) ? trim($data['TER_NO']) : '';
      
        if(isset($data['ITEMS'])){
            $qty=[];
            foreach($data['ITEMS'] as $sumList){
                $qty[] = trim($sumList['QTY']);
            }
            $total_qty = number_format(array_sum($qty), 3);
            if(floatval($QTY_SLD) !== floatval($total_qty)){
                $qtyDiff = abs(floatval($QTY_SLD) - floatval($total_qty));
                $message = 'ITEMS QTY_SLD discrepancy ('.$qtyDiff.')';
                $param['type'] = 0;
                $param['error_type']='Transaction';
                $param['filename']=$filename;
                $param['merchant_code'] = $merchant_code;
                $param['transaction_date']=$TRN_DATE;
                $param['transaction_no']=$TRANSACTION_NO;
                $param['terminal_no']=$TER_NO;
                $param['error_description']=$message;
                $Logs->savelogs($param); /**save logs */ 
            }
        }
   
    }
}
