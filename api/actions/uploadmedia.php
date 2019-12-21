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
require_once '../utils/thumbnail.php';

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

$target_dir  = realpath( $_SERVER["DOCUMENT_ROOT"] ) . "/gallery/";
$target_file = $target_dir . basename( $_FILES["file"]["name"] );

//Get file extension to check later
$imageFileType = strtolower( pathinfo( $target_file, PATHINFO_EXTENSION ) );

//Make sure file is an image
if ( isset( $_FILES['file']['tmp_name'] ) )
{
  $finfo = finfo_open( FILEINFO_MIME_TYPE ); // return mime type
  $mime  = finfo_file( $finfo, $_FILES["file"]["tmp_name"] );
  finfo_close( $finfo );

  if ( $mime != "image/jpeg" )
  {
    http_response_code( 400 ); //bad request
    echo json_encode( [
      'message' => 'Upload error',
      'error'   => 'This file type is not allowed',
    ] );
    exit;
  }
}
else
{
  http_response_code( 400 ); //bad request
  echo json_encode( [
    'message' => 'Upload error',
    'error'   => 'No files attached',
  ] );
  exit;
}

//Check if file already exists
if ( file_exists( $target_file ) )
{
  http_response_code( 400 ); //bad request
  echo json_encode( [
    'message' => 'Upload error',
    'error'   => 'This file already exists',
  ] );
  exit;
}

if ( move_uploaded_file( $_FILES["file"]["tmp_name"], $target_file ) )
{
  make_thumb( $target_file, $target_dir . "thumbs/" . basename( $_FILES["file"]["name"] ), 125 );
  http_response_code( 200 ); //success
  echo json_encode( ['baseURL' => "http://$_SERVER[HTTP_HOST]/gallery/" . basename( $_FILES["file"]["name"] )] );
  // echo $target_file; //Final URL of uploaded image
}
else
{
  http_response_code( 400 ); //bad request
  echo json_encode( [
    'message' => 'Upload error',
    'error'   => 'There was an error uploading your file',
  ] );
  exit;
}
