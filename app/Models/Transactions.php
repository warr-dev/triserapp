<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    use HasFactory;
    protected $table='service';

    protected $fillable=[
        'user_id','pickup','destination','passengers_count'
    ];

    public function drivers()
    {
        return $this->belongsToMany(Tricycle::class,'driver_service','transaction_id','driver_id');
    }
    protected $with=["drivers"];
}
