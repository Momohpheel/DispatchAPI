<?php

namespace App\Traits;

use App\Models\History;

trait Logs{

    public function walletLogs(){

    }

    public function history($type, $data, $id, $user){

        $history = new History;
        $history->type = $type;
        $history->data = $data;
        if ($user == 'user'){
            $history->user_id = $id;
        }else if ($user == 'rider'){
            $history->rider_id = $id;
        }else {
            $history->partner_id = $id;
        }

        $history->save();

    }


}
