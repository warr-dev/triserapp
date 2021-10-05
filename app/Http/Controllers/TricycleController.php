<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tricycle;
use App\Models\Transactions;

class TricycleController extends Controller
{
    public function index()
    {
        if(auth('sanctum')->user()->id!=1){
            $drivers=Tricycle::select('*');
            if(in_array(date('N'),[1,3,5,7]))
            $drivers=$drivers->where(function ($query){
                for($i=0;$i<9;$i++)
                    if($i%2==1)
                        $query=$query->orWhere(function ($query) use ($i){
                            $query->whereRaw("SUBSTR(tricycle.plate_no, -1) ='$i'");
                        });
                return $query;
            });
            if(in_array(date('N'),[2,4,6]))
                $drivers=$drivers->where(function ($query){
                    for($i=0;$i<9;$i++)
                        if($i%2==0)
                            $query=$query->orWhere(function ($query) use ($i){
                                $query->whereRaw("SUBSTR(tricycle.plate_no, -1) ='$i'");
                            });
                    return $query;
                });
            return \response(['drivers'=>$drivers->get()]);
        }
        return \response(['drivers'=>Tricycle::all()]);
    }
    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'plate_no' => 'required|string|max:255',
            'cpnum' => 'required|digits:11',
        ]);
        Tricycle::create(
            $request->all()
        );
        return \response(['msg'=>'Driver added successfully','status'=>'success']);
    }
    public function destroy(Tricycle $id)
    {
        $id->delete();
        return \response(['msg'=>'Driver deleted successfully','status'=>'success']);
    }
    public function show(Tricycle $id)
    {
        $count=$id->transactions()->where('status','done')->count();
        $rate=$count>0?$id->transactions()->where('status','done')->sum('rating')/$count:0; 
        $comments= $id->transactions()
            ->orderBy('id','desc')
            ->join('profile','service.user_id','profile.user_id')
            ->where('status','done')
            ->take(5)->get();
        return \response([
            'driver'=>$id,
            'status'=>'success',
            'rate'=>[
                'rate'=>$rate,
                'count'=>$count
            ],
            'comments'=>$comments
        ]);
    }
    public function update(Request $request,Tricycle $id)
    {
        $id->update($request->all());
        return \response([
            'msg'=>'Tricycle driver updated!',
            'status'=>'success',
        ]);
    }
    public function bandriver(Request $request,Tricycle $id)
    {
        $id->update(['status'=>'banned','reason'=>$request->reason]);
        $msg='
You have been banned to the terminal

Reason : '.$request->reason;
        $this->itexmo($id->cpnum,$msg);
        return \response([
            'msg'=>'Tricycle driver Banned!',
            'status'=>'success',
            'driver'=>$id
        ]);
    }
    public function unbandriver(Tricycle $id)
    {
        $id->update(['status'=>'active']);
        $msg='You have been unbanned to the terminal';
        $this->itexmo($id->cpnum,$msg);
        return \response([
            'msg'=>'Tricycle driver Un-Banned!',
            'status'=>'success',
            'driver'=>$idd
        ]);
    }
    

     
    //##########################################################################
    // ITEXMO SEND SMS API - PHP - CURL-LESS METHOD
    // Visit www.itexmo.com/developers.php for more info about this API
    //##########################################################################
    function itexmo($number,$message){
        $apicode=config('app.apiCode');
        $passwd=config('app.apiPass');
        $url = 'https://www.itexmo.com/php_api/api.php';
        $itexmo = array(
            '1' => $number, 
            '2' => $message.'
', 
            '3' => $apicode, 
            'passwd' => $passwd);
        $param = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($itexmo),
            ),
        );
        $context  = stream_context_create($param);
        return file_get_contents($url, false, $context);
    }
}
