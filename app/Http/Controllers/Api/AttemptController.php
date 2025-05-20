<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\UserPrize;
use App\Http\Requests\StoreAttemptRequest;
use App\Http\Requests\UpdateAttemptRequest;
use App\Services\ActiveWheelService;
use Illuminate\Http\Request;

class AttemptController extends Controller
{
    /**
     * @OA\Schema(
     *     schema="Attempt",
     *     type="object",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="Попытка"),
     * )
     *
     *
     * 
     * @OA\Get(
     *    path="/api/attempt",
     *    summary="Получение списка попыток",
     *    tags={"Попытки"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     *    @OA\Parameter(
    *         name="name",
    *         in="query",
    *         description="Фильтрация по наименованию",
    *         required=false,
    *         @OA\Schema(type="string")
    *     ),
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
     *            @OA\Items(ref="#/components/schemas/Attempt")
     *        )
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
     *    )
     *    
     * )
     */
    public function index(Request $request)
    {
        $query = Attempt::query();
        if ($request->has('name') && $request->name != '') {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }
        $sortField = $request->input('sort', 'id');
        $sortOrder = $request->input('order', 'asc');
        $query->orderBy($sortField, $sortOrder);

        $perPage = $request->input('per_page', 10);
        $attempts = $query->paginate($perPage);

        return response()->json($attempts, 200);
    }

/**
     * 
     * @OA\Post(
     *    path="/api/attempt",
     *    summary="Создание попытки",
     *    tags={"Попытки"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
     * 
     *    @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Попытка"),
     *             required={"name"}
     *         )
     *     ),
     * 
     *    @OA\Response(
     *        response=201,
     *        description="ОК",
     *        @OA\JsonContent(
     *            ref="#/components/schemas/Attempt"
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
     *    )
     * )
     */
    public function store(StoreAttemptRequest $request)
    {
        $attempt = Attempt::create([
            'name'=>$request->name
        ]);

        return response()->json($attempt, 201);
    }

    /**
     * 
     * @OA\Get(
     *    path="/api/attempt/{id}",
     *    summary="Получение попытки по id",
     *    tags={"Попытки"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
     *    @OA\Parameter(
     *        description="id попытки",
     *        in="path",
     *        name="id",
     *        required=true,
     *        example=1,
     *        @OA\Schema(type="integer")
     *    ),
     *
     *    @OA\Response(
     *        response=200,
     *        description="ОК",
     *        @OA\JsonContent(
     *            ref="#/components/schemas/Attempt"
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
     *        response=404,
     *        description="Ничего не найдено",
     *        @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Попытки с таким id не существует")
     *        )
     *    ),
     * )
     */
    public function show($id)
    {
        $attempt = Attempt::findOrFail($id);
        return response()->json($attempt,200);
    }

/**
     * 
     * @OA\Put(
     *    path="/api/attempt/{id}",
     *    summary="Обновление попытки по id",
     *    tags={"Попытки"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
     *    @OA\Parameter(
     *        description="id попытки",
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
     *             @OA\Property(property="name", type="string", example="попытка"),
     *             required={"name"}
     *         )
     *     ),
     * 
     *    @OA\Response(
     *        response=200,
     *        description="ОК",
     *        @OA\JsonContent(
     *            ref="#/components/schemas/Attempt"
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
     *        response=404,
     *        description="Ничего не найдено",
     *        @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Попытки с таким id не существует")
     *        )
     *    ),
     * )
     */
    public function update(UpdateAttemptRequest $request, $id)
    {
        $attempt = Attempt::findOrFail($id);
        $userPrizes = UserPrize::where('prize_type', Attempt::class)->where('prize_id', $id)->get();
        if(!$userPrizes->isEmpty()){
            return response()->json(["message"=>"Этот приз редактировать нельзя, его выиграли"], 403);
        }
        
        $attempt->name = $request->name;
        $attempt->save();

        return $attempt;
    }
/**
     * 
     * @OA\Delete(
     *    path="/api/attempt/{id}",
     *    summary="Удаление попытки по id",
     *    tags={"Попытки"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
     *    @OA\Parameter(
     *        description="id попытки",
     *        in="path",
     *        name="id",
     *        required=true,
     *        example=1,
     *        @OA\Schema(type="integer")
     *    ),
     *
     *    @OA\Response(
     *        response=200,
     *        description="ОК",
     *        @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Попытка успешно удален")
     *        ) 
     *    ),
     * 
     *    @OA\Response(
     *        response=403,
     *        description="Действие запрещено", 
     *        @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Этот приз удалить нельзя, его выйграли")
     *        ) 
     *    ),
     *    @OA\Response(
     *        response=401,
     *        description="Неавторизованный доступ",
     *        @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *        )
     *    ),
     *    @OA\Response(
     *        response=404,
     *        description="Ничего не найдено",
     *        @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Попытки с таким id не существует")
     *        )
     *    ),
     * )
     */
    public function destroy($id)
    {
        $attempt = Attempt::findOrFail($id);
        $userPrizes = UserPrize::where('prize_type', Attempt::class)->where('prize_id', $id)->get();
        if(!$userPrizes->isEmpty()){
            return response()->json(["message"=>"Этот приз удалить нельзя, его выиграли"], 403);
        }

        $wheel = ActiveWheelService::getActiveWheel();
        $sectors = $wheel->sectors;
        foreach($sectors as $item){
            if($item->prize_type == Attempt::class && $item->prize_id == $id){
                return response()->json(["message"=>"Приз в активном колесе - нельзя удалить"], 403);
            }
        }
        $attempt->delete();
        return response()->json(["message"=>"Попытка успешно удалена"]);
    }
}
