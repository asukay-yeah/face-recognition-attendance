@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Mark Attendance') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="text-center mb-4">
                        <div id="video-container" class="mb-3">
                            <video id="video" width="400" height="300" autoplay muted></video>
                            <canvas id="canvas" width="400" height="300" style="display: none;"></canvas>
                        </div>
                        
                        <div id="result-container" class="mb-3" style="display: none;">
                            <img id="captured-image" width="400" height="300" />
                        </div>
                        
                        <div class="d-flex justify-content-center">
                            <button id="capture-btn" class="btn btn-primary mx-2">Capture</button>
                            <button id="recapture-btn" class="btn btn-secondary mx-2" style="display: none;">Recapture</button>
                            <button id="verify-btn" class="btn btn-success mx-2" style="display: none;">Mark Attendance</button>
                        </div>
                    </div>
                    
                    <div id="status-container" class="text-center mt-3">
                        <div id="loading" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p>Processing...</p>
                        </div>
                        
                        <div id="recognition-result" class="mt-3" style="display: none;">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Recognition Result</h5>
                                    <div id="user-info"></div>
                                    <div id="attendance-info" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="success-message" class="alert alert-success mt-3" style="display: none;"></div>
                        <div id="error-message" class="alert alert-danger mt-3" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/face-api.min.js') }}"></script>
<script src="{{ asset('js/face-recognition.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize face recognition
        const faceRecognition = new FaceRecognition({
            videoElement: document.getElementById('video'),
            canvasElement: document.getElementById('canvas'),
            captureButton: document.getElementById('capture-btn'),
            recaptureButton: document.getElementById('recapture-btn'),
            verifyButton: document.getElementById('verify-btn'),
            resultContainer: document.getElementById('result-container'),
            capturedImage: document.getElementById('captured-image'),
            videoContainer: document.getElementById('video-container'),
            loadingElement: document.getElementById('loading'),
            recognitionResult: document.getElementById('recognition-result'),
            userInfo: document.getElementById('user-info'),
            attendanceInfo: document.getElementById('attendance-info'),
            successMessage: document.getElementById('success-message'),
            errorMessage: document.getElementById('error-message'),
            attendanceEndpoint: '{{ route('attendance') }}',
            csrfToken: '{{ csrf_token() }}'
        });
        
        faceRecognition.init();
    });
</script>
@endpush
@endsection