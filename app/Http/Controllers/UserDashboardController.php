<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserDashboardController extends Controller
{

    public function userDashboardInfo()
    {
        return response()->json(['hello world'], 200);
    }
}
