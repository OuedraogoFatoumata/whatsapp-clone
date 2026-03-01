<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    
    public function search(Request $request)
    {
        $recherche = $request->get('q');

        if (!$recherche || strlen($recherche) < 2) {
            return response()->json([]);
        }

        $utilisateurs = User::where('id', '!=', auth()->id())
            ->where(function ($query) use ($recherche) {
                $query->where('username', 'LIKE', '%' . $recherche . '%')
                      ->orWhere('email',    'LIKE', '%' . $recherche . '%')
                      ->orWhere('name',     'LIKE', '%' . $recherche . '%');
            })
            ->select('id', 'name', 'username', 'email', 'avatar', 'is_online')
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id'        => $user->id,
                    'name'      => $user->name,
                    'username'  => $user->username,
                    'email'     => $user->email,
                    'avatar'    => $user->avatar,
                    'is_online' => $user->is_online,
                    'initiale'  => strtoupper(substr($user->name, 0, 1)),
                ];
            });

        return response()->json($utilisateurs);
    }
}