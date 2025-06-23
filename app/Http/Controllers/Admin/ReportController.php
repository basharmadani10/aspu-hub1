<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{

    public function show(Report $report)
    {
        $report->load('reporter', 'reportable');
        return view('admin.reports.show', ['report' => $report]);
    }


    public function deleteContent(Report $report)
    {

        $report->reportable->delete();


        $report->update(['status' => 'resolved']);

        return redirect()->route('admin.dashboard')->with('success', 'Content has been deleted successfully.');
    }


    public function banUser(Report $report)
    {

        $userToBan = $report->reportable->user;


        $userToBan->update(['is_blocked' => true]);


        $report->reportable->delete();


        $report->update(['status' => 'resolved']);

        return redirect()->route('admin.dashboard')->with('success', 'User has been banned and content deleted.');
    }

 
    public function resolve(Report $report)
    {
        $report->update(['status' => 'resolved']);

        return redirect()->route('admin.dashboard')->with('success', 'Report has been marked as resolved.');
    }
}
