<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    // @desc Show all users job listings
    //@route GET /dashboard
    public function index(): View
    {
        //get the login user
        $user = Auth::user(); 

        //get the user listings
        $jobs = Job::where('user_id', $user->id)->with('applicants')->get();
        
        // dd($jobs);

        //return dashboard.index and pass the data $user and $jobs
        return view('dashboard.index', compact('user', 'jobs'));
    }

}
