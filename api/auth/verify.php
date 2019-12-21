<?php

include_once '../JWT/BeforeValidException.php';
include_once '../JWT/ExpiredException.php';
include_once '../JWT/SignatureInvalidException.php';
include_once '../JWT/JWT.php';
use \Firebase\JWT\JWT;

//modified code from:
//https: //stackoverflow.com/questions/42098150/how-to-verify-firebase-id-token-with-phpjwt

//I fixed a bug where the Google token cache wasn't being used

function verify_firebase_token( $email, $token )
{
  global $fbProjectId;
  global $JWT_leeway;

  $return = array();
  $userId = $deviceId = "";
  checkKeys();
  $pkeys_raw   = getKeys();
  JWT::$leeway = $JWT_leeway;

  $return['success'] = false;
  $return['error']   = '';

  if ( !empty( $pkeys_raw ) )
  {
    $pkeys = json_decode( $pkeys_raw, true );
    try {
      $decoded = JWT::decode( $token, $pkeys, ["RS256"] );

      if ( !empty( $decoded ) )
      {
        // do all the verifications Firebase says to do as per https://firebase.google.com/docs/auth/admin/verify-id-tokens
        // exp must be in the future
        $exp = $decoded->exp > time();
        // ist must be in the past
        $iat = $decoded->iat < time() + JWT::$leeway;
        // aud must be your Firebase project ID
        $aud = $decoded->aud == $fbProjectId;
        // iss must be "https://securetoken.google.com/<projectId>"
        $iss = $decoded->iss == "https://securetoken.google.com/$fbProjectId";
        // sub must be non-empty and is the UID of the user or device
        $sub = $decoded->sub;
        //$em  = $decoded->email == $email;

        if ( /*$em &&*/ $exp && $iat && $aud && $iss && !empty( $sub ) )
        {
          // Firebase user is confirmed
          // build an array with data for further processing
          $return['success'] = true;
          $return['uid']     = $sub;
          $return['email']   = $decoded->email;
          // $return['debug uid']   = $uid;
          // $return['debug email'] = $email;
        }
        else
        {
          $return['success'] = false;
          $return['error']   = 'Token data failed validation';
          $return['exp:']    = $decoded->exp;
          $return['iat:']    = $decoded->iat;
          $return['aud:']    = $decoded->aud;
          $return['email']   = $decoded->email;

          //DO FURTHER PROCESSING IF YOU NEED TO
          // (if $sub is false you may want to still return the data or even enter the verified user into the database at this point.)
        }

      }

    }
    catch ( Exception $e )
    {
      $return['success'] = false;
      $return['error']   = $e->getMessage();
    }
  }
  else
  {
    $return['success'] = false;
    $return['error']   = 'pkeys_raw is empty';
  }

  return $return;
}

/**
 * Checks whether new keys should be downloaded, and retrieves them, if needed.
 */
function checkKeys()
{
  global $cache_file;

  if ( file_exists( $cache_file ) && filesize( $cache_file ) > 0 )
  {
    $fp = fopen( $cache_file, "r+" );

    if ( flock( $fp, LOCK_SH ) )
    {
      $contents = fread( $fp, filesize( $cache_file ) );

      if ( $contents > time() )
      {
        flock( $fp, LOCK_UN );
      }
      elseif ( flock( $fp, LOCK_EX ) )
      {
        // upgrading the lock to exclusive (write)
        // here we need to revalidate since another process could've got to the LOCK_EX part before this
        if ( fread( $fp, filesize( $cache_file ) ) <= time() )
        {
          refreshKeys( $fp );
        }

        flock( $fp, LOCK_UN );
      }
      else
      {
        throw new \RuntimeException( 'Cannot refresh keys: file lock upgrade error.' );
      }

    }
    else
    {
      // you need to handle this by signaling error
      throw new \RuntimeException( 'Cannot refresh keys: file lock error.' );
    }

    fclose( $fp );
  }
  else
  {
    refreshKeys();
  }
}

/**
 * Downloads the public keys and writes them in a file. This also sets the new cache revalidation time.
 * @param null $fp the file pointer of the cache time file
 */
function refreshKeys( $fp = null )
{
  global $keys_file;
  global $cache_file;

  $ch = curl_init();
  curl_setopt( $ch, CURLOPT_URL, "https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com" );
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
  curl_setopt( $ch, CURLOPT_HEADER, 1 );
  $data        = curl_exec( $ch );
  $header_size = curl_getinfo( $ch, CURLINFO_HEADER_SIZE );
  $headers     = trim( substr( $data, 0, $header_size ) );
  $raw_keys    = trim( substr( $data, $header_size ) );

  // http_response_code( 400 );
  // echo "CURL:".$data;
  // die();

  if ( preg_match( '/age:[ ]+?(\d+)/i', $headers, $age_matches ) === 1 )
  {
    $age = $age_matches[1];

    if ( preg_match( '/cache-control:.+?max-age=(\d+)/i', $headers, $max_age_matches ) === 1 )
    {
      $valid_for = $max_age_matches[1] - $age;
      $fp        = fopen( $cache_file, "w" );
      ftruncate( $fp, 0 );
      fwrite( $fp, "" . ( time() + $valid_for ) );
      fflush( $fp );
      // $fp will be closed outside, we don't have to
      $fp_keys = fopen( $keys_file, "w" );

      if ( flock( $fp_keys, LOCK_EX ) )
      {
        fwrite( $fp_keys, $raw_keys );
        fflush( $fp_keys );
        flock( $fp_keys, LOCK_UN );
      }

      fclose( $fp_keys );
    }

  }
  else
  {
    http_response_code( 500 );
    echo json_encode( ['message' => 'refreshKeys', 'error' => 'Could not get firebase public keys'] );
    die();
  }
}

/**
 * Retrieves the downloaded keys.
 * This should be called anytime you need the keys (i.e. for decoding / verification).
 * @return null|string
 */
function getKeys()
{
  global $keys_file;
  $fp   = fopen( $keys_file, "r" );
  $keys = null;

  if ( flock( $fp, LOCK_SH ) )
  {
    $keys = fread( $fp, filesize( $keys_file ) );
    flock( $fp, LOCK_UN );
  }

  fclose( $fp );
  return $keys;
}
