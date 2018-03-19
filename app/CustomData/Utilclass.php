<?php 

namespace App\CustomData;
use DB; 


class Utilclass 
{

	public function isAuthenticated($id, $token)
	{


		 //Check if this is a valid token
      $myToken = DB::table('users')->where('id', $id)->value('token');

      if($token != $myToken)
      {
        return false;
      }
      else
      {
		    return true;
	    }

	}

	public function sendPushNotification($userID,$title,$body)
	{

$FcmToken = DB::table('individualfcmtokens')->where('userID', $userID)->pluck('FcmToken')->first();

$notification = array("title" => $title, "body" => $body, "sound" => "default");
$temp = array("to" => $FcmToken, "notification" => $notification);
$url = "https://fcm.googleapis.com/fcm/send";

    $client = new \GuzzleHttp\Client([
    'headers' => [ 'Content-Type' => 'application/json',
    'Authorization' => "key=AAAATWX-L58:APA91bEGdeOe6ggkjXiQ9422xx16QsNphpz4eQG4RPlYSZrW5qdNslCu_Ne4-6FRD1YChuXbWveqE5PpBpjPtxJmsYA6qdDQfbPwSguqFjvXQb3yaqYzTG1Q5uotMYlSeEAsNh8bXVMU"
     ]
]);


if (empty($FcmToken)) 
{
 return;
}
$response = $client->post($url,[ 'body' => json_encode($temp) ]);


return $response;


	
	}	

	public function sendPushNotificationToGroup($GroupName,$title,$body)
	{

$FcmToken = DB::table('groupfcmtokens')->where('GroupName',$GroupName)->pluck('GroupToken')->first();


$notification = array("title" => $title, "body" => $body, "sound" => "default");
$temp = array("to" => $FcmToken, "notification" => $notification);
$url = "https://fcm.googleapis.com/fcm/send";

    $client = new \GuzzleHttp\Client([
    'headers' => [ 'Content-Type' => 'application/json',
    'Authorization' => "key=AAAATWX-L58:APA91bEGdeOe6ggkjXiQ9422xx16QsNphpz4eQG4RPlYSZrW5qdNslCu_Ne4-6FRD1YChuXbWveqE5PpBpjPtxJmsYA6qdDQfbPwSguqFjvXQb3yaqYzTG1Q5uotMYlSeEAsNh8bXVMU"
     ]
]);

$response = $client->post($url, [ 'body' => json_encode($temp) ]);
  
return $response;


	
	}		


}