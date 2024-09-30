<?php

namespace App\Http\Controllers\Backend\GoogleLogin;

use App\Helpers\UserHelper;
use App\Http\Controllers\Controller;
use App\Mail\RegisterMail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use Illuminate\Support\Str;

class GoogleLoginController extends Controller
{
    public function redirectToGoogle()
    {




       $config = config('services.google');
        return Socialite::driver('google')->stateless()->redirect();
    }


    public function handleGoogleCallback(Request $request)
    {
        try {
                 $googleUser = Socialite::driver('google')->stateless()->user();
            $user = User::where('email', $googleUser->email)->first();

            if (!$user) {
                $password = Str::random(16);

                $user = User::create([
                    'profile_photo' => $googleUser->avatar,
                    'google_id' => $googleUser->id,
                    'auth_type' => 'google',
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'password' => Hash::make($password),
                    'is_profile_completed' => 0,
                    'is_blocked' => 1,
                    'api_token' => sha1(time()),
                    'type' => 'Instructor',
                ]);


                $instructorRole = Role::where('name', 'Instructor')->first();
                if ($instructorRole) {
                    $user->attachRole($instructorRole);
                }

                Mail::to($user->email)->send(new RegisterMail($password));

            } else {
                $user->update([
                    'name' => $googleUser->name,
                    'profile_photo' => $googleUser->avatar,
                ]);
            }
            Auth::login($user);
            $request->session()->regenerate();

            if ($user->is_verified == '1' && $user->is_profile_completed == '1') {
                return redirect()->route('instructor_dashboard');
            } else {
                return redirect()->route('sps_form');
            }
        } catch (Exception $e) {

            return redirect()->route('login_instructor')->with('error', $e->getMessage());
        }
    }

//public function handleGoogleCallback()
//{
//    $googleUser = Socialite::driver('google')->stateless()->user();
//    $user = User::where('email', $googleUser->email)->first();
//
//
//
//    if (!$user) {
//        $user = User::create([
//            'profile_photo' => $googleUser->avatar,
//            'github_id' => $googleUser->id,
//            'auth_type' => 'google',
//            'name' => $googleUser->name,
//            'email' => $googleUser->email,
//             'password' => Hash::make('Qusteam12345678'),
//            'is_profile_completed' => 0,
//            'is_blocked' => 1,
//            'api_token'=> sha1(time()),
//            'type' => 'Instructor',
//
//        ]);
//
//        $instructorRole = Role::where('name', 'Instructor')->first();
//
//        if ($instructorRole) {
//            $user->attachRole($instructorRole);
//        }
//
//        Auth::login($user);
//
//     if ($user->is_verified == '1' && $user->is_profile_completed == '1') {
//               return redirect()->route('instructor_dashboard');
//       } else {
//            UserHelper::sent_email($user->email, 'Registration', 'Registration Via Google Success!! Please Complete next Process for Account Verification!!');
//            return redirect()->route('sps_form');
//}
//    } else {
//        User::where('email', $googleUser->email)->update([
//            'name' => $googleUser->name,
//            'email' => $googleUser->email,
//            'profile_photo' => $googleUser->avatar,
//        ]);
//
//            $user = User::where('email', $googleUser->email)->first();
//
//             Auth::login($user);
//
//           if ($user->is_verified == '1' && $user->is_profile_completed == '1') {
//
//            return redirect()->route('instructor_dashboard');
//        } else {
//            return redirect()->route('sps_form');
//        }
//    }
//}




}
