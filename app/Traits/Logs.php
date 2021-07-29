<?php

namespace App\Traits;

use App\Models\History;

use App\Models\TransactionLogs;


trait Logs{

    public function walletLogs($type,$data, $id, $user){

        $history = new History;
        $history->type = 'wallet';
        $history->data = $data;
        //$history->status = $status;
        if ($user == 'user'){
            $history->user_id = $id;
        }else if ($user == 'rider'){
            $history->rider_id = $id;
        }else if ($user == 'partner'){
            $history->partner_id = $id;
        }else{
            throw new Exception('No User Specified!');
        }

        return $history->save();

    }



    public function transactionLog($type, $data, $id, $user){

        $trans = new TransactionLogs;
        $trans->log_type = $type;
        $trans->log_data = $data;
        //$trans->status = $status;
        if ($user == 'user'){
            $trans->user_id = $id;
        }else if ($user == 'rider'){
            $trans->rider_id = $id;
        }else if ($user == 'partner'){
            $trans->partner_id = $id;
        }else{
            throw new Exception('No User Specified!');
        }

        return $trans->save();

    }


    public function history($type, $data, $id, $user){

        $history = new History;
        $history->type = $type;
        $history->data = $data;
        if ($user == 'user'){
            $history->user_id = $id;
        }else if ($user == 'rider'){
            $history->rider_id = $id;
        }else if ($user == 'partner'){
            $history->partner_id = $id;
        }else{
            throw new Exception('No User Specified!');
        }

        return $history->save();

    }


}
