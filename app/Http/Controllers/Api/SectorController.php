<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sector;
use App\Models\Wheel;
use App\Http\Requests\StoreSectorRequest;
use App\Http\Requests\UpdateSectorRequest;
use App\Enums\StatusWheelType;
use App\Services\PrizeTypeService;

class SectorController extends Controller
{
    /**
     * 
     * 
     *@OA\Schema(
     *     schema="Sector",
     *     type="object",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="prize_type", type="string", example="material_thing"),
     *     @OA\Property(property="prize_id", type="integer", example=1),
     *     @OA\Property(property="probability", type="string", example="15"),
     *     @OA\Property(property="wheel_id", type="integer", example=1),
     *     @OA\Property(property="prize", ref="#/components/schemas/Prize")
     * )
     *
     * @OA\Schema(
     *     schema="Prize",
     *     type="object",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="Ручка синяя шариковая"),
     *     @OA\Property(property="count", type="integer", example=10)
     * )
     * 
     * @OA\Get(
     *    path="/api/sector",
     *    summary="Получение списка секторов",
     *    tags={"Секторы"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     *
     *        @OA\Response(
     *        response=200,
     *        description="ОК",
     *        @OA\JsonContent(
     *            type="array",
     *            @OA\Items(ref="#/components/schemas/Sector")
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
     * )
     */
    public function index()
    {
        $sectors = Sector::with('prize')->get();
        if(!$sectors->isEmpty()){
            foreach($sectors as $item){
                $item->prize_type = PrizeTypeService::classToString($item->prize_type);
            }
        }
        return response()->json($sectors, 200);
    }

    /**
     * 
     * @OA\Post(
     *    path="/api/sector",
     *    summary="Создание сектора",
     *    tags={"Секторы"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
     * 
     *    @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="prize_type",
     *                 type="string",
     *                 enum={"promocode", "material_thing", "empty_prize"},
     *                 example="promocode"
     *             ),
     *             @OA\Property(property="prize_id", type="integer", example=1),
     *             @OA\Property(property="wheel_id", type="integer", example=1),
     *             required={"name", "prize_type","prize_id", "wheel_id"}
     *         )
     *     ),
     * 
     *    @OA\Response(
     *        response=201,
     *        description="ОК",
     *        @OA\JsonContent(
     *            @OA\Property(property="prize_type",type="string",example="promocode"),
     *            @OA\Property(property="prize_id", type="integer", example=1),
     *            @OA\Property(property="wheel_id", type="integer", example=1),
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

    public function store(StoreSectorRequest $request)
    {
        $wheel = Wheel::findOrFail($request->wheel_id);
        if($wheel->status != "Не активно"){
            return response()->json(["message"=>"Нелья добавлять секторы"],403);
        }
        $sectors = Sector::where('wheel_id', $wheel->id)->get();
        $count_sec = $sectors->count();
        if($count_sec == $wheel->count_sectors){
            return response()->json(["message"=>"Нелья добавлять секторы, доступно только ".$wheel->count_sectors." секторов"],403);
        }

        if(!$sectors->isEmpty()){
            $sum = 0;
            foreach($sectors as $item){
                $sum += $item->probability;
            }

            $sum += $request->probability;
            if($sum > 100){
                return response()->json(["message"=>"Общая сумма вероятностей превышает 100%"],403);
            }
        }

        $typePrize = PrizeTypeService::stringToClass($request->prize_type);

        $sector = Sector::create([
            'prize_type'=>$typePrize,
            'prize_id'=>$request->prize_id,
            'wheel_id'=>$request->wheel_id,
            'probability'=>0,
        ]);
        return response()->json($sector,201);
       
    }

    /**
     * 
     * @OA\Get(
     *    path="/api/sector/{id}",
     *    summary="Получение сектора по id",
     *    tags={"Секторы"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
     *    @OA\Parameter(
     *        description="id сектора",
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
     *            ref="#/components/schemas/Sector"
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
     *             @OA\Property(property="message", type="string", example="Сектора с таким id не существует")
     *        )
     *    ),
     * )
     */

    public function show($id)
    {
        $sector = Sector::with('prize')->findOrFail($id);
        $sector->prize_type = PrizeTypeService::classToString($sector->prize_type);
        return response()->json($sector, 200);
    }

    /**
     * 
     * @OA\Put(
     *    path="/api/sector/{id}",
     *    summary="Обновление сектора по id",
     *    tags={"Секторы"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
     *    @OA\Parameter(
     *        description="id сектора",
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
     *                 property="prize_type",
     *                 type="string",
     *                 enum={"promocode", "material_thing", "empty_prize"},
     *                 example="promocode"
     *             ),
     *             @OA\Property(property="prize_id", type="integer", example=1),
     *             @OA\Property(property="probability", type="float", example=15),
     *             @OA\Property(property="wheel_id", type="integer", example=1),
     *             required={"name", "prize_type","prize_id", "probability", "wheel_id", "count"}
     *         )
     *     ),
     * 
     *    @OA\Response(
     *        response=200,
     *        description="ОК",
     *        @OA\JsonContent(
     *            @OA\Property(property="prize_type",type="string",example="promocode"),
     *            @OA\Property(property="prize_id", type="integer", example=1),
     *            @OA\Property(property="probability", type="float", example=15),
     *            @OA\Property(property="wheel_id", type="integer", example=1),
     *            
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
     *            @OA\Property(property="message", type="string", example="Общая сумма вероятностей превышает 100%")
     *        )
     *    ),
     * 
     *   
     *    @OA\Response(
     *        response=404,
     *        description="Ничего не найдено",
     *        @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Сектора с таким id не существует")
     *        )
     *    ),
     * )
     */

    public function update(UpdateSectorRequest $request, $id)
    {
        $sector = Sector::findOrFail($id);
        
        $sectors = Sector::where('wheel_id', $request->wheel_id)->get();
        if(!$sectors->isEmpty()){
            $sum = 0;
            foreach($sectors as $item){
                $sum += $item->probability;
            }

            $sum += $request->probability - $sector->probability;
            if($sum > 100){
                return response()->json(["message"=>"Общая сумма вероятностей превышает 100%"],403);
            }
        }

        $typePrize = PrizeTypeService::stringToClass($request->prize_type);
            
        
        $sector->prize_type = $typePrize;
        $sector->prize_id = $request->prize_id;
        $sector->probability = $request->probability;
        $sector->wheel_id = $request->wheel_id;
        $sector->save();

        return response()->json($sector, 200);
    }

    /**
     * 
     * @OA\Delete(
     *    path="/api/sector/{id}",
     *    summary="Удаление сектора по id",
     *    tags={"Секторы"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
     *    @OA\Parameter(
     *        description="id сектора",
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
     *             @OA\Property(property="message", type="string", example="Сектор успешно удален")
     *        )  
     *    ),
     * 
     *    @OA\Response(
     *        response=403,
     *        description="При активном колесе нельзя удалить сектор",  
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
     *             @OA\Property(property="message", type="string", example="Сектора с таким id не существует")
     *        )
     *    ),
     * )
     */

    public function destroy($id)
    {
        $sector = Sector::findOrFail($id);
        $wheel = Wheel::findOrFail($sector->wheel_id);
        if($wheel->status == StatusWheelType::active->value){
            return response()->json(["message"=>"При активном колесе нельзя удалить сектор"], 403);
        }
        $sector->delete();
        return response()->json(["message"=>"Сектор успешно удален"]);
    }


    
}