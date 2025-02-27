<?php

namespace App\Http\Controllers;

use App\User;
use App\Services\FaceRecognition\FaceRecognitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FaceRecognitionController extends Controller
{
    protected $faceRecognitionService;

    public function __construct(FaceRecognitionService $faceRecognitionService)
    {
        $this->faceRecognitionService = $faceRecognitionService;
        $this->middleware('auth');
    }

    /**
     * Show the face registration form.
     */
    public function showRegistrationForm()
    {
        $user = Auth::user();
        
        return view('face-registration.index', [
            'hasFaceRegistered' => $user->hasFaceRegistered()
        ]);
    }

    /**
     * Register a face.
     */
    public function registerFace(Request $request)
    {
        $this->validate($request, [
            'image' => 'required|string',
        ]);

        $user = Auth::user();
        $imageBase64 = $request->input('image');

        $success = $this->faceRecognitionService->registerFace($user, $imageBase64);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Face registered successfully.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to register face.'
        ], 400);
    }

    /**
     * Recognize a face.
     */
    public function recognizeFace(Request $request)
    {
        $this->validate($request, [
            'image' => 'required|string',
        ]);

        $imageBase64 = $request->input('image');

        $user = $this->faceRecognitionService->recognizeFace($imageBase64);

        if ($user) {
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Face not recognized.'
        ], 404);
    }
}