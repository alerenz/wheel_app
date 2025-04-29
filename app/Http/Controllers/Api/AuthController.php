<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    

    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|min:6',
            'surname'=>'required|string',
            'name'=>'required|string',
            'patronymic'=>'required|string',
        ]);

        $user = User::create([
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'surname'=>$request->surname,
            'name'=>$request->name,
            'patronymic'=>$request->patronymic,
            'role'=>'admin',
            'active'=>true,
            
            
        ]);

        return response()->json(['message' => 'User registered successfully'], 201);
    }


    public function login(){
        $credentials = request(['username', 'password']);

        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function user(){
        return response()->json(auth('api')->user());
    }

    public function logout(){
        auth('api')->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }
    
    public function refresh(){
        return $this->respondWithToken(auth('api')->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 30
        ]);
    }
}
