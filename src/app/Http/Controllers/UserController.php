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

    public function address(Request $request, $itemId)
    {
        $user = Auth::user();
        $item = Item::findOrFail($itemId);

        return view('address', [
            'user' => $user,
            'item' => $item,
        ]);
    }

    public function addressEdit(Request $request, $itemId)
    {
        $userId = Auth::id();

        if (!$userId) {
            return view('auth.login');
        }

        $addressData = $request->only(['postal_code', 'address', 'building_name']);

        $user = User::findOrFail($userId);
        $user->update($addressData);

        return redirect()->route('purchase', ['itemId' => $itemId]);
    }

    public function profile(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $currentTab = $request->query('tab', 'selling');

        $sellingItems = Item::where('user_id', $user->id)->paginate(8);

        $purchasedItems = Purchase::where('user_id', $user->id)
            ->with('item')
            ->paginate(8);

        return view('profile', compact('user', 'currentTab', 'sellingItems', 'purchasedItems'));
    }

    public function updateUserImage(Request $request)
    {
        $user = Auth::user();

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profile_images', 'public');

            session(['profile_image' => $path]);
        }

        return redirect()->route('profile_edit');
    }

    public function edit(ProfileRequest $request)
    {
        $user = Auth::user();

        if (session()->has('profile_image')) {
            $user->profile_image = session('profile_image');
        }

        $validatedData = $request->validated();
        $user->update($validatedData);

        session()->forget('profile_image');

        if ($user->first_login) {
            $user->first_login = false;
            $user->save();
        }

        return redirect('/');
    }

    public function revertProfileImage()
    {
        $user = Auth::user();

        if (session()->has('profile_image')) {
            $user->profile_image = session('profile_image');
            $user->save();
        }
    }
}
