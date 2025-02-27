<?php

namespace App\Http\Controllers;

use App\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get today's attendance
        $today = Carbon::now()->toDateString();
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();
        
        // Get recent attendance history
        $recentAttendances = Attendance::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();
        
        // Check if user has registered face
        $hasFaceRegistered = $user->hasFaceRegistered();
        
        return view('dashboard.index', [
            'user' => $user,
            'todayAttendance' => $todayAttendance,
            'recentAttendances' => $recentAttendances,
            'hasFaceRegistered' => $hasFaceRegistered
        ]);
    }
}