<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promocode;
use App\Http\Requests\StorePromocodeRequest;
use App\Http\Requests\UpdatePromocodeRequest;
use App\Enums\DiscountType;
use App\Models\UserPrize;

class PromocodeController extends Controller
{
    /**
     * 
     * @OA\Get(
     *    path="/api/promocode",
     *    summary="Получение списка промокодов",
     *    tags={"Промокоды"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     *
     *    @OA\Response(
     *        response=200,
     *        description="ОК",
     *      
     *    ),
     * )
     */
    public function index()
    {
        return Promocode::all();
    }

    /**
     * 
     * @OA\Post(
     *    path="/api/promocode",
     *    summary="Создание промокода",
     *    tags={"Промокоды"},
     *    security={{"bearerAuth":{} }},
     * 
     * 
     *    @OA\RequestBody(
     *        @OA\JsonContent(
     *            allOf={
     *                @OA\Schema(
     *                    @OA\Property(property="type_discount", type="enum", example="Процентная"),
     *                    @OA\Property(property="discount_value", type="float", example=15),
     *                    @OA\Property(property="expiry_date", type="date", example="2025-05-01"),
     *                )
     *            }
     *        )
     *    ),
     * 
     *    @OA\Response(
     *        response=201,
     *        description="ОК",
     *        @OA\JsonContent(
     *            @OA\Property(property="name", type="string", example="abc123"),
     *            @OA\Property(property="type_discount", type="enum", example="Процентная"),
     *            @OA\Property(property="discount_value", type="float", example=15),
     *            @OA\Property(property="expiry_date", type="date", example="2025-05-01"),
     *        )
     *    ),
     * )
     */
    public function store(StorePromocodeRequest $request)
    {
        $promocode = Promocode::create([
            'type_discount' => DiscountType::from($request->type_discount),
            'discount_value'=>$request->discount_value,
            'expiry_date'=>$request->expiry_date,
        ]);

        return response()->json($promocode, 201);
    }


    /**
     * 
     * @OA\Get(
     *    path="/api/promocode/{promocode}",
     *    summary="Получение промокода по id",
     *    tags={"Промокоды"},
     *    security={{"bearerAuth":{} }},
     * 
     *    @OA\Parameter(
     *        description="id промокода",
     *        in="path",
     *        name="promocode",
     *        required=true,
     *        example=1
     *    ),
     *
     *    @OA\Response(
     *        response=200,
     *        description="ОК",
     *      
     *    ),
     * )
     */
    public function show(Promocode $promocode)
    {
        $promocode = Promocode::findOrFail($id);
        return $promocode;
    }


    /**
     * 
     * @OA\Put(
     *    path="/api/promocode/{promocode}",
     *    summary="Обновление промокода  по id",
     *    tags={"Промокоды"},
     *    security={{"bearerAuth":{} }},
     * 
     *    @OA\Parameter(
     *        description="id промокода",
     *        in="path",
     *        name="promocode",
     *        required=true,
     *        example=1
     *    ),
     * 
     * 
     *    @OA\RequestBody(
     *        @OA\JsonContent(
     *            allOf={
     *                @OA\Schema(
     *                    @OA\Property(property="type_discount", type="enum", example="Процентная"),
     *                    @OA\Property(property="discount_value", type="float", example=15),
     *                    @OA\Property(property="expiry_date", type="date", example="2025-05-01"),
     *                )
     *            }
     *        )
     *    ),
     * 
     *    @OA\Response(
     *        response=200,
     *        description="ОК",
     *        @OA\JsonContent(
     *            @OA\Property(property="name", type="string", example="abc123"),
     *            @OA\Property(property="type_discount", type="enum", example="Процентная"),
     *            @OA\Property(property="discount_value", type="float", example=15),
     *            @OA\Property(property="expiry_date", type="date", example="2025-05-01"),
     *        )
     *    ),
     * )
     */
    public function update(UpdatePromocodeRequest $request, $id)
    {
        $promocode = Promocode::findOrFail($id);
        $promocode->type_discount = DiscountType::from($request->type_discount);
        $promocode->discount_value = $request->discount_value;
        $promocode->expiry_date = $request->expiry_date;

        $promocode->save();
        return $promocode;
    }


    /**
     * 
     * @OA\Delete(
     *    path="/api/promocode/{promocode}",
     *    summary="Удаление промокода по id",
     *    tags={"Промокоды"},
     *    security={{"bearerAuth":{} }},
     * 
     *    @OA\Parameter(
     *        description="id промокода",
     *        in="path",
     *        name="promocode",
     *        required=true,
     *        example=1
     *    ),
     *
     *    @OA\Response(
     *        response=200,
     *        description="Промокод успешно удален",  
     *    ),
     * 
     *    @OA\Response(
     *        response=403,
     *        description="Этот приз удалить нельзя, его выйграли",  
     *    ),
     * )
     */
    public function destroy($id)
    {
        $promocode = Promocode::findOrFail($id);
        $userPrizes = UserPrize::where('prize_type', Promocode::class)->where('prize_id', $id)->get();
        if(!$userPrizes->isEmpty()){
            return response()->json(["message"=>"Этот приз удалить нельзя, его выйграли"], 403);
        }
        $promocode->delete();
        return response()->json(["message"=>"Промокод успешно удален"]);
    }

}
