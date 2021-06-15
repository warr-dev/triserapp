<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tricycle;

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
            'license' => 'required|string|max:255',
            'plate_no' => 'required|string|max:255',
            'cpnum' => 'required|string|max:255',
        ]);
        Tricycle::create(
            $request->all()
        );
        return \response(['msg'=>'Driver added successfully','status'=>'success']);
    }
}
