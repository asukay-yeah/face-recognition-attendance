<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Attendance Kiosk</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 0;
            margin: 0;
            height: 100vh;
            overflow: hidden;
        }
        
        .kiosk-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .kiosk-header {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .kiosk-header h1 {
            color: #3490dc;
            font-weight: bold;
            margin: 0;
            font-size: 2.2rem;
        }
        
        .kiosk-clock {
            font-size: 2.5rem;
            margin: 10px 0;
            font-weight: 300;
            color: #333;
        }
        
        .kiosk-date {
            font-size: 1.2rem;
            color: #666;
        }
        
        .main-content {
            display: flex;
            flex: 1;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .left-panel {
            width: 65%;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .right-panel {
            width: 35%;
        }
        
        .card-panel {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            height: 100%;
        }
        
        .video-container {
            position: relative;
            height: 70%;
            background-color: #000;
            border-radius: 10px;
            overflow: hidden;
        }
        
        #video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        #canvas {
            display: none;
        }
        
        .status-panel {
            height: 30%;
            position: relative;
            overflow: hidden;
        }
        
        .status-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .status-header h4 {
            margin: 0;
            color: #3490dc;
        }
        
        .status-light {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #28a745;
            display: inline-block;
            margin-right: 5px;
            animation: blink 2s infinite;
        }
        
        @keyframes blink {
            0% { opacity: 0.4; }
            50% { opacity: 1; }
            100% { opacity: 0.4; }
        }
        
        #status-message {
            font-size: 1.1rem;
            margin-bottom: 15px;
        }
        
        .recognition-result {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
            display: none;
            animation: slideUp 0.3s ease-out;
        }
        
        @keyframes slideUp {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }
        
        .recognition-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .recognition-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #3490dc;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.5rem;
            color: white;
        }
        
        .user-details {
            flex: 1;
        }
        
        .user-name {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .user-status {
            font-size: 1rem;
        }
        
        .recent-logs {
            height: 100%;
            overflow-y: auto;
            padding-right: 5px;
        }
        
        .log-item {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            background-color: #f8f9fa;
            border-left: 4px solid #3490dc;
            transition: all 0.3s ease;
        }
        
        .log-item.new {
            animation: highlightNew 2s ease;
        }
        
        @keyframes highlightNew {
            0% { background-color: #d4edda; }
            100% { background-color: #f8f9fa; }
        }
        
        .log-time {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        .log-action {
            font-size: 0.95rem;
        }
        
        .log-action strong {
            color: #3490dc;
        }
        
        .processing-indicator {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 100;
            display: none;
            text-align: center;
            color: white;
        }
        
        .processing-indicator p {
            margin-top: 10px;
            font-size: 1.1rem;
        }
        
        .kiosk-footer {
            text-align: center;
            padding: 10px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .kiosk-footer p {
            margin: 0;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }
        
        /* Responsiveness */
        @media (max-width: 992px) {
            .main-content {
                flex-direction: column;
            }
            
            .left-panel, .right-panel {
                width: 100%;
            }
            
            .video-container {
                height: 400px;
            }
        }
    </style>
</head>
<body>
    <div class="kiosk-container">
        <!-- Header with Clock and Date -->
        <div class="kiosk-header">
            <h1>Face Recognition Attendance System</h1>
            <div class="kiosk-clock" id="clock">00:00:00</div>
            <div class="kiosk-date" id="date">Loading date...</div>
        </div>
        
        <!-- Main Content Area -->
        <div class="main-content">
            <!-- Left Panel: Camera and Status -->
            <div class="left-panel">
                <!-- Video Feed -->
                <div class="video-container card-panel">
                    <video id="video" autoplay muted></video>
                    <canvas id="canvas"></canvas>
                    <div class="processing-indicator" id="processing">
                        <div class="spinner-border text-light" role="status">
                            <span class="sr-only">Processing...</span>
                        </div>
                        <p>Processing...</p>
                    </div>
                </div>
                
                <!-- Status Panel -->
                <div class="status-panel card-panel">
                    <div class="status-header">
                        <h4>
                            <span class="status-light"></span>
                            Face Detection Status
                        </h4>
                    </div>
                    
                    <p id="status-message">Stand in front of the camera to check-in/check-out</p>
                    
                    <div id="recognition-result" class="recognition-result">
                        <div id="user-info" class="user-info">
                            <div class="user-avatar" id="user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="user-details">
                                <div class="user-name" id="user-name"></div>
                                <div class="user-status" id="user-status"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Panel: Activity Log -->
            <div class="right-panel">
                <div class="card-panel">
                    <div class="status-header">
                        <h4>Recent Activity</h4>
                    </div>
                    <div class="recent-logs" id="recent-logs">
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-history fa-3x mb-3"></i>
                            <p>No recent activity</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="kiosk-footer">
            <p>© {{ date('Y') }} Face Recognition Attendance System | <a href="{{ route('login') }}" class="text-decoration-none">Admin Login</a></p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="{{ asset('js/face-api.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize variables
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const processingIndicator = document.getElementById('processing');
            const statusMessage = document.getElementById('status-message');
            const recognitionResult = document.getElementById('recognition-result');
            const userAvatar = document.getElementById('user-avatar');
            const userName = document.getElementById('user-name');
            const userStatus = document.getElementById('user-status');
            const recentLogs = document.getElementById('recent-logs');
            
            let stream = null;
            let isProcessing = false;
            let detectionInterval = null;
            let lastProcessedTime = 0;
            const processCooldown = 5000; // 5 seconds cooldown between processing
            
            // Update clock and date
            function updateClock() {
                const now = new Date();
                const timeString = now.toLocaleTimeString();
                const dateString = now.toLocaleDateString('en-US', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
                
                document.getElementById('clock').textContent = timeString;
                document.getElementById('date').textContent = dateString;
            }
            
            // Initialize clock
            updateClock();
            setInterval(updateClock, 1000);
            
            // Load face-api.js models
            async function loadModels() {
                statusMessage.textContent = 'Loading face recognition models...';
                
                try {
                    await faceapi.nets.ssdMobilenetv1.loadFromUri('/models/face-api');
                    await faceapi.nets.faceLandmark68Net.loadFromUri('/models/face-api');
                    await faceapi.nets.faceRecognitionNet.loadFromUri('/models/face-api');
                    
                    statusMessage.textContent = 'Models loaded successfully. Stand in front of the camera to check-in/check-out.';
                    startWebcam();
                } catch (error) {
                    console.error('Error loading models:', error);
                    statusMessage.textContent = 'Error loading face recognition models. Please refresh the page.';
                }
            }
            
            // Start webcam
            async function startWebcam() {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            width: { ideal: 640 },
                            height: { ideal: 480 },
                            facingMode: 'user'
                        },
                        audio: false
                    });
                    
                    video.srcObject = stream;
                    
                    video.onloadedmetadata = () => {
                        // Start face detection after webcam is ready
                        startFaceDetection();
                    };
                } catch (error) {
                    console.error('Error accessing webcam:', error);
                    statusMessage.textContent = 'Error accessing webcam. Please make sure your camera is connected and you have granted permission.';
                }
            }
            
            // Start face detection
            function startFaceDetection() {
                detectionInterval = setInterval(async () => {
                    // Skip if already processing or cooldown hasn't passed
                    if (isProcessing || Date.now() - lastProcessedTime < processCooldown) {
                        return;
                    }
                    
                    try {
                        // Detect face
                        const detections = await faceapi.detectSingleFace(video);
                        
                        if (detections) {
                            // Face detected, process attendance
                            processAttendance();
                        }
                    } catch (error) {
                        console.error('Error detecting face:', error);
                    }
                }, 1000); // Check every second
            }
            
            // Process attendance
            async function processAttendance() {
                // Set processing flag
                isProcessing = true;
                lastProcessedTime = Date.now();
                
                // Show processing indicator
                processingIndicator.style.display = 'block';
                statusMessage.textContent = 'Processing... Please wait.';
                
                try {
                    // Capture image from video
                    const context = canvas.getContext('2d');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    context.drawImage(video, 0, 0, canvas.width, canvas.height);
                    const imageData = canvas.toDataURL('image/png');
                    
                    // Send to server for processing
                    const response = await fetch('{{ route("kiosk.process") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            image: imageData
                        })
                    });
                    
                    const data = await response.json();
                    
                    // Handle response
                    if (data.success) {
                        showSuccessResult(data);
                        addActivityLog(data);
                    } else {
                        showErrorResult(data.message);
                    }
                } catch (error) {
                    console.error('Error processing attendance:', error);
                    showErrorResult('Error processing attendance. Please try again.');
                } finally {
                    // Hide processing indicator
                    processingIndicator.style.display = 'none';
                    
                    // Reset processing flag after cooldown
                    setTimeout(() => {
                        isProcessing = false;
                    }, processCooldown);
                }
            }
            
            // Show success result
            function showSuccessResult(data) {
                // Update status message
                statusMessage.textContent = 'Face recognized successfully!';
                
                // Set user info
                userName.textContent = data.user.name;
                
                // Set user status based on action
                let statusText = '';
                let statusIcon = '';
                
                if (data.action === 'check_in') {
                    statusText = `Checked in at ${formatTime(data.attendance.check_in)}`;
                    statusIcon = '<i class="fas fa-sign-in-alt"></i>';
                } else if (data.action === 'check_out') {
                    statusText = `Checked out at ${formatTime(data.attendance.check_out)}`;
                    statusIcon = '<i class="fas fa-sign-out-alt"></i>';
                } else {
                    statusText = 'Already checked in and out today';
                    statusIcon = '<i class="fas fa-info-circle"></i>';
                }
                
                userStatus.textContent = statusText;
                userAvatar.innerHTML = statusIcon;
                
                // Show the result
                recognitionResult.className = 'recognition-result recognition-success';
                recognitionResult.style.display = 'block';
                
                // Hide after 5 seconds
                setTimeout(() => {
                    recognitionResult.style.display = 'none';
                    statusMessage.textContent = 'Stand in front of the camera to check-in/check-out';
                }, 5000);
            }
            
            // Show error result
            function showErrorResult(message) {
                statusMessage.textContent = message;
                
                // Hide after 3 seconds
                setTimeout(() => {
                    statusMessage.textContent = 'Stand in front of the camera to check-in/check-out';
                }, 3000);
            }
            
            // Add activity log
            function addActivityLog(data) {
                // Remove "No recent activity" message if present
                const emptyStateElement = recentLogs.querySelector('.text-center.text-muted');
                if (emptyStateElement) {
                    recentLogs.innerHTML = '';
                }
                
                // Create log item
                const logItem = document.createElement('div');
                logItem.className = 'log-item new';
                
                const now = new Date();
                const currentTime = now.toLocaleTimeString();
                
                let actionText = '';
                if (data.action === 'check_in') {
                    actionText = `<strong>${data.user.name}</strong> checked in at ${formatTime(data.attendance.check_in)}`;
                } else if (data.action === 'check_out') {
                    actionText = `<strong>${data.user.name}</strong> checked out at ${formatTime(data.attendance.check_out)}`;
                } else {
                    actionText = `<strong>${data.user.name}</strong> already checked in/out today`;
                }
                
                logItem.innerHTML = `
                    <div class="log-time">${currentTime}</div>
                    <div class="log-action">${actionText}</div>
                `;
                
                // Add log to the top
                recentLogs.insertBefore(logItem, recentLogs.firstChild);
                
                // Limit to 20 logs
                if (recentLogs.children.length > 20) {
                    recentLogs.removeChild(recentLogs.lastChild);
                }
                
                // Remove 'new' class after animation
                setTimeout(() => {
                    logItem.classList.remove('new');
                }, 2000);
            }
            
            // Format time
            function formatTime(timeString) {
                const date = new Date(`2000-01-01T${timeString}`);
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
            
            // Load models to start the process
            loadModels();
        });
    </script>
</body>
</html>