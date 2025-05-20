<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmptyPrize;
use App\Http\Requests\StoreEmptyPrizeRequest;
use App\Http\Requests\UpdateEmptyPrizeRequest;
use App\Models\UserPrize;
use App\Services\ActiveWheelService;
use Illuminate\Http\Request;

class EmptyPrizeController extends Controller
{
    /**
     * @OA\Schema(
     *     schema="EmptyPrize",
     *     type="object",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="В следующий раз"),
     * )
     *
     *
     * 
     * @OA\Get(
     *    path="/api/empty-prize",
     *    summary="Получение списка пустых призов",
     *    tags={"Пустые призы"},
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
     *            @OA\Items(ref="#/components/schemas/EmptyPrize")
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
        $query = EmptyPrize::query();
        $sortField = $request->input('sort', 'id');
        $sortOrder = $request->input('order', 'asc');
        $query->orderBy($sortField, $sortOrder);

        $perPage = $request->input('per_page', 10);
        $emptyPrizes = $query->paginate($perPage);

        return response()->json($emptyPrizes, 200);
    }

    /**
     * 
     * @OA\Post(
     *    path="/api/empty-prize",
     *    summary="Создание пустого приза",
     *    tags={"Пустые призы"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
     * 
     *    @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="В следующий раз"),
     *             required={"name"}
     *         )
     *     ),
     * 
     *    @OA\Response(
     *        response=201,
     *        description="ОК",
     *        @OA\JsonContent(
     *            ref="#/components/schemas/EmptyPrize"
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
    public function store(StoreEmptyPrizeRequest $request)
    {
        $emptyPrize = EmptyPrize::create([
            'name'=>$request->name
        ]);

        return response()->json($emptyPrize, 201);
    }

    /**
     * 
     * @OA\Get(
     *    path="/api/empty-prize/{id}",
     *    summary="Получение пустого приза по id",
     *    tags={"Пустые призы"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
     *    @OA\Parameter(
     *        description="id пустого приза",
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
     *            ref="#/components/schemas/EmptyPrize"
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
     *             @OA\Property(property="message", type="string", example="Пустого приза с таким id не существует")
     *        )
     *    ),
     * )
     */
    public function show($id)
    {
        $emptyPrize = EmptyPrize::findOrFail($id);
        return $emptyPrize;
    }

    /**
     * 
     * @OA\Put(
     *    path="/api/empty-prize/{id}",
     *    summary="Обновление пустого приза по id",
     *    tags={"Пустые призы"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
     *    @OA\Parameter(
     *        description="id пустого приза",
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
     *             @OA\Property(property="name", type="string", example="В следующий раз"),
     *             required={"name"}
     *         )
     *     ),
     * 
     *    @OA\Response(
     *        response=200,
     *        description="ОК",
     *        @OA\JsonContent(
     *            ref="#/components/schemas/EmptyPrize"
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
     *             @OA\Property(property="message", type="string", example="Пустого приза с таким id не существует")
     *        )
     *    ),
     * )
     */
    public function update(UpdateEmptyPrizeRequest $request, $id)
    {
        $emptyPrize = EmptyPrize::findOrFail($id);
        $userPrizes = UserPrize::where('prize_type', EmptyPrize::class)->where('prize_id', $id)->get();
        if(!$userPrizes->isEmpty()){
            return response()->json(["message"=>"Этот приз редактировать нельзя, его выиграли"], 403);
        }
        $emptyPrize->name = $request->name;
        $emptyPrize->save();

        return $emptyPrize;
    }

    /**
     * 
     * @OA\Delete(
     *    path="/api/empty-prize/{id}",
     *    summary="Удаление пустого приза по id",
     *    tags={"Пустые призы"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
     *    @OA\Parameter(
     *        description="id пустого приза",
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
     *             @OA\Property(property="message", type="string", example="Пустой приз успешно удален")
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
     *             @OA\Property(property="message", type="string", example="Пустого приза с таким id не существует")
     *        )
     *    ),
     * )
     */
    public function destroy($id)
    {
        $emptyPrize = EmptyPrize::findOrFail($id);
        $userPrizes = UserPrize::where('prize_type', EmptyPrize::class)->where('prize_id', $id)->get();
        if(!$userPrizes->isEmpty()){
            return response()->json(["message"=>"Этот приз удалить нельзя, его выиграли"], 403);
        }

        $wheel = ActiveWheelService::getActiveWheel();
        $sectors = $wheel->sectors;
        foreach($sectors as $item){
            if($item->prize_type == EmptyPrize::class && $item->prize_id == $id){
                return response()->json(["message"=>"Приз в активном колесе - нельзя удалить"],403);
            }
        }
        $emptyPrize->delete();
        return response()->json(["message"=>"Пустой приз успешно удален"]);
    }
}
