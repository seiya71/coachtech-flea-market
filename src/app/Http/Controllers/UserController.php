<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Registered;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ProfileRequest;
use App\Http\Requests\ProfileImageRequest;
use App\Http\Requests\ShippingAddressRequest;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Address;

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
        // パスワードの総当たりを防ぐため、同じメールアドレスと IP からの失敗は1分間に5回まで
        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withInput($request->only('email'))->withErrors([
                'email' => "ログインの試行回数が多すぎます。{$seconds}秒後にもう一度お試しください。",
            ]);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            RateLimiter::hit($throttleKey, 60);

            // メールアドレスが登録されているかどうかが分からないように、どちらの場合も同じメッセージにする
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'ログイン情報が登録されていません',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        // メール認証が済んでいない場合は、ログインしたまま認証画面へ（確認メールを再送できるように）
        if (!Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')->with('error', 'メール認証を完了してください。');
        }

        return redirect('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function address(Request $request, $itemId)
    {
        $user = Auth::user();
        $item = Item::findOrFail($itemId);

        // 登録済みの住所（なければプロフィールの住所）を初期値にする
        $address = Address::where('user_id', $user->id)->first() ?? $user;

        $defaultAddress = [
            'postal_code' => $address->postal_code,
            'address' => $address->address,
            'building_name' => $address->building_name,
        ];

        return view('address', [
            'user' => $user,
            'item' => $item,
            'defaultAddress' => $defaultAddress,
        ]);
    }

    public function addressEdit(ShippingAddressRequest $request, $itemId)
    {
        $userId = Auth::id();

        $addressData = $request->validated();

        $address = Address::updateOrCreate(
            ['user_id' => $userId],
            $addressData
        );

        session(['shipping_address' => $address]);

        return redirect()->route('purchase', ['itemId' => $itemId]);
    }



    public function profile(Request $request)
    {
        $user = Auth::user();

        $currentTab = $request->query('tab', 'selling');

        $sellingItems = Item::where('user_id', $user->id)->paginate(8);

        $purchasedItems = Purchase::where('user_id', $user->id)
            ->with('item')
            ->paginate(8);

        return view('profile', compact('user', 'currentTab', 'sellingItems', 'purchasedItems'));
    }

    public function updateUserImage(ProfileImageRequest $request)
    {
        $path = $request->file('profile_image')->store('profile_images', 'public');

        // 画像を選び直した場合は、前に選んでいた画像がどのユーザーにも使われていなければ削除する
        $previousPath = session('profile_image');
        if ($previousPath && !User::where('profile_image', $previousPath)->exists()) {
            Storage::disk('public')->delete($previousPath);
        }

        session(['profile_image' => $path]);

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
}
