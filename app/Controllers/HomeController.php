<?php

namespace App\Controllers;


use Etus\Framework\Http\Response;

class HomeController extends BaseController
{

    public function index(): Response
    {
        return view('welcome', [
            'title' => 'Welcome to Etus Framework',
            'name'  => 'EPHRAITECH UNIFIED SOLUTIONS Framework',
        ]);
    }

    public function framework_info(): Response
    {
        return view('framework');
    }
}
