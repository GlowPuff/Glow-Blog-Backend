<?php
// respond to preflights
if ( $_SERVER['REQUEST_METHOD'] == 'OPTIONS' )
{
  // return only the headers and not the content
  if ( isset( $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] ) &&
    $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'GET'
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
header( "Access-Control-Allow-Methods: GET" );
header( "Access-Control-Max-Age: 3600" );
header( "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With" );

require_once "../config/core.php";
require_once '../auth/verify.php';
require_once "../SleekDB/SleekDB.php";
include_once '../config/database.php';
include_once '../objects/blogpost.php';
include_once '../objects/user.php';

//read all posts with pagination

$database = new Database();
$db       = $database->getPostDB();
$userDB   = $database->getUserDB();
$blogpost = new BlogPost( $db );
$user     = new User( $userDB );
$page     = 1;
$limit    = 5;
$token    = "";
$slug     = "";

//get the user's email from database
$email = $user->getSettings()['email'];
$limit = $user->getSettings()['limit'];

if ( isset( $_GET['slug'] ) && !empty( $_GET['slug'] ) )
{
  $slug = $_GET['slug'];
}

$result = $blogpost->readPost( $slug );

if ( $result['success'] == true )
{
  http_response_code( 200 );
  echo json_encode( ['post' => $result['post'], 'postCount' => $result['postCount']] );
}
else
{
  http_response_code( 500 );
  echo json_encode( ['message' => 'Error reading pages', 'error' => $result['error']] );
}