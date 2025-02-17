<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ProfileRequest;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;

class UserController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        auth()->login($user);

        event(new Registered($user));

        return redirect('/email/verify');
    }

    public function showEdit(Request $request)
    {
        $user = Auth::user();

        return view('profile_edit', compact('user'));
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if (!$user->hasVerifiedEmail()) {
                Auth::logout();
                return redirect('/email/verify')->with('error', 'メール認証を完了してください。');
            }

            $request->session()->regenerate();
            return redirect('/');
        } else {
            return back()->withErrors([
                'email' => 'ログイン情報が正しくありません。',
            ]);
        }
    }
}
