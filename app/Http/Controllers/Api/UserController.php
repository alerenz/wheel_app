<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * 
     * @OA\Schema(
     *     schema="User",
     *     type="object",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="username", type="string", example="user123"),
     *     @OA\Property(property="password", type="string", example="qwerty12345"),
     *     @OA\Property(property="surname", type="string", example="Иванов"),
     *     @OA\Property(property="name", type="string", example="Иван"),
     *     @OA\Property(property="patronymic", type="string", example="Иванович"),
     *     @OA\Property(property="active", type="boolean", example=true),
     *     @OA\Property(property="role", type="string", example="user"),
     *     @OA\Property(property="attempts", type="integer", example=3),
     * )
     * 
     * @OA\Get(
     *    path="/api/users",
     *    summary="Получение списка пользователей",
     *    tags={"Пользователи"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     *    @OA\Parameter(
    *         name="sort",
    *         in="query",
    *         description="Поле для сортировки",
    *         required=false,
    *         @OA\Schema(type="string", default="id")
    *     ),
    *     @OA\Parameter(
    *         name="order",
    *         in="query",
    *         description="Порядок сортировки",
    *         required=false,
    *         @OA\Schema(type="string", enum={"asc", "desc"}, default="asc")
    *     ),
    *    @OA\Parameter(
    *         name="per_page",
    *         description="Количество элементов на странице (по умолчанию 10)",
    *         required=false,
    *         in="query",
    *         @OA\Schema(type="integer")
    *     ),
    *    @OA\Parameter(
    *         name="page",
    *         description="Страница",
    *         required=false,
    *         in="query",
    *         @OA\Schema(type="integer")
    *     ),
     *
     *    @OA\Response(
     *        response=200,
     *        description="ОК",
     *        @OA\JsonContent(
     *            type="array",
     *            @OA\Items(ref="#/components/schemas/User")
     *        )
     *
     *    ),
     *    @OA\Response(
     *        response=401,
     *        description="Неавторизованный доступ",
     *        @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *        )
     *    ),
     *    @OA\Response(
     *        response=403,
     *        description="Доступ запрещен",
     *        @OA\JsonContent(
     *            @OA\Property(property="message", type="string", example="Forbidden.")
     *        )
     *    ),
     * )
     * 
     */

    public function index(Request $request)
    {
        $query = User::query();
        $sortField = $request->input('sort', 'id');
        $sortOrder = $request->input('order', 'asc');
        $query->orderBy($sortField, $sortOrder);

        $perPage = $request->input('per_page', 10);
        $users = $query->paginate($perPage);

        return response()->json($users, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }

    /**
     * 
     * @OA\Post(
     *    path="/api/users/accrual-attempts/{id}",
     *    summary="Начисление попыток",
     *    tags={"Пользователи"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
     *    @OA\Parameter(
     *        description="id пользователя",
     *        in="path",
     *        name="id",
     *        required=true,
     *        example=1,
     *        @OA\Schema(type="integer")
     *    ),
     * 
     * 
     *    @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="attempts", type="integer", example=3),
     *             required={"attempts"}
     *         )
     *     ),
     * 
     *    @OA\Response(
     *        response=200,
     *        description="ОК",
     *        @OA\JsonContent(
     *            @OA\Property(property="message", type="string", example="Попытки для пользователя user1 начилислились")
     *        )
     *        
     *    ),
     *    @OA\Response(
     *        response=401,
     *        description="Неавторизованный доступ",
     *        @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *        )
     *    ),
     *    @OA\Response(
     *        response=403,
     *        description="Доступ запрещен",
     *        @OA\JsonContent(
     *            @OA\Property(property="message", type="string", example="Forbidden.")
     *        )
     *    ),
     *    @OA\Response(
     *        response=422,
     *        description="Необрабатываемый контент",
     *        @OA\JsonContent(
     *            @OA\Property(property="message", type="string", example="Количество начисляемых попыток должно быть больше 0")
     *        )
     *    ),
     * )
     */

    public function accrualAttempts(Request $request, $id)
    {
        $user = User::findOrFail($id);
        if($request->attempts < 0){
            return response()->json(["message"=>"Количество начисляемых попыток должно быть больше 0"], 422);
        }
        if($request->attempts > config('custom.max_attempts')){
            return response()->json(["message"=>
            "Количество начисляемых попыток не может быть больше чем ".config('custom.max_attempts')], 422);
        }

        $user->attempts = $request->attempts;
        $user->save();

        return response()->json(["message"=>"Попытки для пользователя ".$user->username." начислились"], 200);
    }
}
