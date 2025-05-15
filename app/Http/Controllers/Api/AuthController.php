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

        $refreshToken = hash('sha512', Str::random(64));
        $expiresAt = now()->addMinutes(config('jwt.refresh_ttl'));
        $user = User::where('username',$request->username)->first();

        RefreshToken::create([
            'token' => $refreshToken,
            'user_id'=>$user->id,
            'expires_at' => $expiresAt
        ]);

        return $this->respondWithToken($token, $refreshToken);
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
     * @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="refresh_token", type="string", example="abc1234hfhfgsjhehjdhu"),
     *             required = {"refresh_token"}
     *         ) 
     *    ),
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
    
    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required'
        ]);
        $refreshToken = $request->refresh_token;
        $refreshTokenRecord = RefreshToken::where('token', $refreshToken)->first();

        if (!$refreshTokenRecord || $refreshTokenRecord->expires_at <= now()) {
            return response()->json(['error' => 'Refresh токен истек или недействителен'], 401);
        }

        $user = User::find($refreshTokenRecord->user_id);

        if (!$user) {
            return response()->json(['error' => 'Пользователь не найден'], 404);
        }

        $newAccessToken = auth('api')->refresh();
        $newRefreshToken = hash('sha512', Str::random(64));
        $expiresAt = now()->addMinutes(config('jwt.refresh_ttl'));

        $refreshTokenRecord->update([
            'token' => $newRefreshToken,
            'expires_at' => $expiresAt
        ]);

        return $this->respondWithToken($newAccessToken, $newRefreshToken);
    }

    protected function respondWithToken($token,  $refreshToken)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 30,
            'refresh_token' => $refreshToken,
        ]);
    }

    
}
