@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Face Registration') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="text-center mb-4">
                        @if ($hasFaceRegistered)
                            <div class="alert alert-info">
                                You already have a face registered. Registering a new face will replace the existing one.
                            </div>
                        @endif
                        
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
                            <button id="register-btn" class="btn btn-success mx-2" style="display: none;">Register Face</button>
                        </div>
                    </div>
                    
                    <div id="status-container" class="text-center mt-3">
                        <div id="loading" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p>Processing...</p>
                        </div>
                        
                        <div id="success-message" class="alert alert-success" style="display: none;"></div>
                        <div id="error-message" class="alert alert-danger" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/face-api.min.js') }}"></script>
<script src="{{ asset('js/face-capture.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize face capture
        const faceCapture = new FaceCapture({
            videoElement: document.getElementById('video'),
            canvasElement: document.getElementById('canvas'),
            captureButton: document.getElementById('capture-btn'),
            recaptureButton: document.getElementById('recapture-btn'),
            registerButton: document.getElementById('register-btn'),
            resultContainer: document.getElementById('result-container'),
            capturedImage: document.getElementById('captured-image'),
            videoContainer: document.getElementById('video-container'),
            loadingElement: document.getElementById('loading'),
            successMessage: document.getElementById('success-message'),
            errorMessage: document.getElementById('error-message'),
            registerEndpoint: '{{ route('face.registration') }}',
            csrfToken: '{{ csrf_token() }}'
        });
        
        faceCapture.init();
    });
</script>
@endpush
@endsection