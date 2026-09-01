<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Login with email OR phone number
     * POST /api/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string', // Can be email OR phone
            'password' => 'required|string',
        ]);

        $login = $request->input('login');
        $password = $request->input('password');

        // Determine if login is email or phone
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        // Find user by email or phone
        $user = User::where($field, $login)->first();

        // If user not found, check if it's a phone number with formatting
        if (!$user && $field === 'phone') {
            // Try with different phone formats (remove spaces, special chars)
            $cleanedPhone = preg_replace('/[^0-9+]/', '', $login);
            $user = User::where('phone', $cleanedPhone)->first();
        }

        if (!$user) {
            throw ValidationException::withMessages([
                'login' => ['No user found with this email or phone number.'],
            ]);
        }

        // Attempt login with credentials
        $credentials = [
            $field => $login,
            'password' => $password
        ];

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.'],
            ]);
        }

        // Get authenticated user
        $user = Auth::user();

        // Check if login is enabled for the user
        if (isset($user->is_enable_login) && $user->is_enable_login == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is disabled. Please contact administrator.'
            ], 403);
        }

        // Check if user is active
        if (isset($user->status) && $user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not active. Please contact administrator.'
            ], 403);
        }

        // Revoke previous tokens (optional - keep only current session)
        // $user->tokens()->delete();

        // Create new Sanctum token
        $token = $user->createToken('api-token')->plainTextToken;

        // Get employee data if exists
        $employee = $user->employee;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'type' => $user->type,
                'user_type' => $user->type,
                'is_enable_login' => $user->is_enable_login ?? 1,
                'status' => $user->status ?? 'active',
                'created_at' => $user->created_at,
                'employee_id' => $employee ? $employee->id : null,
                'employee_name' => $employee ? $employee->name : null,
                'employee_code' => $employee ? $employee->employee_id : null,
                'department' => $employee && $employee->department ? $employee->department->name : null,
                'designation' => $employee && $employee->designation ? $employee->designation->name : null,
                'is_face_enrolled' => $employee && !empty($employee->face_descriptor) ? true : false,
                'has_face_photo' => $employee && !empty($employee->face_photo) ? true : false,
                'face_photo_url' => $employee && !empty($employee->face_photo) ? asset('uploads/face/' . $employee->face_photo) : null,
            ]
        ]);
    }

    /**
     * Logout user
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            
            if ($user) {
                // Revoke current access token
                $request->user()->currentAccessToken()->delete();
                
                // Optional: Revoke all tokens
                // $user->tokens()->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Logout failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated user details
     * GET /api/me
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'type' => $user->type,
                'user_type' => $user->type,
                'is_enable_login' => $user->is_enable_login ?? 1,
                'status' => $user->status ?? 'active',
                'avatar' => $user->avatar ? asset('uploads/avatar/' . $user->avatar) : null,
                'created_at' => $user->created_at,
                'employee' => $employee ? [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'employee_code' => $employee->employee_id,
                    'email' => $employee->email,
                    'phone' => $employee->phone,
                    'department' => $employee->department ? $employee->department->name : null,
                    'designation' => $employee->designation ? $employee->designation->name : null,
                    'branch' => $employee->branch ? $employee->branch->name : null,
                    'is_face_enrolled' => !empty($employee->face_descriptor),
                    'has_face_photo' => !empty($employee->face_photo),
                    'face_photo_url' => !empty($employee->face_photo) ? asset('uploads/face/' . $employee->face_photo) : null,
                    'half_day_threshold' => $employee->half_day_threshold ?? 4.0,
                    'late_access_enabled' => $employee->late_access_enabled ?? false,
                    'late_allowed_minutes' => $employee->late_allowed_minutes ?? 60,
                ] : null,
            ]
        ]);
    }

    /**
     * Refresh token
     * POST /api/refresh-token
     */
    public function refreshToken(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Revoke current token
            $user->currentAccessToken()->delete();

            // Create new token
            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'token' => $token,
                'token_type' => 'Bearer'
            ]);
        } catch (\Exception $e) {
            Log::error('Refresh token error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh token: ' . $e->getMessage()
            ], 500);
        }
    }
}