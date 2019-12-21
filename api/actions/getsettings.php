<?php
require_once "../config/core.php";

// respond to preflights
if ( $_SERVER['REQUEST_METHOD'] == 'OPTIONS' )
{
  // simple origin check
  if ( isset( $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] ) &&
    $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'GET'
    && isset( $_SERVER['HTTP_ORIGIN'] ) )
  //&& $_SERVER['HTTP_ORIGIN'] == $approvedOrigin )
  //&& is_approved( $_SERVER['HTTP_ORIGIN'] ) )
  {
    header( 'Access-Control-Allow-Origin: *');// . $approvedOrigin );
    header( 'Access-Control-Allow-Headers: Content-Type,X-Requested-With, Authorization' );
  }
  else
  {
    echo 'Unauthorized access:' . $_SERVER['HTTP_ORIGIN'];
    die();
  }

  exit;
}

header( "Access-Control-Allow-Origin: *");// . $approvedOrigin );
header( "Content-Type: application/json; charset=UTF-8" );
header( "Access-Control-Allow-Methods: GET" );
header( "Access-Control-Max-Age: 3600" );
header( "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With" );

require_once '../auth/verify.php';
require_once "../SleekDB/SleekDB.php";
include_once '../config/database.php';
include_once '../objects/blogpost.php';
include_once '../objects/user.php';

//get the User object's data

$database = new Database();
$userDB   = $database->getUserDB();
$user     = new User( $userDB );
$token    = "";

//NOTE: This is now OBSOLETE and unnecessary.  The JWT validation method (verify_firebase_token) no longer needs the user's email
//get the user's email from database
$email = $user->getSettings()['email'];

//get the token from the Authorization header
foreach ( getallheaders() as $name => $value )
{
  if ( $name == 'Authorization' )
  {
    $token = explode( ' ', $value )[1];
  }
}

//verify the token
$verify = verify_firebase_token( $email, $token );
if ( $verify['success'] == false )
{
  http_response_code( 401 );
  echo json_encode( [
    'message' => 'Could not verify token',
    'error'   => $verify['error'],
  ] );
  die();
}

$result = $user->getSettings();

http_response_code( 200 );
echo json_encode( ['settings' => $result] );