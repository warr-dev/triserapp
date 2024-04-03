<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);
        $user = User::where("username", $request->username)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }
        if ($user->status != 'verified') {
            return \response([
                'status' => 'failed',
                'message' => 'Account was not verified yet',
                'shouldValidate' => true
            ], 403);
        }
        return response()->json(['user' => $user, 'token' => $user->createToken($user->username)->plainTextToken]);
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'fname' => 'required|string|max:255',
            'mname' => 'string|max:255',
            'lname' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'cpnum' => 'required|digits:11',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'username' => $validatedData['username'],
            'cpnum' => $validatedData['cpnum'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'code' => Str::random(4),
        ]);
        if ($user)
            Profile::create([
                'fname' => $validatedData['fname'],
                'mname' => $validatedData['mname'],
                'lname' => $validatedData['lname'],
                'user_id' => $user->id,
            ]);
        $ms = "Triser: your code is " . $user->code;
        // $asd = $this->itexmo($user->cpnum, $ms);

        // $mail = new MailMessage();

        $token = $user->createToken('auth_token')->plainTextToken;


        // return response()->json([
        //             'access_token' => $token,
        //             'token_type' => 'Bearer',
        // ]);
        return response()->json([
            'status' => 'success',
            // 'asd' => $asd,
            // 'cpnum' => $user->cpnum,
            // 'ms' => $ms,
            'msg' => 'Please Confirm with Verification code',
        ]);
    }
    public function logout()
    {
        $request->user()->currentAccessToken()->delete();
        return \response(['status' => 'successful', 'msg' => 'Log out successful']);
    }
    public function myAccount()
    {
        return \response([
            'status' => 'successful',
            'user' => auth('sanctum')->user(),
            'profile' => auth('sanctum')->user()->profile
        ]);
    }
    public function verify(Request $request)
    {
        $validatedData = $request->validate([
            'code' => 'required',
        ]);
        $user = User::where('code', $request->input('code'))->first();
        if (!$user) {
            return \response([
                'status' => 'failed',
                'msg' => 'Code was not valid'
            ], 403);
        }
        $user->status = 'verified';
        $user->save();
        return \response([
            'status' => 'success',
            'msg' => 'The account was verified successfully'
        ]);
    }
    public function update(Request $request)
    {
        $user = auth('sanctum')->user();
        // return response($user->username);
        $validatedData = $request->validate([
            'username' => 'required|string',
            'cpnum' => 'required|digits:11',
            'email' => 'required|email',
        ]);
        $data = [
            'username' => $request->input('username'),
            'cpnum' => $request->input('cpnum'),
            'email' => $request->input('email'),
        ];
        if ($request->input('password'))
            $data['password'] = $request->input('password');
        $user->update($data);
        if ($user->acctype != 'admin')
            $user->profile->update([
                'fname' => $request->input('fname'),
                'mname' => $request->input('mname'),
                'lname' => $request->input('lname')
            ]);
        return \response([
            'status' => 'successful',
            'msg' => "Profile Updated Successfully",
        ]);
    }
}
