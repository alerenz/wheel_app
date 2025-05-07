<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promocode;
use App\Http\Requests\StorePromocodeRequest;
use App\Http\Requests\UpdatePromocodeRequest;
use App\Enums\DiscountType;
use App\Models\UserPrize;
use DateTime;

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
    public function index()
    {
        return Promocode::all();
    }

    /**
     * 
     * @OA\Post(
     *    path="/api/promocode",
     *    summary="Создание промокодов посредством загрузки csv файла",
     *    tags={"Промокоды"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
     * 
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="CSV file to upload"
     *                 ),
     *             )
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
    public function store(StorePromocodeRequest $request)
    {
        $allPromocodes = Promocode::all();

        $file = $request->file('file');
        $promocodes = [];

        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            $headers = null;
            $row = 0;
            while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                
                $row += 1;
                if($headers === null){
                    $headers = $data;
                    continue;
                }

                $data = array_map(function($item) {
                    return mb_convert_encoding($item, 'UTF-8', 'Windows-1251');
                }, $data);


                
                $code = $data[0];

                

                if($code === null || $code === "" || $code === " " || strlen($code) < 4 || strlen($code) > 8){
                    return response()->json(["message"=>
                    "На ".$row." cтроке указан неверный код промокода, он должен быть от 4 до 8 символов"]);
                }

                if($allPromocodes !== null){
                    foreach($allPromocodes as $item){
                        if($item->code === $code){
                            return response()->json(["message"=>
                            "На ".$row." cтроке код активации совпадает с другим кодом - ".$item->code]);
                        }
                    }
                }

                if($promocodes !== null){
                    foreach($promocodes as $item){
                        if($item["code"] === $code){
                            return response()->json(["message"=>
                            "На ".$row." cтроке код активации совпадает с другим кодом - ".$item["code"]]);
                        }
                    }
                }

                $type_discount = $data[1];
                if(!in_array($type_discount, array_column(DiscountType::cases(), 'value'))){
                    return response()->json(["message"=>"На ".$row." cтроке указан неверный тип скидки"]);
                }
                $discount_value = $data[2];
                if($discount_value === null || $discount_value < 0 || !is_numeric($discount_value)){
                    return response()->json(["message"=>"На ".$row." cтроке указано неверное значение скидки, оно должно 
                    быть положительным числом"]);
                }

                $expiry_date = $data[3];
                if($expiry_date !== null){
                    $expiry_date = date("Y-m-d", strtotime($expiry_date));
                }
                else{
                    return response()->json(["message"=>"На ".$row." cтроке нету даты или же неверный формат"]);
                }                
                $today = date("Y-m-d");
                if($expiry_date < $today){
                    return response()->json(["message"=>"На ".$row." cтроке дата окончания должна быть позже ".$today]);
                }

                $promocode = [
                    "code" => $code,
                    "type_discount" => $type_discount,
                    "discount_value" => $discount_value,
                    "expiry_date" => $expiry_date,
                ];

                $promocodes[] = $promocode;
            }
            fclose($handle);
        }else{
            return response()->json(["message"=>"Нельзя открыть файл"]);
        }

        $newPromocodes = [];
        foreach($promocodes as $item){
            $promocode = Promocode::create([
                'code'=>$item["code"],
                'type_discount'=>$item["type_discount"],
                'discount_value'=>$item["discount_value"],
                'expiry_date'=>$item["expiry_date"]
            ]);
            $newPromocodes[] = $promocode;
        }

        return response()->json($newPromocodes, 201);
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
    public function show(Promocode $promocode)
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
     *             @OA\Property(
     *                 property="type_discount",
     *                 type="string",
     *                 enum={"Процентная", "Фиксированная"},
     *                 example="Фиксированная"
     *             ),
     *             @OA\Property(property="discount_value", type="float", example=15),
     *             @OA\Property(property="expiry_date", type="date", example="2025-05-01"),
     *             required={"type_discount", "discount_value","expiry_date"}
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
     *             @OA\Property(property="message", type="string", example="Промокода с таким id не существует")
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
            return response()->json(["message"=>"Этот приз удалить нельзя, его выйграли"], 403);
        }
        $promocode->delete();
        return response()->json(["message"=>"Промокод успешно удален"]);
    }

}
