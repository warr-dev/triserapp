<?php

namespace App\Http\Controllers;
use App\Models\Transactions;

use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    public function transactions()
    {
        $user = auth('sanctum')->user();
        if($user->acctype=='admin')
            return \response(['transactions'=>Transactions::all()]);
        return \response(['transactions'=>$user->transactions]);
    }
    
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'pickup' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'passengers_count' => '',
        ]);
        $book=new Transactions(
            array_merge(
                $validatedData,
                [
                    'user_id'=>auth('sanctum')->user()->id,
                    'status' => 'for pickup',
                ]
            ));
        $book->save();
        return \response(['msg'=>'Applied for service successful','status'=>'success']);
    }
    public function assign(Request $request,Transactions $id)
    {
        $id->driver_id=$request->input('to');
        $id->save();
        return \response(['msg'=>'driver Assigning successful','status'=>'success']);
    }
}
