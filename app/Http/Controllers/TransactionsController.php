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
        $nextid=Transactions::getnextdriver($request->passengers_count);
        //return response(['sdas'=>Transactions::getnextdriver($request->passengers_count)]);
        $validatedData = $request->validate([
            'pickup' => 'required|string|max:255',
            'passengers_count' => 'required|numeric',
            'notes' => '',
            'displayname'=>''
        ]);
        $displayname=$request->displayname?1:0;
        $book=new Transactions(
            array_merge(
                $validatedData,
                [
                    'user_id'=>auth('sanctum')->user()->id,
                    'status' => 'for pickup',
                    'displayname'=>$displayname
                ]
            ));
        $book->save();
        // $book->drivers()->sync($nextid);
        //$this->assign($book->id,$nextid);
        // $trans=Transactions::find($id);
        
        $book->drivers()->sync($nextid);
        $book->status='assigned';
        $book->save();
        $drivers=Tricycle::select('*');
        foreach($nextid as $req){
            $drivers=$drivers->orWhere('id',$req);
        }
        $drivers=$drivers->get();
        $name=$book->displayname==1?'Name: '.$book->client->profile->name:'';
        $contact=$book->displayname==1?'Contact Number : '.$book->client->cpnum:'';

        $drivermsg=
'New Client!!!

'.$name.'
Location :'.$book->pickup.'
'.$contact.'
Notes : '.$book->notes;
        
        
        $drivernames=$drivers->pluck('name')->toArray();
        $driverplates=$drivers->pluck('plate_no')->toArray();
        $drivercpnum=$drivers->pluck('cpnum')->toArray();

        $clientmsg=
'At your service for today

Name/s : '.implode(', ',$drivernames).'
Plate Number/s :'.implode(', ',$driverplates).'
Contact Number/s :'.implode(', ',$drivercpnum);
        // $test=['cmsg'=>$clientmsg,'dmsg'=>$drivermsg];
        // return['cpnum'=>$book->client->cpnum,'msg'=>$clientmsg,'res'=>$this->itexmo($book->client->cpnum,$clientmsg)];
        $this->itexmo($book->client->cpnum,$clientmsg);
        foreach($drivers as $driver){
            $this->itexmo($driver->cpnum,$drivermsg);
        }
        // return \response([
        //     'msg'=>'driver Assigning successful',
        //     'status'=>'success',
        //     // 'test'=>$test
        //     ]);
        return \response(['msg'=>'Applied for service successful','status'=>'success']);
    }
    public function assign($id,$nextids)
    {
        $trans=Transactions::find($id);
        
        $trans->drivers()->sync($nextids);
        $trans->status='assigned';
        $trans->save();
        $drivers=Tricycle::select('*');
        foreach($nextids as $req){
            $drivers=$drivers->orWhere('id',$req);
        }
        $drivers=$drivers->get();
        $name=$trans->displayname==1?'Name: '.$trans->client->profile->name:'';
        $contact=$trans->displayname==1?'Contact Number : '.$trans->client->cpnum:'';

        $drivermsg=
'New Client!!!

'.$name.'
Location :'.$trans->pickup.'
'.$contact.'
Notes : '.$trans->notes;
        
        $drivernames=$drivers->pluck('name')->toArray();
        $driverplates=$drivers->pluck('plate_no')->toArray();
        $drivercpnum=$drivers->pluck('cpnum')->toArray();

        $clientmsg=
'At your service for today

Name/s : '.implode(', ',$drivernames).'
Plate Number/s :'.implode(', ',$driverplates).'
Contact Number/s :'.implode(', ',$drivercpnum);
        // $test=['cmsg'=>$clientmsg,'dmsg'=>$drivermsg];
        return['cpnum'=>$trans->client->cpnum,'msg'=>$clientmsg];
        $this->itexmo($trans->client->cpnum,$clientmsg);
        foreach($drivers as $driver){
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
    function itexmo($number,$message,$apicode='ST-BENED777092_6WFM9',$passwd='2z8ef56c!%'){
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
