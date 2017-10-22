<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
  function index()
  {
    $greetings = Greeting::query()->orderBy('created_at', 'desc')->limit(20)->get();
    return view('home')->with(['greetings'=>$greetings]);
  }
}
