<?php

namespace App\Http\Controllers;

use App\Attendance;
use App\User;
use App\Services\FaceRecognition\FaceRecognitionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    protected $faceRecognitionService;

    public function __construct(FaceRecognitionService $faceRecognitionService)
    {
        $this->faceRecognitionService = $faceRecognitionService;
        $this->middleware('auth');
    }

    /**
     * Show the attendance page.
     */
    public function index()
    {
        return view('attendance.index');
    }

    /**
     * Mark attendance with face recognition.
     */
    public function markAttendance(Request $request)
    {
        $this->validate($request, [
            'image' => 'required|string',
        ]);

        $imageBase64 = $request->input('image');

        // Recognize the face
        $user = $this->faceRecognitionService->recognizeFace($imageBase64);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Face not recognized.'
            ], 404);
        }

        // Get today's date
        $today = Carbon::now()->toDateString();
        
        // Check if an attendance record already exists for today
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        $currentTime = Carbon::now()->toTimeString();

        if (!$attendance) {
            // Create a new attendance record (check-in)
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'check_in' => $currentTime,
                'status' => $this->determineStatus($currentTime),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Check-in successful.',
                'attendance' => $attendance
            ]);
        } else if (!$attendance->check_out) {
            // Update existing attendance record (check-out)
            $attendance->update([
                'check_out' => $currentTime
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Check-out successful.',
                'attendance' => $attendance
            ]);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'You have already checked in and out today.',
                'attendance' => $attendance
            ]);
        }
    }

    /**
     * Determine attendance status based on check-in time.
     */
    private function determineStatus($checkInTime)
    {
        // Define the threshold for late check-in (e.g., 9:00 AM)
        $lateThreshold = Carbon::createFromTimeString('09:00:00');
        
        // Convert check-in time to Carbon instance
        $checkIn = Carbon::createFromTimeString($checkInTime);
        
        return $checkIn->gt($lateThreshold) ? 'late' : 'present';
    }

    /**
     * Get attendance history for the authenticated user.
     */
    public function history()
    {
        $user = Auth::user();
        
        $attendances = Attendance::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->paginate(10);
        
        return view('attendance.history', [
            'attendances' => $attendances
        ]);
    }

    /**
     * Admin: Get all attendance records.
     */
    public function adminIndex()
    {
        // Ensure the user is an admin
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }
        
        $attendances = Attendance::with('user')
            ->orderBy('date', 'desc')
            ->paginate(15);
        
        return view('admin.attendance.index', [
            'attendances' => $attendances
        ]);
    }
}