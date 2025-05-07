<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmptyPrize;
use App\Http\Requests\StoreEmptyPrizeRequest;
use App\Http\Requests\UpdateEmptyPrizeRequest;
use App\Models\UserPrize;

class EmptyPrizeController extends Controller
{
    /**
     * 
     * @OA\Get(
     *    path="/api/empty-prize",
     *    summary="Получение списка пустых призов",
     *    tags={"Пустые призы"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     *
     *    @OA\Response(
     *        response=200,
     *        description="ОК",
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
    public function index()
    {
        return EmptyPrize::all();
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
     *             @OA\Property(property="name", type="string", example="Попытка"),
     *             required={"name"}
     *         )
     *     ),
     * 
     *    @OA\Response(
     *        response=201,
     *        description="ОК",
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
     *             @OA\Property(property="name", type="string", example="Попытка"),
     *             required={"name"}
     *         )
     *     ),
     * 
     *    @OA\Response(
     *        response=200,
     *        description="ОК",
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
            return response()->json(["message"=>"Этот приз удалить нельзя, его выйграли"], 403);
        }
        $emptyPrize->delete();
        return response()->json(["message"=>"Пустой приз успешно удален"]);
    }
}
