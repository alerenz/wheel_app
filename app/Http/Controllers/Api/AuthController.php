<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * 
     * @OA\Post(
     *    path="/api/auth/register",
     *    summary="Регистрация пользователя",
     *    tags={"Auth"},
     *    
     *    @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="username", type="string", example="user123"),
     *             @OA\Property(property="password", type="string", example="qwerty12345"),
     *             @OA\Property(property="surname", type="string", example="Иванов"),
     *             @OA\Property(property="name", type="string", example="Иван"),
     *             @OA\Property(property="patronymic", type="string", example="Иванович"),
     *             required = {"username","password","surname","name","patronymic"}
     *         ) 
     *    ),
     * 
     *    @OA\Response(
     *        response=201,
     *        description="OK",
     *        @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Пользователь успешно зарегистрирован")
     *        )
     *        
     *    ),
     * )
     */
    

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
            'role'=>'user',
            'active'=>true,
            
            
        ]);

        return response()->json(['message' => 'Пользователь успешно зарегистрирован'], 201);
    }

    /**
     * 
     * @OA\Post(
     *    path="/api/auth/login",
     *    summary="Авторизация пользователя",
     *    tags={"Auth"},
     *    
     *    @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="username", type="string", example="user123"),
     *             @OA\Property(property="password", type="string", example="qwerty12345"),
     *             required={"username","password"},    
     *        )
     *    ),
     * 
     *    @OA\Response(
     *        response=200,
     *        description="ОК",
     *        
     *    ),
     * )
     */

    public function login(){
        $credentials = request(['username', 'password']);

        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * @OA\Get(
     *     path="/api/auth/show",
     *     summary="Получить информацию о текущем пользователе",
     *     security={{"bearerAuth": {} }},
     *     tags={"Auth"},
     *     @OA\Response(
     *         response=200,
     *         description="Успешное получение данных пользователя",
     *        
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизованный доступ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */

    public function user(){
        return response()->json(auth('api')->user());
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Выход пользователя",
     *     security={{"bearerAuth": {} }},
     *     tags={"Auth"},
     *     @OA\Response(
     *         response=200,
     *         description="Выход произошел успешно",
     *        
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизованный доступ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */

    public function logout(){
        auth('api')->logout();
        return response()->json(['message' => 'Выход произошел успешно']);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/refresh",
     *     summary="Обновление токена",
     *     security={{"bearerAuth": {} }},
     *     tags={"Auth"},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *        
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизованный доступ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    
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
