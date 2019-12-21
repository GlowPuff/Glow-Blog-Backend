<?php
require_once "core.php";

class Database
{
    public $postStore;
    public $userStore;

    public function getPostDB()
    {
        global $dataDir;
        //create blog post store
        $this->postStore = \SleekDB\SleekDB::store('posts', $dataDir);

        return $this->postStore;
    }

    public function getUserDB()
    {
        global $dataDir;
        //create user store
        $this->userStore = \SleekDB\SleekDB::store('user', $dataDir);

        return $this->userStore;
    }
}
