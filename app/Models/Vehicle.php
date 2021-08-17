<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    public function partner(){
        return $this->belongsTo(Partner::class);
    }

    public function rider(){
        return $this->hasOne(Rider::class);
    }

    public function dropoff(){
        return $this->hasMany(Dropoff::class);
    }
}
