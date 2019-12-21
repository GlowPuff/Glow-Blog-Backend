<?php
//path to database store folder
$dataDir = "../storeDB";
//the project ID of your firebase app
$fbProjectId = 'YOUR_PROJECT_ID';
// the downloaded firebase public keys
$keys_file = "../securetoken.json";
// the next time the system has to revalidate the keys
$cache_file     = "../pkeys.cache";
$JWT_leeway     = 120;
$approvedOrigin = 'https://localhost:8080';

date_default_timezone_set( 'America/New_York' );