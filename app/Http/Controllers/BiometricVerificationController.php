<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class BiometricVerificationController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.biometric-login');
    }

    public function login(Request $request)
    {
        // Step 1: Validate email and password
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt to find the user
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        // At this point, email and password are correct
        // Return user info for biometric verification
        return response()->json([
            'success' => true,
            'user_id' => $user->id,
            'message' => 'Please complete biometric verification',
        ]);
    }

    public function verifyBiometrics(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'face_image' => 'required|string', // Base64 encoded image
            'voice_sample' => 'required|file|mimes:wav,mp3|max:5000',
            'facial_descriptor' => 'required|string', // JSON string of facial descriptors
        ]);

        $user = User::findOrFail($request->user_id);
        
        // Verify facial match
        $storedFacialDescriptor = json_decode($user->facialData->facial_descriptor, true);
        $currentFacialDescriptor = json_decode($request->facial_descriptor, true);
        
        $facialMatch = $this->compareFacialDescriptors($storedFacialDescriptor, $currentFacialDescriptor);
        
        if (!$facialMatch) {
            return response()->json([
                'success' => false,
                'message' => 'Facial verification failed',
            ], 401);
        }
        
        // Verify voice match (this would use a voice recognition library)
        // For demonstration, we'll assume it's successful
        $voiceMatch = true;
        
        if (!$voiceMatch) {
            return response()->json([
                'success' => false,
                'message' => 'Voice verification failed',
            ], 401);
        }
        
        // Both biometrics matched, log in the user
        Auth::login($user);
        
        return response()->json([
            'success' => true,
            'redirect' => route('home'),
        ]);
    }
    
    // This is a simplified implementation - in a real application
    // you would use a more sophisticated matching algorithm
    private function compareFacialDescriptors($stored, $current)
    {
        // For demonstration purposes, we'll use Euclidean distance
        // between the descriptor vectors and a threshold
        
        // Convert the JSON arrays to PHP arrays
        $storedArray = is_array($stored) ? $stored : [$stored];
        $currentArray = is_array($current) ? $current : [$current];
        
        // Calculate Euclidean distance
        $sum = 0;
        for ($i = 0; $i < count($storedArray); $i++) {
            $sum += pow($storedArray[$i] - $currentArray[$i], 2);
        }
        
        $distance = sqrt($sum);
        
        // If distance is below threshold, consider it a match
        $threshold = 0.6; // This value would need tuning
        return $distance < $threshold;
    }
}