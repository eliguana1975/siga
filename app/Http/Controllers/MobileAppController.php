<?php

namespace App\Http\Controllers;

class MobileAppController extends Controller
{
    public function __invoke()
    {
        return view('mobile.app');
    }
}
