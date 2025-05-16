<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PromocodesCode;
use App\Models\Promocode;
use App\Http\Requests\StorePromocodesCodeRequest;
use App\Http\Requests\UpdatePromocodesCodeRequest;
use Illuminate\Http\Request;

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
     *    path="/api/promocodes-code",
     *    summary="Получение списка кодов промокодов",
     *    tags={"Коды промокодов"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
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
    public function index(Request $request)
    {
        $query = PromocodesCode::query();
        $sortField = $request->input('sort', 'id');
        $sortOrder = $request->input('order', 'asc');
        $query->orderBy($sortField, $sortOrder);

        $perPage = $request->input('per_page', 10);
        $promocodesCodes = $query->paginate($perPage);
        return response()->json($promocodesCodes, 200);
    }

    /**
     * 
     * @OA\Post(
     *    path="/api/promocodes-code/{id}",
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
            return response()->json(["message"=>"Промокода с таким id не существует"], 404);
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
                    return response()->json(["message"=>"В строке должно быть одно поле с кодами"],422);
                }
                $code = $data[0];

                if($code === null || $code === "" || mb_strlen($code) < 4 || mb_strlen($code) > 8){
                    return response()->json(["message"=>
                    "На ".$row." cтроке указан неверный код промокода, он должен быть от 4 до 8 символов"],422);
                }

                if($allPromocodesCodes !== null){
                    foreach($allPromocodesCodes as $item){
                        if($item->code === $code){
                            return response()->json(["message"=>
                            "На ".$row." cтроке код активации совпадает с другим кодом - ".$item->code],422);
                        }
                    }
                }

                if($promocodesCodes !== null){
                    foreach($promocodesCodes as $item){
                        if($item["code"] === $code){
                            return response()->json(["message"=>
                            "На ".$row." cтроке код активации совпадает с другим кодом - ".$item["code"]],422);
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
            return response()->json(["message"=>"Нельзя открыть файл"],406);
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
