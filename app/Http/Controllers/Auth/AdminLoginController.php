<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Communitie; 
use App\Models\Community_Manager; 
use Illuminate\Support\Facades\Hash;

class AdminLoginController extends Controller
{

    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return view('auth.admin-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'البريد الإلكتروني غير موجود.',
            ]);
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'password' => 'كلمة المرور غير صحيحة.',
            ]);
        }

        if ($user->roleID != 2) {

            Auth::logout();
            return back()->withErrors([
                'email' => 'هذا الحساب ليس لديه صلاحيات المشرف.',
            ]);
        }


        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $communities = Communitie::all();
            $user->load('managedCommunities');
            $userManagedCommunityIds = $user->managedCommunities->pluck('community_id')->toArray();

            $needsUpdate = false;
            if (count($userManagedCommunityIds) < $communities->count()) {
                $needsUpdate = true;
            } else {

                foreach ($communities as $community) {
                    if (!in_array($community->id, $userManagedCommunityIds)) {
                        $needsUpdate = true;
                        break;
                    }
                }
            }



            if ($needsUpdate) {
                foreach ($communities as $community) {

                    if (!in_array($community->id, $userManagedCommunityIds)) {
                        Community_Manager::create([
                            'user_id'      => Auth::user()->id,   
                            'community_id' => $community->id,     
                            'is_active'    => true,                
                        ]);
                    }
                }
            }

            return redirect()->intended(route('admin.dashboard'));
        }


        return back()->withErrors([
            'email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
