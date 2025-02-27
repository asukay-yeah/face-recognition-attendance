class FaceRecognition {
    constructor(config) {
        this.video = config.videoElement;
        this.canvas = config.canvasElement;
        this.captureBtn = config.captureButton;
        this.recaptureBtn = config.recaptureButton;
        this.verifyBtn = config.verifyButton;
        this.resultContainer = config.resultContainer;
        this.capturedImage = config.capturedImage;
        this.videoContainer = config.videoContainer;
        this.loadingElement = config.loadingElement;
        this.recognitionResult = config.recognitionResult;
        this.userInfo = config.userInfo;
        this.attendanceInfo = config.attendanceInfo;
        this.successMessage = config.successMessage;
        this.errorMessage = config.errorMessage;
        this.attendanceEndpoint = config.attendanceEndpoint;
        this.csrfToken = config.csrfToken;
        
        this.stream = null;
        this.capturedData = null;
    }
    
    async init() {
        try {
            // Load face-api.js models
            await this.loadModels();
            
            // Start the webcam
            await this.startWebcam();
            
            // Add event listeners
            this.addEventListeners();
        } catch (error) {
            console.error('Error initializing face recognition:', error);
            this.showError('Failed to initialize camera. Please ensure your camera is connected and you have granted permission to access it.');
        }
    }
    
    async loadModels() {
        this.showLoading('Loading face recognition models...');
        
        // Load face detection models
        await faceapi.nets.ssdMobilenetv1.loadFromUri('/models/face-api');
        await faceapi.nets.faceLandmark68Net.loadFromUri('/models/face-api');
        await faceapi.nets.faceRecognitionNet.loadFromUri('/models/face-api');
        
        this.hideLoading();
    }
    
    async startWebcam() {
        this.stream = await navigator.mediaDevices.getUserMedia({
            video: {
                width: { ideal: 640 },
                height: { ideal: 480 },
                facingMode: 'user'
            },
            audio: false
        });
        
        this.video.srcObject = this.stream;
        
        return new Promise((resolve) => {
            this.video.onloadedmetadata = () => {
                resolve();
            };
        });
    }
    
    addEventListeners() {
        // Capture button
        this.captureBtn.addEventListener('click', () => this.captureFace());
        
        // Recapture button
        this.recaptureBtn.addEventListener('click', () => this.recapture());
        
        // Verify button
        this.verifyBtn.addEventListener('click', () => this.markAttendance());
    }
    
    async captureFace() {
        this.showLoading('Detecting face...');
        
        // Draw the video frame to the canvas
        const context = this.canvas.getContext('2d');
        context.drawImage(this.video, 0, 0, this.canvas.width, this.canvas.height);
        
        // Get the image data from the canvas
        this.capturedData = this.canvas.toDataURL('image/png');
        
        // Detect faces in the captured image
        const detections = await faceapi.detectSingleFace(this.video);
        
        if (!detections) {
            this.showError('No face detected. Please ensure your face is clearly visible and try again.');
            this.hideLoading();
            return;
        }
        
        // Show the captured image
        this.capturedImage.src = this.capturedData;
        this.videoContainer.style.display = 'none';
        this.resultContainer.style.display = 'block';
        this.captureBtn.style.display = 'none';
        this.recaptureBtn.style.display = 'inline-block';
        this.verifyBtn.style.display = 'inline-block';
        
        this.hideLoading();
    }
    
    recapture() {
        // Hide the captured image and show the video again
        this.resultContainer.style.display = 'none';
        this.videoContainer.style.display = 'block';
        this.recaptureBtn.style.display = 'none';
        this.verifyBtn.style.display = 'none';
        this.captureBtn.style.display = 'inline-block';
        
        // Hide recognition result
        this.recognitionResult.style.display = 'none';
        
        // Clear any previous messages
        this.clearMessages();
    }
    
    async markAttendance() {
        this.showLoading('Verifying identity and marking attendance...');
        
        try {
            // Send the captured image to the server
            const response = await fetch(this.attendanceEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({
                    image: this.capturedData
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(data.message);
                
                // Show user and attendance info
                this.displayUserInfo(data);
                
                // Show recognition result
                this.recognitionResult.style.display = 'block';
            } else {
                this.showError(data.message);
            }
        } catch (error) {
            console.error('Error marking attendance:', error);
            this.showError('Failed to mark attendance. Please try again.');
        } finally {
            this.hideLoading();
        }
    }
    
    displayUserInfo(data) {
        if (data.user) {
            this.userInfo.innerHTML = `
                <p><strong>Name:</strong> ${data.user.name}</p>
                <p><strong>Email:</strong> ${data.user.email}</p>
            `;
        }
        
        if (data.attendance) {
            const attendance = data.attendance;
            let status = '';
            
            switch (attendance.status) {
                case 'present':
                    status = '<span class="badge badge-success">Present</span>';
                    break;
                case 'late':
                    status = '<span class="badge badge-warning">Late</span>';
                    break;
                default:
                    status = '<span class="badge badge-secondary">' + attendance.status + '</span>';
            }
            
            this.attendanceInfo.innerHTML = `
                <p><strong>Date:</strong> ${attendance.date}</p>
                <p><strong>Status:</strong> ${status}</p>
                <p><strong>Check-in:</strong> ${attendance.check_in || 'N/A'}</p>
                <p><strong>Check-out:</strong> ${attendance.check_out || 'N/A'}</p>
            `;
        }
    }
    
    showLoading(message = 'Processing...') {
        this.loadingElement.querySelector('p').textContent = message;
        this.loadingElement.style.display = 'block';
    }
    
    hideLoading() {
        this.loadingElement.style.display = 'none';
    }
    
    showSuccess(message) {
        this.successMessage.textContent = message;
        this.successMessage.style.display = 'block';
        this.errorMessage.style.display = 'none';
    }
    
    showError(message) {
        this.errorMessage.textContent = message;
        this.errorMessage.style.display = 'block';
        this.successMessage.style.display = 'none';
    }
    
    clearMessages() {
        this.successMessage.style.display = 'none';
        this.errorMessage.style.display = 'none';
    }
    
    stopWebcam() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
        }
    }
}