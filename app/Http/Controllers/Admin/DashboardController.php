<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Report;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DashboardController extends Controller
{

    public function index()
    {

        $postReportsCount = Report::where('reportable_type', 'App\Models\Post')->where('status', 'pending')->count();
        $commentReportsCount = Report::where('reportable_type', 'App\Models\Comment')->where('status', 'pending')->count();


        $supervisedGroupsCount = Auth::user()->managedCommunities()->count();
        $pendingRequestsCount = Subject::where('status', 'pending')->count();

        $recentReports = Report::where('status', 'pending')->with('reportable', 'reporter')->latest()->take(5)->get();


        return view('admin.dashboard', [
            'postReportsCount' => $postReportsCount,
            'commentReportsCount' => $commentReportsCount,
            'pendingRequestsCount' => $pendingRequestsCount,
            'supervisedGroupsCount' => $supervisedGroupsCount,
            'recentReports' => $recentReports,
        ]);
    }
}
