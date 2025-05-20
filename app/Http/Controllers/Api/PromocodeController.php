<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promocode;
use App\Http\Requests\StorePromocodeRequest;
use App\Http\Requests\UpdatePromocodeRequest;
use App\Models\UserPrize;
use App\Services\ActiveWheelService;
use Illuminate\Http\Request;

class PromocodeController extends Controller
{
    /**
     * 
     * 
     * @OA\Schema(
     *     schema="Promocode",
     *     type="object",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="Скидка 10% на пиццу"),
     * )
     * 
     * @OA\Get(
     *    path="/api/promocode",
     *    summary="Получение списка промокодов",
     *    tags={"Промокоды"},
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
     *            @OA\Items(ref="#/components/schemas/Promocode"),
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
    public function index(Request $request)
    {
        $query = Promocode::withCount(['codes' => function ($q) {
            $q->where('active', true);
        }]);
        if ($request->has('name') && $request->name != '') {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }
        $sortField = $request->input('sort', 'id');
        $sortOrder = $request->input('order', 'asc');
        $query->orderBy($sortField, $sortOrder);

        $perPage = $request->input('per_page', 10);
        $promocodes = $query->paginate($perPage);

        return response()->json($promocodes, 200);
    }

    /**
     * 
     * @OA\Post(
     *    path="/api/promocode",
     *    summary="Создание промокода",
     *    tags={"Промокоды"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
     * 
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Скидка на пиццу 10%"),
     *             required={"name"}
     *         )
     *     ),
     * 
     *    @OA\Response(
     *        response=201,
     *        description="ОК",
     *        @OA\JsonContent(
     *            @OA\Property(property="id", type="integer", example=1),
     *            @OA\Property(property="name", type="string", example="Скидка на пиццу 10%"),
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
    
    public function store(StorePromocodeRequest $request)
    {
        $promocode = Promocode::create([
            'name'=>$request->name
        ]);

        return response()->json($promocode, 201);
    }


    /**
     * 
     * @OA\Get(
     *    path="/api/promocode/{id}",
     *    summary="Получение промокода по id",
     *    tags={"Промокоды"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
     *    @OA\Parameter(
     *        description="id промокода",
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
     *            ref="#/components/schemas/Promocode"
     *        )
     *      
     *    ),
     *    @OA\Response(
     *        response=401,
     *        description="Неавторизованный доступ",
     *        @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *        )
     *    ),
     *    @OA\Response(
     *        response=403,
     *        description="Доступ запрещен",
     *        @OA\JsonContent(
     *            @OA\Property(property="message", type="string", example="Forbidden")
     *        )
     *    ),
     *    @OA\Response(
     *        response=404,
     *        description="Ничего не найдено",
     *        @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Промокода с таким id не существует")
     *        )
     *    ),
     * )
     */
    public function show($id)
    {
        $promocode = Promocode::findOrFail($id);
        return $promocode;
    }


    /**
     * 
     * @OA\Put(
     *    path="/api/promocode/{id}",
     *    summary="Обновление промокода  по id",
     *    tags={"Промокоды"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
     *    @OA\Parameter(
     *        description="id промокода",
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
     *             @OA\Property(property="name", type="string", example="Промокод на скидку 15 процентов"),
     *             required={"name"}
     *         )
     *     ),
     * 
     *    @OA\Response(
     *        response=200,
     *        description="ОК",
     *        @OA\JsonContent(
     *            ref="#/components/schemas/Promocode"
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
     *             @OA\Property(property="message", type="string", example="Промокода с таким id не существует")
     *        )
     *    ),
     * )
     */
    public function update(UpdatePromocodeRequest $request, $id)
    {
        $promocode = Promocode::findOrFail($id);
        $userPrizes = UserPrize::where('prize_type', Promocode::class)->where('prize_id', $id)->get();
        if(!$userPrizes->isEmpty()){
            return response()->json(["message"=>"Этот промокод редактировать нельзя, его выиграли"], 403);
        }
        $promocode->name = $request->name;
        $promocode->save();
        return $promocode;
    }


    /**
     * 
     * @OA\Delete(
     *    path="/api/promocode/{id}",
     *    summary="Удаление промокода по id",
     *    tags={"Промокоды"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
     *    @OA\Parameter(
     *        description="id промокода",
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
     *             @OA\Property(property="message", type="string", example="Промокод успешно удален")
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
     *             @OA\Property(property="message", type="string", example="Промокода с таким id не существует")
     *        )
     *    ),
     * 
     * )
     */
    public function destroy($id)
    {
        $promocode = Promocode::findOrFail($id);
        $userPrizes = UserPrize::where('prize_type', Promocode::class)->where('prize_id', $id)->get();
        if(!$userPrizes->isEmpty()){
            return response()->json(["message"=>"Этот приз удалить нельзя, его выиграли"], 403);
        }

        $wheel = ActiveWheelService::getActiveWheel();
        $sectors = $wheel->sectors;
        foreach($sectors as $item){
            if($item->prize_type == Promocode::class && $item->prize_id == $id){
                return response()->json(["message"=>"Приз в активном колесе - нельзя удалить"],403);
            }
        }
        $promocode->delete();
        return response()->json(["message"=>"Промокод успешно удален"]);
    }

}
