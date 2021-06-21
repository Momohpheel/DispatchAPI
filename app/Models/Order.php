<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public function dropoff(){
        return $this->belongsToMany(DropOff::class, 'order_dropoffs');
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
