<?php

class User
{
  private $dbConn;

  public function __construct( $db )
  {
    $this->dbConn = $db;
  }

  public function getSiteData()
  {
    $res = $this->dbConn->fetch();
    $res = ['success' => true,
      'heroTitle'       => $res[0]['heroTitle'],
      'heroSubtitle'    => $res[0]['heroSubtitle'],
      'blogTitle'       => $res[0]['blogTitle'],
    ];
    return $res;
  }

  public function create( $email, $token )
  {
    //first check if a user already exists
    if ( count( $this->dbConn->fetch() ) > 0 )
    {
      return ['success' => false, 'error' => 'An account already exists'];
    }

    //verify new user with logged in firebase JWT token
    $verify = verify_firebase_token( $email, $token );

    if ( $verify['success'] == false )
    {
      return ['success' => false, 'error' => 'Could not verify token: ' . $verify['error']];
    }

    //create new user
    $userObject = [
      'email' => $email,
      'uid'   => $verify['uid'],
    ];
    $res = $this->dbConn->insert( $userObject );

    return ['success' => true];
  }

  public function deleteUser()
  {
    $this->dbConn->deleteStore();
  }

  public function getSettings()
  {
		//NOTE: 'email' is no longer being used for JWT verification, so just return empty string for $res['email']
    $res = $this->dbConn->fetch();
    $res = [
      'email'        => "",//$res[0]['email'],
      'limit'        => $res[0]['limit'],
      'blogTitle'    => $res[0]['blogTitle'],
      'heroTitle'    => $res[0]['heroTitle'],
      'heroSubtitle' => $res[0]['heroSubtitle'],
    ];
    return $res;
  }

  public function setSettings( $settingsObject )
  {
    // $settingsObject = [
    //   'heroSubtitle' => 'test',
    // ];
    $res = $this->dbConn->where( '_id', '=', '1' )->update( $settingsObject );
  }
}
