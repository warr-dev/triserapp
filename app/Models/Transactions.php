<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    use HasFactory;
    protected $table='service';

    public static function boot()
    {
        parent::boot();

        // self::created(function($model){
        //     //print_r(Transactions::getnextdriver($model->passengers_count));
        //     $model->drivers()->sync(Transactions::getnextdriver($model->passengers_count));
        // });

    }

    protected $fillable=[
        'user_id','pickup','destination','passengers_count','notes','rating','comment','displayname'
    ];

    public function client()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function drivers()
    {
        return $this->belongsToMany(Tricycle::class,'driver_service','transaction_id','driver_id');
    }
    public static function getnextdriver($counter){
        $count=$counter/2;
        if($counter%2!=0) $count++;
        $lastid=Tricycle::where('status','active')->orderBy('id','desc')->first()->id;
        $lasttrans=Transactions::orderBy('id','desc')->first()->id??0;

        $returned=Tricycle::where('status','active');
        if(in_array(date('N'),[1,3,5,7]))
            $returned=$returned->where(function ($query){
                for($i=0;$i<9;$i++)
                    if($i%2==1)
                        $query=$query->orWhere(function ($query) use ($i){
                            $query->whereRaw("SUBSTR(tricycle.plate_no, -1) ='$i'");
                        });
                return $query;
            });
        if(in_array(date('N'),[2,4,6]))
            $returned=$returned->where(function ($query){
                for($i=0;$i<9;$i++)
                    if($i%2==0)
                        $query=$query->orWhere(function ($query) use ($i){
                            $query->whereRaw("SUBSTR(tricycle.plate_no, -1) ='$i'");
                        });
                return $query;
            });
        if($lasttrans==0)
            return $returned->take($count)->get()->pluck('id')->toArray();
        $trans=Transactions::orderBy('service.id','desc')->join('driver_service','service.id','driver_service.transaction_id')->where('driver_service.transaction_id',$lasttrans)->get()->pluck('driver_id')->toArray();
        
        if(!$trans)
            return $returned->take($count)->get()->pluck('id')->toArray();
        if(sizeof($trans)==0)
            return $returned->take($count)->get()->pluck('id')->toArray();
        else{
            $max=max($trans);
            if($lastid==$max)
                return $returned->orderBy('id','asc')->take($count)->get()->pluck('id')->toArray();
            // $ids=Tricycle::where('id','>',$max)->where('status','active')->orderBy('id','asc')->take($count)->get()->pluck('id')->toArray();
            $ids=$returned->where('id','>',$max)->orderBy('id','asc')->take($count)->get()->pluck('id')->toArray();
            $ctr=sizeof($ids);
            if($ctr<$count){
                $ids=array_merge($ids,$returned->orderBy('id','asc')->take($count-$ctr)->get()->pluck('id')->toArray());
            }
            return $ids;
        }
    }
    protected $with=["drivers","client.profile"];
}
