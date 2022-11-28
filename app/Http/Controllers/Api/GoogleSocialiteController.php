<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;

class GoogleSocialiteController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function handleCallback()
    {
        try {

            $user = Socialite::driver('google')->stateless()->user();

            $finduser = User::where('social_id', $user->id)->first();

            if ($finduser) {
                $login = [
                    'email' => $finduser->email,
                    'password' => 'password'
                ];
                auth()->attempt($login);
                $token = auth()->user()->createToken('auth_token')->plainTextToken;

                return redirect('http://localhost:3000/auth/login?token=' . $token)->withCookie(cookie()->forever('auth_token', $token));
                // return response()->json(['user' => auth()->user(), 'token' => $token], 200);
            } else {
                $newUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'social_id' => $user->id,
                    'social_type' => 'google',
                    'password' => Hash::make('password'),
                ]);
                $newUser->markEmailAsVerified();
                $token = $newUser->createToken('auth_token')->plainTextToken;
                $login = [
                    'email' => $newUser->email,
                    'password' => 'password'
                ];
                auth()->attempt($login);

                // return response()->json(['user' => $user, 'token' => $token], 200);
                return redirect('http://localhost:3000/auth/login?token=' . $token)->withCookie(cookie()->forever('auth_token', $token));
                // return redirect('/home');
            }
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}
