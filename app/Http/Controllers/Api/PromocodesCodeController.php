<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PromocodesCode;
use App\Models\Promocode;
use App\Http\Requests\StorePromocodesCodeRequest;
use App\Http\Requests\UpdatePromocodesCodeRequest;

class PromocodesCodeController extends Controller
{
/**
     * 
     * 
     * @OA\Schema(
     *     schema="PromocodesCode",
     *     type="object",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="code", type="string", example="ABC123"),
     *     @OA\Property(property="active", type="boolean", example="true"),
     *     @OA\Property(property="promocode_id", type="integer", example=1),
     * )
     * 
     * @OA\Get(
     *    path="/api/promocodesCode",
     *    summary="Получение списка кодов промокодов",
     *    tags={"Коды промокодов"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     *
     *    @OA\Response(
     *        response=200,
     *        description="ОК",
     *        @OA\JsonContent(
     *            type="array",
     *            @OA\Items(ref="#/components/schemas/PromocodesCode")
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
    public function index()
    {
        return PromocodesCode::all();
    }

    /**
     * 
     * @OA\Post(
     *    path="/api/promocodesCode/{id}",
     *    summary="Создание промокодов посредством загрузки csv файла",
     *    tags={"Коды промокодов"},
     *    security={{"bearerAuth":{"role": "admin"} }},
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
     *        @OA\JsonContent(
     *            ref="#/components/schemas/PromocodesCode"
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
    public function store(StorePromocodesCodeRequest $request, $id)
    {
        $promocode = Promocode::findOrFail($id);
        if($promocode == null){
            return response()->json(["message"=>"Промокода с таким id не существует"]);
        }
        $allPromocodesCodes = PromocodesCode::all();

        $file = $request->file('file');
        
        $promocodesCodes = [];

        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            $row = 0;
            while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                $row += 1;
                $data = array_map(function($item) {
                    return mb_convert_encoding($item, 'UTF-8', 'Windows-1251');
                }, $data);

                $num = count($data);
                
                if($num !== 1){
                    return response()->json(["message"=>"В строке должно быть одно поле с кодами"]);
                }
                $code = $data[0];

                if($code === null || $code === "" || $code === " " || strlen($code) < 4 || strlen($code) > 8){
                    return response()->json(["message"=>
                    "На ".$row." cтроке указан неверный код промокода, он должен быть от 4 до 8 символов"]);
                }

                if($allPromocodesCodes !== null){
                    foreach($allPromocodesCodes as $item){
                        if($item->code === $code){
                            return response()->json(["message"=>
                            "На ".$row." cтроке код активации совпадает с другим кодом - ".$item->code]);
                        }
                    }
                }

                if($promocodesCodes !== null){
                    foreach($promocodesCodes as $item){
                        if($item["code"] === $code){
                            return response()->json(["message"=>
                            "На ".$row." cтроке код активации совпадает с другим кодом - ".$item["code"]]);
                        }
                    }
                }

                $promocodesCode = [
                    "code" => $code,
                    "active"=>true,
                    "promocode_id" => $id,
                ];

                $promocodesCodes[] = $promocodesCode;
            }
            fclose($handle);
        }else{
            return response()->json(["message"=>"Нельзя открыть файл"]);
        }

        $newPromocodesCodes = [];
        foreach($promocodesCodes as $item){
            $promocodeCode = PromocodesCode::create([
                'code'=>$item["code"],
                'active'=>$item["active"],
                'promocode_id'=>$item["promocode_id"]
            ]);
            $newPromocodesCodes[] = $promocodeCode;
        }

        return response()->json($newPromocodesCodes, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PromocodesCode $promocodesCode)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePromocodesCodeRequest $request, PromocodesCode $promocodesCode)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PromocodesCode $promocodesCode)
    {
        //
    }
}
