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

// $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

$page         = 1;
$limit        = 1;
$actual_link  = "http://$_SERVER[HTTP_HOST]/gallery/thumbs/";
$thumbs       = scandir( "../../gallery/thumbs/" );
$returnObject = array();

if ( isset( $_GET['page'] ) && !empty( $_GET['page'] ) )
{
  $page = $_GET['page'];
  $page = max( 1, $page );
}

if ( isset( $_GET['limit'] ) && !empty( $_GET['limit'] ) )
{
  $limit = $_GET['limit'];
  $limit = max( 1, $limit );
}

//Check success
if ( $thumbs )
{
  foreach ( $thumbs as &$value )
  {
    if ( $value != "." && $value != ".." )
    {
      $fname = "../../gallery/thumbs/" . $value;
      $value = $actual_link . $value;
      $count = count( $thumbs ) - 2;
      array_push( $returnObject, array( "source" => $value, "date" => filemtime( $fname ) ) );
    }
  }

  $returnObject = array_slice( $returnObject, ( $page - 1 ) * $limit, $limit );

  http_response_code( 200 );
  print json_encode( ['imagecount' => $count, 'sources' => $returnObject] );
  exit;
}
else
{
  http_response_code( 500 );
  print json_encode( ['message' => 'Could not get thumbnails', 'error' => 'Error while scanning directory'] );
  exit;
}
?>