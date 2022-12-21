<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProvideCallback($provider)
    {
        try {

            $user = Socialite::driver($provider)->stateless()->user();
        } catch (Exception $e) {
            return redirect()->back();
        }
        // find or create user and send params user get from socialite and provider
        // $authUser = $this->findOrCreateUser($user, $provider);
        $findUser = User::where('social_id', $user->id)->first();

        if ($findUser) {
            $login = [
                'email' => $findUser->email,
                'password' => 'themapunleashtoyoufolowingfolowing',
            ];
            Auth::guard('web')->attempt($login, true);
            return redirect()->away(env('FRONTEND_URL', 'http://localhost:3000/dashboard'));
        } else {
            $newUser = User::create([
                'name' => $user->name,
                'email' => $user->email,
                'social_id' => $user->id,
                'social_type' => $provider,
                'password' => Hash::make('themapunleashtoyoufolowingfolowing')
            ]);
            $newUser->markEmailAsVerified();
            $login = [
                'email' => $newUser->email,
                'password' => 'themapunleashtoyoufolowingfolowing',
            ];
            Auth::guard('web')->attempt($newUser, true);
        }


        // login user
        Auth::guard('web')->attempt($login, true);

        // setelah login redirect ke dashboard
        return redirect()->away(env('FRONTEND_URL', 'http://localhost:3000/dashboard'));
    }
}
