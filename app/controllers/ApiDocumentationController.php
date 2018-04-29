<?php
/**
 * Created by PhpStorm.
 * User: saumya
 * Date: 29/8/17
 * Time: 8:07 PM
 */

class ApiDocumentationController extends BaseController
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
        return View::make('web.api_reference');
    }

}