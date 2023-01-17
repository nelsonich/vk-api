<?php

namespace App\Controllers;

use System\Controller;
use System\Request;

class HomePageController extends Controller
{
    public function __construct()
    {
        // ...
    }

    function __destruct()
    {
        // ...
    }

    public function index()
    {
        return $this->view('home_page');
    }

    public function getData()
    {
        // Get data
    }

    public function deleteRow(Request $request)
    {
        // Remove row
    }
}
