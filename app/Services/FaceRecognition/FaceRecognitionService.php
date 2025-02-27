<?php

namespace App\Services\FaceRecognition;

use App\FaceEncoding;
use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FaceRecognitionService
{
    protected $faceDetectionService;
    protected $faceEncodingService;
    protected $faceComparisonService;

    public function __construct(
        FaceDetectionService $faceDetectionService,
        FaceEncodingService $faceEncodingService,
        FaceComparisonService $faceComparisonService
    ) {
        $this->faceDetectionService = $faceDetectionService;
        $this->faceEncodingService = $faceEncodingService;
        $this->faceComparisonService = $faceComparisonService;
    }

    /**
     * Register a face for a user
     * 
     * @param User $user
     * @param string $imageBase64
     * @return bool
     */
    public function registerFace(User $user, $imageBase64)
    {
        try {
            // Detect face in the image
            $faceDetected = $this->faceDetectionService->detectFace($imageBase64);
            
            if (!$faceDetected) {
                return false;
            }
            
            // Generate face encoding
            $encoding = $this->faceEncodingService->generateEncoding($imageBase64);
            
            if (empty($encoding)) {
                return false;
            }
            
            // Save image to storage
            $imagePath = $this->saveImage($imageBase64, $user->id);
            
            // Save face encoding to database
            FaceEncoding::create([
                'user_id' => $user->id,
                'encoding' => json_encode($encoding),
                'image_path' => $imagePath
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Face registration error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Recognize a face
     * 
     * @param string $imageBase64
     * @return User|null
     */
    public function recognizeFace($imageBase64)
    {
        try {
            // Detect face in the image
            $faceDetected = $this->faceDetectionService->detectFace($imageBase64);
            
            if (!$faceDetected) {
                return null;
            }
            
            // Generate face encoding for the input image
            $inputEncoding = $this->faceEncodingService->generateEncoding($imageBase64);
            
            if (empty($inputEncoding)) {
                return null;
            }
            
            // Get all face encodings from the database
            $faceEncodings = FaceEncoding::all();
            
            // Find the matching face
            foreach ($faceEncodings as $faceEncoding) {
                $dbEncoding = json_decode($faceEncoding->encoding, true);
                
                $match = $this->faceComparisonService->compareFaces($inputEncoding, $dbEncoding);
                
                if ($match) {
                    return User::find($faceEncoding->user_id);
                }
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Face recognition error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Save the face image to storage
     * 
     * @param string $imageBase64
     * @param int $userId
     * @return string
     */
    private function saveImage($imageBase64, $userId)
    {
        // Remove the data URI part
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageBase64));
        
        // Generate a filename
        $filename = 'face_' . $userId . '_' . time() . '.png';
        
        // Define the path
        $path = 'public/faces/' . $filename;
        
        // Save the image
        Storage::put($path, $imageData);
        
        return $path;
    }
}