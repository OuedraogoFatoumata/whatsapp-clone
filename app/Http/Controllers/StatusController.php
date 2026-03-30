<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Status;

class StatusController extends Controller
{
    public function index()
    {
       
        $statuses = Status::where('created_at', '>=', now()->subDay())
                          ->latest()
                          ->get();

        return view('status.index', compact('statuses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'nullable|string',
            'media' => 'nullable|image|max:2048',
        ]);

        $path = null;

        if ($request->hasFile('media')) {
            $path = $request->file('media')->store('statuses', 'public');
        }

        Status::create([
            'user_id' => auth()->id(),
            'content' => $request->content,
            'media' => $path,
            'expires_at' => now()->addHours(24),
        ]);

        return back();
    }
}