<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redirect;
use App\User;
use App\profiles;
use App\Order;
use Illuminate\Http\Request;
use App\Http\Requests\StoreBlogPost;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\Individualfcmtokens;
use App\CustomData\Utilclass;
// use mail;
use App\resources\emails\mailExample;
use App\config\services;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Mail\Mailer;
use DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Testing\Fakes\MailFake;
use Mailgun\Mailgun;
use Carbon\Carbon;
use App\Comission;
use GuzzleHttp\Client;
use Exception;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use App\GroupFcmTokens;
use FCMGroup;
use FCM;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use App\Notifications\Contracts\FirebaseNotification;
use LaravelFCM\Request\GroupRequest;

//use Auth;

class UserController extends Controller

{
    protected function jwt($myUser) {
        $payload = [
            'iss' => "lumen-jwt", // Issuer of the token
            'sub' => $myUser, // Subject of the token
            'iat' => time(), // Time when JWT was issued. 
            'exp' => time() + 60*60 // Expiration time
        ];
        
        // As you can see we are passing `JWT_SECRET` as the second parameter that will 
        // be used to decode the token in the future.
        return JWT::encode($payload, env('JWT_SECRET'));
    } 


     public function store(Request $request)
    {
        if (User::where('Email', '=', Input::get('Email'))->exists()) 
    {
        return response()->json(['statusCode'=>'0','statusMessage'=>'Email Already Exists','Result'=>NULL]);              

    }
     else
        { 
          
         $user = new User();

       $user->Email=$request->input('Email');
       $user->Password=$request->input('Password');


        $token = bin2hex(openssl_random_pseudo_bytes(25));

        DB::table('users')->where('Email', Input::get('Email'))->update(array('token' => $token));

        $user->token = $token;

       $user->save();
       $collection = collect(['id' => $user->id, 'Email' =>  $user->Email, 'Token' =>  $user->token]);

       $collection->toJson();

        //..............Creation of Profiles............//

              $Profiles = new Profiles();

             $Profiles->userID=$user->id;
            
             $Profiles->save();



       // return response()->json([
       //          'token' => $this->jwt($collection)
       //      ], 200);

           return response()->json(['statusCode'=>'1','statusMessage'=>'Account Created','Result'=>$collection]);

        }
    }

    public function ResendVerificationEmail(Request $request)
    {   

        $users = DB::table('users')
            ->select()
            ->where('Email', '=', Input::get('Email'))
            ->first();
        if (!$users) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'No such Email Account exists', 'Result' => NULL]);
         }

        $token = bin2hex(openssl_random_pseudo_bytes(25));

        DB::table('users')->where('Email', Input::get('Email'))->update(array('token' => $token));

        $users->token = $token;


        $id = $users->id;
        $Role = $users->Role;


        $this->basic_ConfirmationEmail($users);

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Check Your Email For Account Verification', 'Result' => $this->CreateSignUPResult($users->Email, $users->Role, $users->id)]);

    }

    public function confirm(Request $request, $id)
    {

        $user = User::where('token', '=', $id)->first();
        if (!$user) {
            return response('This link has expired.');
        } else {
            $status = 1;
            DB::table('users')->where('token', $id)->update(array('Account_Status' => $status));

            $user->save();
            if ($user->Role == 2) {
                return redirect('https://seller.baqalah.biz/index.php/sellerController/login_seller?success=1');
            } else if ($user->Role == 3) {
                return redirect('https://baqalah.biz/index.php/buyerController/login_buyer?success=1');
            }
        }
    }

    public function ForgotPassWord(Request $request)
    {
        $user = new User();

        $user = $request->input('Email');


        $users = DB::table('users')
            ->select()
            ->where('Email', '=', $user)
            ->first();
        if (!$users) {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Email Account is not found in our system.', 'Result' => NULL]);
        }
        // return response($users);

        $concatedToken = $users->token;
        $concatedToken .= ':';
        $concatedToken .= $users->id;
        $role = $users->Role;
        /*if (!$users) {
            return response('Mail Address Not Exist');
        }*/
        Mail::send('emails.forgotPass', ['tokenVal' => $concatedToken, 'role'=>$role], function ($message) use ($user) {
            $message->from('support@baqalah.biz', 'Baqalah Support');
            // $message->setBody("<a href='baqalah.biz/index.php?/buyerController/change_password'>click here</a>",'text/html');
            $message->to($user, '')->subject('Reset Password Request');
        });

        return response()->json(['statusCode' => '1', 'statusMessage' => 'Email Sent to Your Mailing Address', 'Result' => NULL]);
    }


    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */

    public function show($id)

    {   
        
        // return response('sss');

        $admin = User::find($id);

        return response()->json(['statusCode' => '1', 'statusMessage' => 'User Retrieved', 'Result' => $admin]);
    }


    public function showAllUsers(Request $request)

    {   
         $get_token = $request->input('token');

        $admin = DB::table('users')
            ->select()
            ->where('token', '=', $get_token)
            ->first();
      if (!$admin) {
   return response()->json(['statusCode' => '0', 'statusMessage' => 'No such Admin found', 'Result' => $admin]);
        }

           if ($admin->Role == 1){
              $admin = \App\User::all();
            return response()->json(['statusCode' => '1', 'statusMessage' => 'showing All Users', 'Result' => $admin]);      
           }
           else{
            return response()->json(['statusCode' => '0', 'statusMessage' => 'You do not have access for this', 'Result' => $admin]);      
               } 

         // return $admin->Role;


          $admin = User::find($id);

        return response()->json(['statusCode' => '1', 'statusMessage' => 'User Retrieved', 'Result' => $admin]);
    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */


    public function basic_ConfirmationEmail($user)
    {
        try{
            Mail::send('emails.kkk', ["data" => $user], function ($message) use ($user) {
                //$message->from('baqalah1@gmail.com', 'Baqalah.biz');
                $message->from('support@baqalah.biz', 'Baqalah Support');

                $message->to($user->Email, '')->subject('Account Verification');
            });
        }
        catch (\Exception $e){
            return false;
        }

        return true;

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $admin = User::find($id);
        $admin->delete();
        return response()->json('Deleted');
    }


    public function doLogin()   
      
    {
      $token = "";
      $Email = Input::get("Email");
      $Password = Input::get("Password");

      $token = bin2hex(openssl_random_pseudo_bytes(40));
     // $token->save();
                $myUser = DB::table('users')
                ->select('id','Email','token')
                     ->where('Email', '=', $Email)
                     ->where('Password', '=', $Password)
                     ->first();

if ($myUser)
{
    $myUser->token = $token;
    DB::table('users')->where('Email', $Email)->update(array('token' => $token));


           // return response()->json([
           //      'token' => $this->jwt($myUser)
           //  ], 200);


    return response()->json(['statusCode'=>'1','statusMessage'=>'Logged In','Result'=>$myUser]);

 }
 else
   {
      return response()->json(['statusCode'=>'0','statusMessage'=>'Email or Password Incorrect','Result'=>NULL]);
    }
                      
  }




    public function IsFacebookToken($token)
    {

        $url = 'https://graph.facebook.com/me?access_token=';
        $url .= $token; 

    $client = new \GuzzleHttp\Client(); 
    try {
        $res = $client->get($url);
    } catch (RequestException $e) 
    {
        //echo Psr7\str($e->getRequest());
        if ($e->hasResponse()) {
           // echo Psr7\str($e->getResponse());
        }
        return NULL;
    }

    return $res;

    }


public function IsGmailToken($token)
{

    $url = 'https://www.googleapis.com/oauth2/v3/tokeninfo?access_token=';
    $url .= $token; 

$client = new \GuzzleHttp\Client(); 
try {
    $res = $client->get($url);
} catch (RequestException $e) 
{
    //echo Psr7\str($e->getRequest());
    if ($e->hasResponse()) {
       // echo Psr7\str($e->getResponse());
    }
    return NULL;
}

return $res;

}

public function IsTwitterToken($token)
{

    $url = 'https://api.twitter.com/1/account/verify_credentials.json';
    $url .= $token; 

$client = new \GuzzleHttp\Client(); 
try {
    $res = $client->get($url);
} catch (RequestException $e) 
{
    //echo Psr7\str($e->getRequest());
    if ($e->hasResponse()) {
       // echo Psr7\str($e->getResponse());
    }
    return NULL;
}

return $res;

}
public function External_Login(Request $request)
    {

        //Get Input parameters from the service URL

        $Email = $request->input('Email');
        $Provider = $request->input('Provider');
        $token = $request->input('token'); // for facebook only
        $Role = $request->input('Role');

        //if($Provider=='FaceBook')

        if ($Provider == 'facebook' || $Provider == 'FACEBOOK') {
            // return 'Facebook';

            // $temp = $this->IsFacebookToken($token);
            
            // return $temp;
            if (!($this->IsFacebookToken($token))) {

                return response()->json(['statusCode' => '0', 'statusMessage' => 'Facebook Token is not valid', 'Result' => NULL]);
            }
        }  
        if ($Provider == 'gmail' || $Provider == 'GMAIL') {
            // return 'Gmail';
            if (!($this->IsGmailToken($token))) {
                return response()->json(['statusCode' => '0', 'statusMessage' => 'Gmail Token is not valid', 'Result' => NULL]);
            }
        }     
       if ($Provider == 'Twitter' || $Provider == 'TWITTER') {
            // return 'Twitter';
            if (!($this->IsTwitterToken($token))) {
                return response()->json(['statusCode' => '0', 'statusMessage' => 'Twitter Token is not valid', 'Result' => NULL]);
            }
        }
        else 
        {
            $token = bin2hex(openssl_random_pseudo_bytes(25)); // If provider is not facebook then generate token
        }

            $myUser = '';
            //If user does not exirt, create the user
            if (!(User::where('Email', '=', $Email)->exists())) {
                $admin = new User();
                $admin->Email = $Email;
                $admin->Password = $admin->UUID;

                $admin->token = $token;

                $admin->save();

             $Profiles = new Profiles();

             $Profiles->userID=$admin->id;
            
             $Profiles->save();

                $myUser = $admin;

            } 

            else   //If user exists
            {
                //Get user
                $myUser = DB::table('users')
                   ->join('profiles','profiles.userID','=','users.id')
                   ->where('Email', '=', $Email)
                    // ->select('users.id','Email','token')
                    ->first();
            }

                if ($myUser) {
                    $myUser->token = $token;
                    DB::table('users')->where('Email', $Email)->update(array('token' => $token)); // updating table with newly retrieved token from facebook
                    // $this->saveUUIDandUserID($myUser->id, $UUID, $myUser->Role);
                  
             return response()->json(['statusCode' => '1', 'statusMessage' => 'Logged In', 'Result' => $myUser]);
                } else {
            return response()->json(['statusCode' => '0', 'statusMessage' => 'Email or Password Incorrect', 'Result' => NULL]);
            }

        
    }


    public function uploadProfileImage(Request $request,$id)
    {

   // return response('hetgdf');s

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
{

    $format = $request->input('content_type');   
    $imageName =$id.$format; 
    $entityBody = file_get_contents('php://input');
    $directory = "/images/ProfileImages/";
    $path = base_path()."/public".$directory;
    $data = base64_decode($entityBody);

 file_put_contents($path.$imageName, $data);


$response = $directory.$imageName;

$cat=User::find($id);
      $cat->Profile_Images = $response ;
      $cat->save();

  return response()->json(['statusCode'=>'1','statusMessage'=>'Profile Image uploaded','Result'=>$response]);
 
        }      


    }

 public function uploadCNICImage(Request $request,$id)
    {

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

$cat=User::find($id);
      $cat->CNIC_Image  = $response ;
      $cat->save();

  return response()->json(['statusCode'=>'1','statusMessage'=>'CNIC Images uploaded','Result'=>$response]);
        }
}