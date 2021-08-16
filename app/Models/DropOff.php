<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DropOff extends Model
{
    use HasFactory;
    public function order(){
        return $this->belongsTo(Order::class);
    }

    public function partner(){
        return $this->belongsTo(Partner::class);
    }
}
