<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class MobileAppController extends Controller
{
    public function __invoke()
    {
        return Auth::check()
            ? redirect()->route('admin.index')
            : redirect()->route('login');
    }
}
