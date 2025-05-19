<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RefreshToken;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * 
     * 
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
     *            @OA\Property(property="access_token", type="string", example="Пользователь успешно зарегистрирован"),
     *         )
     *        
     *    ),
     * 
     *    @OA\Response(
     *        response=422,
     *        description="Unprocessable Entity"
     *    )
     * )
     */
    

    public function register(StoreUserRequest $request)
    {
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
     *        @OA\JsonContent(
     *            @OA\Property(property="access_token", type="string", example="token"),
     *            @OA\Property(property="token_type", type="string", example="bearer"),
     *            @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *        
     *    ),
     *    @OA\Response(
     *        response=422,
     *        description="Unprocessable Entity"
     *    )
     * )
     */

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string|exists:users',
            'password' => 'required|string'
        ]);

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
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="username", type="string", example="user"),
     *             @OA\Property(property="surname", type="string", example="Иванов"),
     *             @OA\Property(property="name", type="string", example="Иван"),
     *             @OA\Property(property="patronymic", type="string", example="Иванович"),
     *             @OA\Property(property="active", type="boolean", example="true"),
     *             @OA\Property(property="role", type="string", example="user"),
     *             @OA\Property(property="attempts", type="integer", example=3),
     *         ),
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

    public function user()
    {
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

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Выход произошел успешно']);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/refresh",
     *     summary="Обновление токена",
     *     security={{"bearerAuth": {} }},
     *     tags={"Auth"},
     * 
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *            @OA\Property(property="access_token", type="string", example="token"),
     *            @OA\Property(property="token_type", type="string", example="bearer"),
     *            @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
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
    
    public function refresh()
    {

        return $this->respondWithToken(auth('api')->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ]);
    }

    
}
