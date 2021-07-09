<?php

namespace App\Http\Controllers;
use App\Models\Transactions;
use App\Models\Tricycle;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
        $drivers=Tricycle::select('*');
        foreach($request->input('to') as $req){
            $drivers=$drivers->orWhere('id',$req);
        }
        $drivers=$drivers->get();
        $drivermsg=
'New Client!!!

Name: '.$id->client->profile->name.'
Location :'.$id->pickup.'
Contact Number :'.$id->client->cpnum.'
Notes : '.$id->notes;
        
        $drivernames=$drivers->pluck('name')->toArray();
        $driverplates=$drivers->pluck('plate_no')->toArray();
        $drivercpnum=$drivers->pluck('cpnum')->toArray();

        $clientmsg=
'At your service for today

Name/s : '.implode(', ',$drivernames).'
Plate Number/s :'.implode(', ',$driverplates).'
Contact Number/s :'.implode(', ',$drivercpnum);
        // $test=['cmsg'=>$clientmsg,'dmsg'=>$drivermsg];
        $this->itexmo($id->client->cpnum,$clientmsg);
        foreach($id->drivers as $driver){
            $this->itexmo($driver->cpnum,$drivermsg);
        }
        return \response([
            'msg'=>'driver Assigning successful',
            'status'=>'success',
            // 'test'=>$test
            ]);
    }
    public function cancel(Request $request,Transactions $id)
    {
        $id->status='canceled';
        $id->save();
        return \response(['msg'=>'transaction cancelled','status'=>'success']);
    }
    public function rate(Request $request,Transactions $id)
    {
        $id->status='done';
        $id->rating=$request->input('rate');
        $id->comment=$request->input('comment');
        $id->save();
        return \response(['msg'=>'Transaction marked as done','status'=>'success']);
    }
    
//     public function test()
//     {
//         $res=$this->itexmo('09635246503',
// 'Name: Client name
// Location :
// Contact Number :
// Dress color (optional) : Color of dress'
//     );
//         return \response($res);
//     }
    
    //##########################################################################
    // ITEXMO SEND SMS API - PHP - CURL-LESS METHOD
    // Visit www.itexmo.com/developers.php for more info about this API
    //##########################################################################
    function itexmo($number,$message,$apicode='ST-PETRE365581_JG6XH',$passwd='5w[1@87]8$'){
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
    //##########################################################################

}
