<?php

namespace App\Http\Controllers;

use App\Models\FacialData;
use App\Models\User;
use App\Models\VoiceData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BiometricRegistrationController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.biometric-register');
    }

    public function register(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'face_image' => 'required|string', // Base64 encoded image
            'voice_sample' => 'required|file|mimes:wav,mp3|max:5000',
            'facial_descriptor' => 'required|string', // JSON string of facial descriptors
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // Process and store face image
        $faceImage = $request->face_image;
        $faceImage = str_replace('data:image/jpeg;base64,', '', $faceImage);
        $faceImage = str_replace(' ', '+', $faceImage);
        $faceImageName = 'face_' . $user->id . '_' . time() . '.jpg';
        Storage::disk('public')->put('faces/' . $faceImageName, base64_decode($faceImage));

        // Store facial data
        FacialData::create([
            'user_id' => $user->id,
            'facial_descriptor' => $request->facial_descriptor,
            'face_image_path' => 'faces/' . $faceImageName
        ]);

        // Process and store voice sample
        $voiceSample = $request->file('voice_sample');
        $voiceSamplePath = $voiceSample->store('voices', 'public');

        // Extract voice features (this would be done with a voice recognition library)
        // For demonstration, we'll just use a placeholder
        $voiceFeatures = json_encode(['placeholder' => 'voice_features']);

        // Store voice data
        VoiceData::create([
            'user_id' => $user->id,
            'voice_features' => $voiceFeatures,
            'voice_sample_path' => $voiceSamplePath
        ]);

        // Automatically log the user in
        auth()->login($user);

        return response()->json(['success' => true, 'redirect' => route('home')]);
    }
}