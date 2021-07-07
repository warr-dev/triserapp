<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tricycle;
use App\Models\Transactions;

class TricycleController extends Controller
{
    public function index()
    {
        return \response(['drivers'=>Tricycle::all()]);
    }
    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'plate_no' => 'required|string|max:255',
            'cpnum' => 'required|string|max:255',
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
        $comments= $id->transactions()->orderBy('id','desc')->where('status','done')->take(5)->get()->pluck('comment')->toArray();
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
}
