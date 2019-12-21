<?php
// respond to preflights
if ( $_SERVER['REQUEST_METHOD'] == 'OPTIONS' )
{
  // return only the headers and not the content
  if ( isset( $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] ) &&
    $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'POST'
    && isset( $_SERVER['HTTP_ORIGIN'] ) )
  //&& is_approved($_SERVER['HTTP_ORIGIN']))
  {
    header( 'Access-Control-Allow-Origin: *' );
    header( 'Access-Control-Allow-Headers: Content-Type,X-Requested-With, Authorization' );
  }

  exit;
}

header( "Access-Control-Allow-Origin: *" );
header( "Content-Type: application/json; charset=UTF-8" );
header( "Access-Control-Allow-Methods: POST" );
header( "Access-Control-Max-Age: 3600" );
header( "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With" );

require_once "../config/core.php";
require_once '../auth/verify.php';
require_once "../SleekDB/SleekDB.php";
include_once '../config/database.php';
include_once '../objects/user.php';

//read all posts with pagination

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

$settings = json_decode( file_get_contents( "php://input" ) );

$user->setSettings( $settings );

http_response_code( 200 );