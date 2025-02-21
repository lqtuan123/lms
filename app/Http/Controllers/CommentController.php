<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'content' => 'required|string|min:3',
        ]);

        $comment = Comment::create([
            'user_id' => Auth::id(),
            'book_id' => $request->book_id,
            'content' => $request->content,
        ]);

        return response()->json([
            'success' => true,
            'user' => Auth::user()->full_name,
            'content' => $comment->content,
            'created_at' => $comment->created_at->diffForHumans()
        ]);
    }
}


