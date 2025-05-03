<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_description' => 'required|string',
            'face_image' => 'required|string', // Base64 encoded image
            'voice_sample' => 'required|file|mimes:wav,mp3|max:5000',
            'facial_descriptor' => 'required|string', // JSON string of facial descriptors
        ]);

        $user = Auth::user();

        // Verify biometrics before proceeding with payment
        $biometricsVerified = $this->verifyUserBiometrics(
            $user,
            $request->facial_descriptor,
            $request->file('voice_sample')
        );

        if (!$biometricsVerified) {
            return response()->json([
                'success' => false,
                'message' => 'Biometric verification failed. Payment cancelled.',
            ], 401);
        }

        // Simulate a successful payment
        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully!',
            'amount' => $request->amount,
            'description' => $request->payment_description,
            'date' => now()->format('Y-m-d H:i:s'),
            'transaction_id' => 'TXN' . rand(100000, 999999),
        ]);
    }

    private function verifyUserBiometrics($user, $facialDescriptor, $voiceSample)
    {
        // Get stored biometric data
        $storedFacialDescriptor = json_decode($user->facialData->facial_descriptor, true);

        // Compare facial descriptors
        $currentFacialDescriptor = json_decode($facialDescriptor, true);

        // Simplified facial comparison
        $facialMatch = $this->compareFacialDescriptors($storedFacialDescriptor, $currentFacialDescriptor);

        if (!$facialMatch) {
            return false;
        }

        // Simulate voice match success
        $voiceMatch = true;

        return $voiceMatch;
    }

    private function compareFacialDescriptors($stored, $current)
    {
        $storedArray = is_array($stored) ? $stored : [$stored];
        $currentArray = is_array($current) ? $current : [$current];

        $sum = 0;
        for ($i = 0; $i < count($storedArray); $i++) {
            $sum += pow($storedArray[$i] - $currentArray[$i], 2);
        }

        $distance = sqrt($sum);

        $threshold = 0.6;
        return $distance < $threshold;
    }
}
