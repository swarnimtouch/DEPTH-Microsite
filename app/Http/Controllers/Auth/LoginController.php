<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Admin\Controller;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function index()
    {
        return view('auth.login', ['title' => __('Login')]);
    }

    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = array_merge($request->only('email', 'password'), ['status' => 'active']);

        if (Auth::guard('admin')->attempt($credentials, $request->remember ?? false)) {
            $user = Auth::guard('admin')->user();
            $isAllowed = $user->type === 'admin'
                || ($user->type === 'employee' && $user->role === 'sub_admin');

            if (!$isAllowed) {
                Auth::guard('admin')->logout();

                return response()->json([
                    'success' => false,
                    'message' => 'This account is not authorised for the admin panel.',
                ], 403);
            }

            $request->session()->regenerate();
            return response()->json([
                'success' => true,
                'message' => 'Login successful!',
                'redirect' => route('admin.dashboard')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials.',
            'errors' => [
                'email' => ['These credentials do not match our records.']
            ]
        ], 401);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('admin/login');
    }
}
