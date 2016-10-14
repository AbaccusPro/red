<?php

namespace App\Http\Controllers;

// use Guzzle\Service\Client;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    public function getLocation(Request $request)
    {
        $location = str_replace(' ', '+', $request->location);

        $map_url = 'http://www.google.com/maps/place/'.$location;

        return redirect($map_url);
    }
}
