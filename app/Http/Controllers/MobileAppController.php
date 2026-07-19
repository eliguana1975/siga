<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class MobileAppController extends Controller
{
    public function __invoke()
    {
        if (Auth::check()) {
            return redirect()->route('admin.index');
        }

        return view('mobile.app');
    }
}
