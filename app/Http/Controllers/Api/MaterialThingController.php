<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material_thing;
use App\Http\Requests\StoreMaterial_thingRequest;
use App\Http\Requests\UpdateMaterial_thingRequest;
use App\Models\UserPrize;

class MaterialThingController extends Controller
{
    /**
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
        return Material_thing::all();
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
    public function store(StoreMaterial_thingRequest $request)
    {
        $thing= Material_thing::create([
            'name'=>$request->name,
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
        $material_thing = Material_thing::findOrFail($id);
        return $material_thing;
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
     *             @OA\Property(property="message", type="string", example="Вещи с таким id не существует")
     *        )
     *    ),
     * )
     */
    public function update(UpdateMaterial_thingRequest $request, $id)
    {
        $material_thing = Material_thing::findOrFail($id);
//        если выиграли приз, то его можно отредактировать?
//        один приз может быть у разных колес?
        $material_thing->name = $request->name;

        $material_thing->save();
        return $material_thing;
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
        $material_thing = Material_thing::findOrFail($id);
        $userPrizes = UserPrize::where('prize_type', Material_thing::class)->where('prize_id', $id)->get();
        if(!$userPrizes->isEmpty()){
            return response()->json(["message"=>"Этот приз удалить нельзя, его выйграли"], 403);
        }
        $material_thing->delete();
        return response()->json(["message"=>"Вещь успешно удалена"]);
    }
}
