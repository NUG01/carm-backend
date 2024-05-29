<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerificationEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Lang;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $user = User::where('email', $request->email)->first();

        if ($user && $user->hasVerifiedEmail() && Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return response()->json(['user' => Auth::user()]);
        }

        return response()->json(['message' => __('messages.error')], 401);
    }

    public function register(Request $request)
    {

        $request->validate([
            'name' => 'required|string',
            'surname' => 'required|string',
            'mobile_number' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'mobile_number' => $request->mobile_number,
            'email' => strtolower($request->email),
            'password' => bcrypt($request->password),
        ]);

        $payload = [
            'name' => $user['name'],
            'email' => $user['email'],
            'url' => config('auth.frontend-verification.url') . hash('sha256',  $user->id . $user->email)
        ];


        Mail::to($payload['email'])->send(new VerificationEmail($payload));
        return response()->json(['message' => 'შეამოწმეთ ელ.ფოსტა რეგისტრაციის დასასრულებლად!']);
    }

    public function logout()
    {
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return response()->noContent();
    }



    public function verifyEmail(Request $request)
    {
        $user_instance = DB::table('users')
            ->whereRaw('SHA2(CONCAT(id, email), 256) = ?', [$request->token])
            ->first();

        $user = User::where('id', $user_instance->id)->firstOrFail();

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            return response()->json(['message' => 'Email successfully verified']);
        } else {
            return response()->json(['message' => 'User already verified']);
        }
    }
}
