<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\models\User;
use App\Models\Item;
use App\Models\Like;
use App\Models\Comment;
use App\Models\Category;
use App\Http\Requests\CommentRequest;
use App\Http\Requests\ExhibitionRequest;
use App\Http\Controllers\UserController;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user && $user->first_login) {
            $userController = new UserController();
            return $userController->showEdit($request);
        }

        $tab = $request->get('tab', session('current_tab', Auth::check() ? 'mylist' : 'all'));
        session(['current_tab' => $tab]);

        $query = $request->get('search', session('search_query', ''));
        session(['search_query' => $query]);

        $items = collect();
        $myitems = collect();

        if ($tab == 'all') {
            $items = Item::where('user_id', '!=', Auth::id())
                ->searchByName($query)
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        } elseif ($tab == 'mylist') {
            if (Auth::check()) {
                $myitems = Auth::user()->likedItems()
                    ->searchByName($query)
                    ->orderBy('created_at', 'desc')
                    ->paginate(20);
            }
        }

        return view('index', compact('items', 'myitems', 'tab', 'query'));
    }
}
