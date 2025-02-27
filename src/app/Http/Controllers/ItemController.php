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
        } elseif ($tab == 'mylist' && Auth::check()) {
            $myitems = Auth::user()->likedItems()
                ->searchByName($query)
                ->orderBy('created_at', 'desc')
                ->paginate(20);
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

    public function addcomment(CommentRequest $request, $itemId)
    {
        $userId = Auth::id();

        if (!$userId) {
            return redirect('/login');
        }

        $content = $request->input('content');

        Comment::create([
            'user_id' => $userId,
            'item_id' => $itemId,
            'content' => $content,
        ]);

        return redirect()->back();
    }

    public function showSell(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $categories = Category::all();

        return view('sell', compact('categories'));
    }

    public function sell(ExhibitionRequest $request)
    {
        $user = Auth::user();

        $validatedData = $request->validated();

        $item = Item::create([
            'item_name' => $validatedData['item_name'],
            'brand' => $validatedData['brand'],
            'description' => $validatedData['description'],
            'price' => $validatedData['price'],
            'user_id' => $user->id,
            'item_image' => $validatedData['item_image'] ?? null,
            'condition' => $validatedData['condition'],
        ]);

        if (isset($validatedData['categories'])) {
            $item->categories()->attach($validatedData['categories']);
        }

        session()->forget('item_image');

        return redirect()->route('home');
    }

    public function uploadItemImage(Request $request)
    {
        if ($request->hasFile('item_image')) {
            $path = $request->file('item_image')->store('item_images', 'public');

            session(['item_image' => $path]);
        }

        return back();
    }
}
