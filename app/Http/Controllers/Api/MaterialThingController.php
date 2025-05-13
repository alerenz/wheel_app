<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaterialThing;
use App\Http\Requests\StoreMaterialThingRequest;
use App\Http\Requests\UpdateMaterialThingRequest;
use App\Models\UserPrize;

class MaterialThingController extends Controller
{

    /**
     * @OA\Schema(
     *     schema="MaterialThing",
     *     type="object",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="Блокнот А5"),
     *     @OA\Property(property="count", type="integer", example=100),
     * )
     *
     *
     * 
     * @OA\Get(
     *    path="/api/material-thing",
     *    summary="Получение списка вещей",
     *    tags={"Вещи"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     *
     *    @OA\Response(
     *        response=200,
     *        description="ОК",
     *        @OA\JsonContent(
     *            type="array",
     *            @OA\Items(ref="#/components/schemas/MaterialThing")
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
    public function index()
    {
        return MaterialThing::all();
    }

    /**
     * 
     * @OA\Post(
     *    path="/api/material-thing",
     *    summary="Создание вещи",
     *    tags={"Вещи"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
     * 
     *    @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Блокнот А5"),
     *             @OA\Property(property="count", type="integer", example=100),
     *             required={"name", "count"}
     *         )
     *     ),
     * 
     *    @OA\Response(
     *        response=201,
     *        description="ОК",
     *        @OA\JsonContent(
     *            ref="#/components/schemas/MaterialThing"
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
    public function store(StoreMaterialThingRequest $request)
    {
        $thing= MaterialThing::create([
            'name'=>$request->name,
            'count'=>$request->count,
        ]);

        return response()->json($thing, 201);
    }

    /**
     * 
     * @OA\Get(
     *    path="/api/material-thing/{id}",
     *    summary="Получение вещи по id",
     *    tags={"Вещи"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
     *    @OA\Parameter(
     *        description="id вещи",
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
     *            ref="#/components/schemas/MaterialThing"
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
     *             @OA\Property(property="message", type="string", example="Вещи с таким id не существует")
     *        )
     *    ),
     * )
     */
    public function show($id)
    {
        $materialThing = MaterialThing::findOrFail($id);
        return $materialThing;
    }

    /**
     * 
     * @OA\Put(
     *    path="/api/material-thing/{id}",
     *    summary="Обновление вещи  по id",
     *    tags={"Вещи"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
     *    @OA\Parameter(
     *        description="id вещи",
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
     *             @OA\Property(property="name", type="string", example="Блокнот А5"),
     *             @OA\Property(property="count", type="integer", example=100),
     *             required={"name"}
     *         )
     *     ),
     * 
     *    @OA\Response(
     *        response=200,
     *        description="ОК",
     *        @OA\JsonContent(
     *            ref="#/components/schemas/MaterialThing"
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
     *             @OA\Property(property="message", type="string", example="Вещи с таким id не существует")
     *        )
     *    ),
     * )
     */
    public function update(UpdateMaterialThingRequest $request, $id)
    {
        $materialThing = MaterialThing::findOrFail($id);
        $userPrizes = UserPrize::where('prize_type', MaterialThing::class)->where('prize_id', $id)->get();
        if(!$userPrizes->isEmpty()){
            return response()->json(["message"=>"Эту вещь редактировать нельзя, его выйграли"], 403);
        }
        $materialThing->name = $request->name;
        $materialThing->count = $request->count;

        $materialThing->save();
        return $materialThing;
    }

    /**
     * 
     * @OA\Delete(
     *    path="/api/material-thing/{id}",
     *    summary="Удаление вещи по id",
     *    tags={"Вещи"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
     *    @OA\Parameter(
     *        description="id вещи",
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
     *             @OA\Property(property="message", type="string", example="Вещь успешна удалена")
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
     *             @OA\Property(property="message", type="string", example="Вещи с таким id не существует")
     *        )
     *    ),
     * )
     */
    public function destroy($id)
    {
        $materialThing = MaterialThing::findOrFail($id);
        $userPrizes = UserPrize::where('prize_type', MaterialThing::class)->where('prize_id', $id)->get();
        if(!$userPrizes->isEmpty()){
            return response()->json(["message"=>"Этот приз удалить нельзя, его выйграли"], 403);
        }
        $materialThing->delete();
        return response()->json(["message"=>"Вещь успешно удалена"]);
    }
}
