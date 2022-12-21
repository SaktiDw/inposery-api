<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
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
            auth('web')->attempt($login, true);
            return redirect(env('FRONTEND_URL'));
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
            auth('web')->attempt($login, true);
        }


        // login user
        auth('web')->attempt($login, true);

        // setelah login redirect ke dashboard
        return redirect()->away(env('FRONTEND_URL') . '/dashboard');
    }
}
