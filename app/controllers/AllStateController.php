<?php

class AllStateController extends \BaseController
{
    public function __construct()
    {
        if (Config::get('app.production')) {
            echo "Something cool is going to be here soon.";
            die();
        }
    }
    public function index()
    {
        return View::make('web.allstate');
    }
}
?>