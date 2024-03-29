<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Mockery\Exception;

class UserController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed'
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password'])
        ]);

        $token = $user->createToken('token')->plainTextToken;


        $response = [
            'user' => $user,
            'token' => $token
        ];

        $user->save();

        return response()->json($response, 201);
    }

    public function login(Request $request): JsonResponse
    {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        // Check the user email in the database
        $user = User::where('email', $fields['email'])->first();

        // Check user's password
        if(!$user || !Hash::check($fields['password'], $user->password)){
            return response()->json([
                'message' => 'The credentials do not match our records'
            ], 401);
        }

        $token = $user->createToken('token')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];
        return response()->json($response, 201);
    }

    public function logout(Request $request): JsonResponse
    {
        try{
            $user = $request->user;
            $userId = $user->id;
            $token = DB::table('personal_access_tokens')
                ->where('tokenable_id', $userId)
                ->latest()
                ->first();
            if ($token) {
                DB::table('personal_access_tokens')->where('id', $token->id)->delete();
            }
            return response()->json([
                'message' => 'Logged out'
            ]);
        } catch (Exception) {
            return response()->json([
                'error' => 'Token not provided'
            ], 400);
        }
    }


    public function passwordReset(Request $request): JsonResponse
    {
        try {
            $fields = $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $user = User::where('email', $fields['email'])->first();

            // Generate a new reset token
            $resetToken = Str::random(10);

            // Update the user's record with the new reset token
            $user->reset_token = $resetToken;
            $user->save();

            // Send the email to the user's email address with the reset token
            Mail::send('emails.password_reset', ['user' => $user], function($message) use ($user) {
                $message->to($user->email);
                $message->subject('Password Reset Request');
            });

            return response()->json([
                'message' => 'A password reset token has been sent to your email address.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while processing your request: ' . $e->getMessage()
            ], 500);
        }
    }


    public function passwordUpdate(Request $request): JsonResponse
    {
        $fields = $request->validate([
            'reset_token' => 'required|string',
            'password' => 'required|string|confirmed'
        ]);

        $user = User::where('reset_token', $fields['reset_token'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'Invalid reset token or passwords do not match'
            ], 400);
        }
        else{
            $user->password = bcrypt($fields['password']);
            $resetToken = Str::random(10);
            $user->reset_token = $resetToken;
            $user->save();

            return response()->json([
                'message' => 'Password changed'
            ]);
        }
    }
}
