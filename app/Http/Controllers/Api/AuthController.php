<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class AuthController extends Controller
{
    public function login()
    {
        request()->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required'],
        ]);
        /**
         * We are authenticating a request from our frontend.
         */
        // if (EnsureFrontendRequestsAreStateful::fromFrontend(request())) {
        if (!Auth::guard('web')
            ->attempt(
                request()->only('email', 'password'),
                request()->boolean('remember')
            )) {
            // return throw ValidationException::withMessages([
            //     'email' => __('auth.failed'),
            // ]);
            return response(['message' => __('auth.failed')], 422);
        }
        return;
        // }
    }

    public function logout()
    {
        // if (EnsureFrontendRequestsAreStateful::fromFrontend(request())) {
        //     // Auth::guard('web')->logout();
        //     auth('web')->logout();

        //     request()->session()->invalidate();

        //     request()->session()->regenerateToken();
        //     return;
        // } else {
        // Revoke token
        return auth()->logout();
        // }
    }

    public function register()
    {
        request()->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed'],
        ]);

        $user = User::create([
            'name' => request('name'),
            'email' => request('email'),
            'password' => Hash::make(request('password')),
        ]);
        $user->sendEmailVerificationNotification();

        return Auth::guard('web')->login($user);
    }

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
            ? response(['message' => 'New Password has been created!'], 200)
            : response(['message' => 'Reset password failed.'], 400);
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
            ? response(['message' => 'Password reset link has been send to your email!'], 200)
            : response(['message' => 'We cannot found your email!'], 400);
    }

    public function resend_email_verification_link(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();
        return response(['message' => 'Verification link sent!'], 200);
    }

    public function verify_email(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect(env('FRONTEND_URL') . '/dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
