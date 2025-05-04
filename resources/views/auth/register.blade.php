@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register with Biometrics') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}" id="registration-form" enctype="multipart/form-data">
                        @csrf

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('E-Mail Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="facial-recognition" class="col-md-4 col-form-label text-md-end">{{ __('Facial Recognition') }}</label>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <video id="video" width="320" height="240" autoplay></video>
                                        <canvas id="canvas" width="320" height="240" style="display: none;"></canvas>
                                        <p id="face-status">Waiting for camera access...</p>
                                        <button type="button" id="capture-btn" class="btn btn-primary">Capture Face</button>
                                        <input type="hidden" id="face_image" name="face_image">
                                        <input type="hidden" id="facial_descriptor" name="facial_descriptor">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="voice-recognition" class="col-md-4 col-form-label text-md-end">{{ __('Voice Recognition') }}</label>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <p id="recording-status">Click "Start Recording" and read the phrase.</p>
                                        <button type="button" id="record-btn" class="btn btn-primary">Start Recording</button>
                                        <button type="button" id="stop-btn" class="btn btn-danger" disabled>Stop Recording</button>
                                        <audio id="audio-playback" controls style="display: none; margin-top: 10px; width: 100%;"></audio>
                                        <input type="file" id="voice_sample_blob" name="voice_sample" style="display: none;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary" id="register-btn" disabled>
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script src="https://www.webrtc-experiment.com/RecordRTC.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', async function() {
        // Load face-api.js models
        await loadFaceApiModels();
        
        // Set up facial recognition
        setupFacialRecognition();
        
        // Set up voice recording
        setupVoiceRecording();
        
        // Form submission
        setupFormSubmission();
    });
    
    async function loadFaceApiModels() {
        const MODEL_URL = '/models';
        
        try {
            await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
            await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
            await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
            console.log('Face-api models loaded successfully');
        } catch (error) {
            console.error('Error loading face-api models:', error);
            document.getElementById('face-status').textContent = 'Error loading facial recognition models';
        }
    }
    
    function setupFacialRecognition() {
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const captureBtn = document.getElementById('capture-btn');
        const faceStatus = document.getElementById('face-status');
        
        // Access webcam
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => {
                video.srcObject = stream;
                faceStatus.textContent = 'Camera ready. Please look at the camera and press "Capture Face"';
            })
            .catch(err => {
                console.error('Error accessing camera:', err);
                faceStatus.textContent = 'Error accessing camera: ' + err.message;
            });
        
        // Capture face button
        captureBtn.addEventListener('click', async () => {
            if (!video.srcObject) {
                faceStatus.textContent = 'Camera not available.';
                return;
            }
            
            faceStatus.textContent = 'Processing...';
            
            try {
                // Draw video frame to canvas
                const context = canvas.getContext('2d');
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                // Detect face in the captured image
                const detections = await faceapi.detectSingleFace(canvas, 
                    new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceDescriptor();
                
                if (!detections) {
                    faceStatus.textContent = 'No face detected. Please try again.';
                    return;
                }
                
                // Store the face image and descriptor
                document.getElementById('face_image').value = canvas.toDataURL('image/jpeg');
                document.getElementById('facial_descriptor').value = JSON.stringify(Array.from(detections.descriptor));
                
                faceStatus.textContent = 'Face captured successfully!';
                
                // Stop the video stream
                video.srcObject.getTracks().forEach(track => track.stop());
                video.style.display = 'none';
                
                // Display the captured image
                canvas.style.display = 'block';
                
                checkFormCompletion();
            } catch (error) {
                console.error('Error capturing face:', error);
                faceStatus.textContent = 'Error capturing face: ' + error.message;
            }
        });
    }
    
    function setupVoiceRecording() {
        const recordBtn = document.getElementById('record-btn');
        const stopBtn = document.getElementById('stop-btn');
        const recordingStatus = document.getElementById('recording-status');
        const audioPlayback = document.getElementById('audio-playback');
        
        let recorder;
        let audioStream;
        
        recordBtn.addEventListener('click', async () => {
            try {
                audioStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                recorder = new RecordRTC(audioStream, {
                    type: 'audio',
                    mimeType: 'audio/webm',
                    recorderType: RecordRTC.StereoAudioRecorder,
                    numberOfAudioChannels: 1,
                    timeSlice: 1000,
                    desiredSampRate: 16000
                });
                
                recorder.startRecording();
                recordingStatus.textContent = 'Recording... Please say: "My voice is my passport, verify me."';
                
                recordBtn.disabled = true;
                stopBtn.disabled = false;
            } catch (error) {
                console.error('Error accessing microphone:', error);
                recordingStatus.textContent = 'Error accessing microphone: ' + error.message;
            }
        });
        
        stopBtn.addEventListener('click', () => {
            if (recorder) {
                recorder.stopRecording(() => {
                    const blob = recorder.getBlob();
                    
                    // Create a URL for the audio blob
                    const audioURL = URL.createObjectURL(blob);
                    audioPlayback.src = audioURL;
                    audioPlayback.style.display = 'block';
                    
                    // Store the blob for form submission
                    const voiceSampleField = document.getElementById('voice_sample_blob');
                    
                    // Create a File object from the blob
                    const voiceFile = new File([blob], 'voice_sample.webm', { 
                        type: 'audio/webm',
                        lastModified: new Date().getTime() 
                    });
                    
                    // Create a new FileList object (not directly possible, so we use DataTransfer)
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(voiceFile);
                    voiceSampleField.files = dataTransfer.files;
                    
                    recordingStatus.textContent = 'Voice recorded successfully!';
                    
                    // Stop the audio stream
                    audioStream.getTracks().forEach(track => track.stop());
                    
                    recordBtn.disabled = false;
                    stopBtn.disabled = true;
                    
                    checkFormCompletion();
                });
            }
        });
    }
    
    function setupFormSubmission() {
        const form = document.getElementById('registration-form');
        
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            
            const formData = new FormData(form);
            
            try {
                const response = await fetch('/register', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert('Registration failed: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error submitting form:', error);
                alert('Error submitting form: ' + error.message);
            }
        });
    }
    
    function checkFormCompletion() {
        const faceImage = document.getElementById('face_image').value;
        const voiceSample = document.getElementById('voice_sample_blob').files;
        const registerBtn = document.getElementById('register-btn');
        
        if (faceImage && voiceSample && voiceSample.length > 0) {
            registerBtn.disabled = false;
        }
    }
</script>
@endsection