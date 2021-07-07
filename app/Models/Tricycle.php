<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tricycle extends Model
{
    use HasFactory;
    protected $table='tricycle';


    protected $fillable=[
        'user_id','name','plate_no','cpnum'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function transactions()
    {
        return $this->belongsToMany(Transactions::class,'driver_service','driver_id','transaction_id');
    }
}
