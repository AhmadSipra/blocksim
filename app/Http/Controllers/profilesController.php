<?php

namespace App\Http\Controllers;
use App\CustomData\Utilclass;
use App\User;
use App\Order;
use App\Profiles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use DB;
use App\Http\Controllers;

class profilesController extends Controller
{


    public function index()
    {
        $admin = \App\Profiles::all();
        return response()->json($admin);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {   
        return response('sss');
    //  $validator = Validator::make($request->all(), [
    //         'userID' => 'required',
    //         'First_Name' => 'required',
    //         'Last_Name' => 'required',
    //         'Address' => 'required',
    //         'Quantity' => 'required',
    //     ]);

    //    if ($validator->fails()) {
    //     return $validator->errors();
    // }


       // $userID = $request->input('userID');


       //  //Get authencation token from request
       //  $authToken = $_SERVER['HTTP_AUTHORIZATION'];

       //  $util = new Utilclass();
       //  $IsAuth = $util->isAuthenticated($userID, $authToken);


       //  if (!$IsAuth) {
       //      return response()->json(['statusCode' => '0', 'statusMessage' => 'Invalid Token or Token Is Expired', 'Result' => NULL]);
       //  }

                   $order = new Profiles();

                   // return $order;

             $order->userID=$request->input('userID');
             $order->First_Name=$request->input('First_Name');
             $order->Last_Name=$request->input('Last_Name');
             $order->Address =$request->input('Address');
             $order->Date_OF_Birth =$request->input('Date_OF_Birth');
             $order->CNIC=$request->input('CNIC');
             $order->Quantity=$request->input('Quantity');

                $order->save();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Order save', 'Result' => $order]);

    }

   public function show($id)
    {
        $Order = Profiles::find($id);
        $Order = DB::table('orders')
            ->select()
            ->where('id', '=', $id)
            ->get();
        return response()->json(['statusCode' => '1', 'statusMessage' => 'showing all Products', 'Result' => $Order]);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */


    public function update(Request $request)
    {   
        $userID=$request->input('userID');
 
    $profileID = DB::table('profiles')
            ->select()
            ->where('userID', $userID)
            ->pluck('id')
            ->first();
            // return  json_encode($profileID);

                     $profile = Profiles::find($profileID);

       
        if (Input::get("First_Name") != NULL) {
            $profile->First_Name = Input::get("First_Name");
        }

        if (Input::get("Last_Name") != NULL) {
            $profile->Last_Name = Input::get("Last_Name");
        }
         if (Input::get("UUID") != NULL) {
            $profile->UUID = Input::get("UUID");
        }
        if (Input::get("Address") != NULL) {
            $profile->Address = Input::get("Address");
        }
        if (Input::get("Date_OF_Birth") != NULL) {
            $profile->Date_OF_Birth = Input::get("Date_OF_Birth");
        }
         if (Input::get("CNIC") != NULL) {
            $profile->CNIC = Input::get("CNIC");
        }
        if (Input::get("status") != NULL) {
            $profile->status = Input::get("status");
        } 
        if (Input::get("Quantity") != NULL) {
            $profile->Quantity = Input::get("Quantity");
        }

                $profile->save();

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Profile Details updated', 'Result' => $profile]);
    }

    public function destroy($id)
    {
         $order = Profiles::find($id);
    if (!$order) {
        return response()->json(['statusCode' => '0', 'statusMessage' => 'No Order Found', 'Result' => NULL]);
    }
        
        DB::table('orders')->where('id', $id)->update(array('status' => 0));

        return response()->json(['statusCode' => '1', 'statusMessage' => 'ORDER DELETED', 'Result' => NULL]);

    }

 public function uploadCNICImage(Request $request)
    {


      $userID = $request->input('userID');

        // $id = Profiles::find($userID)->where('userID', '=',$userID)->pluck('id');
         $id = DB::table('profiles')
            ->select()
            ->where('userID', '=', $userID)
            ->pluck('id')
            ->first();
   // return response('hetgdf');

        //Get user id from the request
//       $userID = $request->input('userID');

//       //Get authencation token from request
//      $authToken = $_SERVER['HTTP_AUTHORIZATION'];

// if(!$authToken)
// {
//    return response()->json(['statusCode'=>'0','statusMessage'=>'Authorization Token Not Found','Result'=>NULL]);
 
// }
//       $util = new Utilclass();
//       $IsAuth = $util->isAuthenticated($userID, $authToken);

//       if(!$IsAuth)
//       {

//         return response()->json(['statusCode'=>'0','statusMessage'=>'User Is Not Authorized','Result'=>NULL]);
//       }
// else
// {

    $format = $request->input('content_type');   
    $imageName =$id.$format; 
    $entityBody = file_get_contents('php://input');
    $directory = "/images/CNICImages/";
    $path = base_path()."/public".$directory;
    $data = base64_decode($entityBody);

 file_put_contents($path.$imageName, $data);


$response = $directory.$imageName;

// $cat=Profiles::find($id);
//       $cat->CNIC_Image = $response ;
//       $cat->save();

      DB::table('profiles')
            ->where('id', $id)
            ->update(['CNIC_Image' => $response]);

  return response()->json(['statusCode'=>'1','statusMessage'=>'CNIC Images uploaded','Result'=>$response]);
        }
      
 public function uploadProfileImage(Request $request)
    {
        $userID = $request->input('userID');

        // $id = Profiles::find($userID)->where('userID', '=',$userID)->pluck('id');
         $id = DB::table('profiles')
            ->select()
            ->where('userID', '=', $userID)
            ->pluck('id')
            ->first();
         // return $id; 

        //Get user id from the request
//       $userID = $request->input('userID');

//       //Get authencation token from request
//      $authToken = $_SERVER['HTTP_AUTHORIZATION'];

// if(!$authToken)
// {
//    return response()->json(['statusCode'=>'0','statusMessage'=>'Authorization Token Not Found','Result'=>NULL]);
 
// }
//       $util = new Utilclass();
//       $IsAuth = $util->isAuthenticated($userID, $authToken);

//       if(!$IsAuth)
//       {

//         return response()->json(['statusCode'=>'0','statusMessage'=>'User Is Not Authorized','Result'=>NULL]);
//       }
// else
// {

    $format = $request->input('content_type');   
    $imageName = $id.$format; 
    $entityBody = file_get_contents('php://input');
    $directory = "/images/ProfileImages/";
    $path = base_path()."/public".$directory;
    $data = base64_decode($entityBody);

 file_put_contents($path.$imageName, $data);


$response = $directory.$imageName;
 
      DB::table('profiles')
            ->where('id', $id)
            ->update(['Profile_Image' => $response]);

  return response()->json(['statusCode'=>'1','statusMessage'=>'Profile Image uploaded','Result'=>$response]);
 
        // }      
    }

public function showOrderAgainstUserID(Request $request)
{   
    try 
    {
        // return response('sss');
      $order = $request->input('userID');
      // return $order;



        $tbluserplandays  = DB::table('orders')
                              ->Where('userID', $order)
                              ->get();
 
// return $tbluserplandays;

     $temp = json_encode(['success'=>'true','message'=>'Result Retrieved','Payload'=>$tbluserplandays]);

                         return $temp;
    
} catch (Exception $e) {
    $temp = json_encode(['success'=>'false','message'=>'error','Payload'=>$e]);

        }
    }

}
