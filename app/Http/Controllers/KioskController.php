<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FaceRecognition\FaceRecognitionService;
use App\Attendance;
use Carbon\Carbon;

class KioskController extends Controller
{
    protected $faceRecognitionService;

    public function __construct(FaceRecognitionService $faceRecognitionService)
    {
        $this->faceRecognitionService = $faceRecognitionService;
        // tidak memerlukanan middleware auth karena ini akan diakses public
    }

    // show kiosk interface for attendance
    public function index()
    {
        return view('kiosk.index');
    }

    // proses mark attendance dan face recognition
    public function prosesAttendance(Request $request)
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
                'action' => 'check-in',
                'message' => 'Welcome, ' . $user->name . '! You have checked in at ' . Carbon::parse($currentTime)->format('h:i A'),
                'user' => $user,
                'attendance' => $attendance,
            ]);
        } else if (!$attendance->check_out) {
            // Update the existing attendance record (check-out)
            $attendance->update([
                'check_out' => $currentTime,
            ]);

            return response()->json([
                'success' => true,
                'action' => 'check-out',
                'message' => 'Goodbye, ' . $user->name . '! You have checked out at ' . Carbon::parse($currentTime)->format('h:i A'),
                'user' => $user,
                'attendance' => $attendance,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'action' => 'alredy_checked',
                'message' => 'Hello, ' . $user->name . '! You have already checked in and checked out at ' . Carbon::parse($attendance->check_in)->format('h:i A') . ' and ' . Carbon::parse($attendance->check_out)->format('h:i A'),
                'user' => $user,
                'attendance' => $attendance,
            ], 400);
        }
    }

    private function determineStatus($checkInTime)
    {
        // Define the threshold for late check-in (e.g., 9:00 AM)
        $lateThreshold = Carbon::createFromTimeString('09:00:00');
        
        // Convert check-in time to Carbon instance
        $checkIn = Carbon::createFromTimeString($checkInTime);
        
        return $checkIn->gt($lateThreshold) ? 'late' : 'present';
    }

}
