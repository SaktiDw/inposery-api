<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{

    public function reset_password(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );
        return $status == Password::PASSWORD_RESET
            ? response()->json(['message' => 'New Password has been created!'])
            : response()->json(['message' => 'Reset password failed.'], 400);
    }
    public function reset_password_link(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Password reset link has been send to your email!'])
            : response()->json(['message' => 'We cannot found your email!'], 400);
    }

    public function resend_email_verification_link(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();
        return response(['message' => 'Verification link sent!']);
    }

    public function verify_email(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect(env('FRONT_URL') . '/email/verify/already-success');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }

    // public function register(Request $request)
    // {
    //     $request->validate([
    //         'name' => ['required', 'string', 'max:255'],
    //         'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
    //         'password' => ['required', 'confirmed', Rules\Password::defaults()],
    //     ]);

    //     $user = User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => Hash::make($request->password),
    //     ]);

    //     // $token = $user->createToken('Laravel-9-Passport-Auth')->accessToken;
    //     $token = $user->createToken('auth_token')->plainTextToken;

    //     return response()->json(['user' => $user, 'token' => $token], 200);
    // }

    // public function login(Request $request)
    // {
    //     $data = [
    //         'email' => $request->email,
    //         'password' => $request->password
    //     ];

    //     if (auth()->attempt($data)) {
    //         $token = auth()->user()->createToken('auth_token')->plainTextToken;
    //         return response()->json(['user' => auth()->user(), 'token' => $token], 200);
    //     } else {
    //         return response()->json(['error' => 'Unauthorised'], 401);
    //     }
    // }

    // public function user()
    // {

    //     $user = auth()->user();

    //     return response()->json(['user' => $user], 200);
    // }

    // public function logout(Request $request)
    // {
    //     if (method_exists(auth()->user()->currentAccessToken(), 'delete')) {
    //         auth()->user()->currentAccessToken()->delete();
    //     }

    //     auth()->guard('web')->logout();
    //     // // Revoke all tokens...
    //     // $request->user()->tokens()->delete();

    //     // // Revoke the token that was used to authenticate the current request...
    //     // $request->user()->currentAccessToken()->delete();
    //     return [
    //         'message' => 'You have successfully logged out and the token was successfully deleted'
    //     ];
    // }
}
