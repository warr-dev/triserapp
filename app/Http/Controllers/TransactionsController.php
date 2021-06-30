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
            'passengers_count' => '',
            'notes' => '',
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
        $id->drivers()->sync($request->input('to'));
        $id->status='assigned';
        $id->save();
        return \response(['msg'=>'driver Assigning successful','status'=>'success']);
    }
    public function rate(Request $request,Transactions $id)
    {
        $id->status='done';
        $id->rating=$request->input('rate');
        $id->save();
        return \response(['msg'=>'Transaction marked as done','status'=>'success']);
    }
}
