<?php

namespace SunAppModules\Sunbet\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use SunAppModules\Core\Http\Controllers\Controller;
use SunAppModules\SunBet\Entities\SunbetUser;

class AuthApiController extends Controller
{

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:4'
        ]);

        $user = SunbetUser::create([
            'name' => ucwords($request->name),
            'email' => $request->email,
            'password' => Hash::make(($request->password)),
        ]);

        return $this->response($user);
    }

    public function login(Request $request)
    {
        $cred = $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required'
        ]);

        if (!Auth::attempt($cred)) {
            return response()->json([
                'message' => 'Unauthorized!'
            ]);
        }

        return $this->response(Auth:: user());

    }

    public function logout(Request $request)
    {
        $user = SunbetUser::updateOrCreate([
            'email' => $request->email,
        ],
            [
                'logged_at' => null,
            ]);
    }
}
