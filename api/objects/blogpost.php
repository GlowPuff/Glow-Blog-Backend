<?php

class BlogPost
{
  private $dbConn;

  public $postID;
  public $timestamp;
  public $publishedAt;
  public $archiveDate;
  // public $timeslugstamp;
  public $slug;
  public $title;
  public $author;
  public $blogpost;
  public $featureImage;
  public $description;
  public $isDraft;
  public $tags;

  public function __construct( $db )
  {
    $this->dbConn = $db;
  }

  public function read()
  {
    $posts = $this->dbConn->fetch();
    return $posts;
  }

  public function update( $data )
  {
    $postObject = [
      // "postID"       => $unique, //$data->postID,
      // "timestamp"    => time(), //$data->timestamp,
      // "publishedAt"  => date( "F j, Y" ), //$data->publishedAt,
      // "archiveDate"  => date( "F Y" ), //only used for Archive search term
      //"timeslugstamp" => $data->timeslugstamp,
      "slug"         => $data->slug,
      "title"        => $data->title,
      // "author"       => $data->author,
      "blogpost"     => $data->blogpost,
      "featureImage" => $data->featureImage,
      "description"  => $data->description,
      // "isDraft"      => $data->isDraft,
      "tags"         => $data->tags,
    ];
    $retval = $this->dbConn->where( 'postID', '=', $data->postID )->update( $postObject );

    if ( $retval )
    {
      $res = ['success' => true];
    }
    else
    {
      $res = ['success' => false, 'error' => 'There was an error updating the post to the database'];
    }

    return $res;
  }

  public function create( $data, $isUpdate = false )
  {
    $unique = uniqid( '', true );
    $unique = str_replace( ".", "", $unique );

    //Make sure we are creating a post with an ID that is NOT used already
    $dupe = $this->dbConn->where( 'postID', '=', $unique )->fetch();
    if ( $dupe )
    {
      $res = ['success' => false, 'error' => 'This post already exists on the server.'];
      return $res;
    }

		//TODO: Make sure we are creating a post with a new, unused SLUG
		

    $postObject = [
      "postID"       => $unique, //$data->postID,
      "timestamp"    => time(), //$data->timestamp,
      "publishedAt"  => date( "F j, Y" ), //$data->publishedAt,
      "archiveDate"  => date( "F Y" ), //only used for Archive search term
      //"timeslugstamp" => $data->timeslugstamp,
      "slug"         => $data->slug,
      "title"        => $data->title,
      "author"       => "",//$data->author,
      "blogpost"     => $data->blogpost,
      "featureImage" => $data->featureImage,
      "description"  => $data->description,
      "isDraft"      => $data->isDraft,
      "tags"         => $data->tags,
    ];
    $retval = $this->dbConn->insert( $postObject );

    if ( $retval )
    {
      $res = ['success' => true];
    }
    else
    {
      $res = ['success' => false, 'error' => 'There was an error saving the post to the database'];
    }

    return $res;
  }

  public function delete( $postID )
  {
    //check if post exists
    $post = $this->dbConn->where( 'postID', '=', $postID )->fetch();
    if ( count( $post ) > 0 )
    {
      $this->dbConn->where( 'postID', '=', $postID )->delete();

      return ['success' => true];
    }
    else
    {
      return ['success' => false, 'error' => 'Post ' . $postID . ' not found in database'];
    }
  }

  //returns all post data in a string array as"Month Year"
  public function getArchiveData()
  {
    $allposts = $this->dbConn->fetch();
    $filtered = [];

    foreach ( $allposts as $post )
    {
      $filtered[$post["archiveDate"]] = 1;
    }

    if ( count( $allposts ) > 0 )
    {
      $res = ['success' => true, 'archive' => $filtered];
    }
    else
    {
      $res = ['success' => true, 'archive' => []];
    }

    return $res;

  }

  public function searchArchive( $month, $year )
  {
    $allposts  = $this->dbConn->fetch();
    $postCount = count( $allposts );
    $filtered  = [];

    if ( $postCount > 0 )
    {
      $filtered = $this->dbConn->where( "archiveDate", "=", $month . " " . $year )->fetch();

      $res = ['success' => true, 'postCount' => count( $filtered ), 'posts' => $filtered];
    }
    else
    {
      $res = ['success' => true, 'postCount' => 0, 'posts' => []];
    }

    return $res;
  }

  public function searchTag( $page, $limit, $queryTag )
  {
    $allposts  = $this->dbConn->fetch();
    $postCount = count( $allposts );
    $filtered  = [];

    if ( $postCount > 0 )
    {
      foreach ( $allposts as $post )
      {
        if ( $post["tags"] !== null && in_array( $queryTag, $post["tags"] ) )
        {
          array_push( $filtered, $post );
        }
      }

      $postCount = count( $filtered );

      if ( $postCount > 0 )
      {
        $res = ['success' => true, 'postCount' => $postCount, 'posts' => $filtered];
      }
      else
      {
        $res = ['success' => false, 'error' => 'Error fetching posts'];
      }
    }
    else
    {
      $res = ['success' => true, 'postCount' => 0, 'posts' => []];
    }

    return $res;
  }

  public function readPost( $slug )
  {
    $posts = $this->dbConn->where( "slug", "=", $slug )->fetch();

    if ( $posts )
    {
      if ( count( $posts ) > 0 )
      {
        $res = ['success' => true, 'postCount' => count( $posts ), 'post' => $posts[0]];
      }
      else
      {
        $res = ['success' => true, 'postCount' => 0, 'posts' => []];
      }
    }
    else
    {
      $res = ['success' => false, 'error' => 'Error fetching posts'];
    }

    return $res;
  }

  public function readPaging( $page, $limit )
  {
    $postCount = count( $this->dbConn->fetch() );

    //get paginated results, sorted by publish date
    if ( $postCount > 0 )
    {
      $posts = $this->dbConn->orderBy( 'desc', 'timestamp' )->skip(  ( $page - 1 ) * $limit )->limit( $limit )->fetch();

      if ( $posts )
      {
        $res = ['success' => true, 'postCount' => $postCount, 'posts' => $posts];
      }
      else
      {
        $res = ['success' => false, 'error' => 'Error fetching posts'];
      }
    }
    else
    {
      $res = ['success' => true, 'postCount' => 0, 'posts' => []];
    }
    return $res;
  }
}
