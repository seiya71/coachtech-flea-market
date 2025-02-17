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

    public function getlike($itemId)
    {

        $userId = Auth::id();

        $likeCount = Like::where('item_id', $itemId)->count();

        $userHasLiked = false;
        if ($userId) {
            $userHasLiked = Like::where('item_id', $itemId)->where('user_id', $userId)->exists();
        }

        return [
            'likeCount' => $likeCount,
            'userHasLiked' => $userHasLiked,
        ];
    }

    public function getcomment($itemId)
    {

        $comments = Comment::where('item_id', $itemId)
            ->with('user')
            ->get();

        $commentCount = $comments->count();

        if ($commentCount === 0) {
            return [
                'commentCount' => 0,
                'comments' => [],
            ];
        }

        $commentData = $comments->map(function ($comment) {
            $user = $comment->user;

            return [
                'user_image' => $user->profile_image,
                'user_name' => $user->name,
                'content' => $comment->content,
            ];
        });

        return [
            'commentCount' => $commentCount,
            'comments' => $commentData,
        ];
    }

    public function show($id)
    {
        $item = Item::findOrFail($id);

        $likeData = $this->getlike($id);

        $commentData = $this->getcomment($id);

        return view('item', [
            'item' => $item,
            'likeCount' => $likeData['likeCount'],
            'userHasLiked' => $likeData['userHasLiked'],
            'comments' => $commentData['comments'],
            'commentCount' => $commentData['commentCount'],
            'categories' => $item->categories,
        ]);
    }

    public function addlike($itemId)
    {
        $userId = Auth::id();

        if (!$userId) {
            return view('auth.login');
        }

        $like = Like::where('item_id', $itemId)->where('user_id', $userId)->first();

        if ($like) {
            $like->delete();
        } else {
            Like::create([
                'user_id' => $userId,
                'item_id' => $itemId
            ]);
        }

        return redirect()->back();
    }
}
