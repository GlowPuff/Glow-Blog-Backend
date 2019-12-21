<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../auth/authenticate.php';
require_once "../SleekDB/SleekDB.php";
include_once '../config/database.php';
include_once '../objects/blogpost.php';

//Read and return ALL posts

$database = new Database();
$db       = $database->getPostDB();
$blogpost = new BlogPost($db);

$posts = $blogpost->read();

if (count($posts) > 0)
{
    // print_r($posts); //debug ONLY
    http_response_code(200);
    //return json encoded post data
    echo json_encode($posts);

}
else
{
    http_response_code(404);
    echo json_encode(["message" => "No posts found"]);
}
