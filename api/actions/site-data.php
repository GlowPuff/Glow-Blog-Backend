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
    http_response_code( 200 );
    header( 'Access-Control-Allow-Origin: *' );
    header( 'Access-Control-Allow-Headers: Content-Type,X-Requested-With, Authorization' );
    header( 'Access-Control-Allow-Credentials: true' );
  }

  exit;
}

header( "Access-Control-Allow-Origin: *" );
header( "Content-Type: application/json; charset=UTF-8" );
header( "Access-Control-Allow-Methods: GET" );
header( "Access-Control-Max-Age: 3600" );
header( "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With" );

require_once "../config/core.php";
require_once "../SleekDB/SleekDB.php";
include_once '../config/database.php';
include_once '../objects/user.php';

$database = new Database();
$userDB   = $database->getUserDB();
$user     = new User( $userDB );
$token    = "";

$res = $user->getSiteData();

if ( $res['success'] )
{
  http_response_code( 200 );
  echo json_encode( ['title' => $res['heroTitle'], 'subtitle' => $res['heroSubtitle'], 'blogTitle' => $res['blogTitle']] );
}
else
{
  http_response_code( 500 );
  echo json_encode( [
    'message' => "Error retrieving site data",
    'error'   => $res['error'],
  ] );
}