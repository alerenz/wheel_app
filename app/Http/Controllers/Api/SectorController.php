<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sector;
use App\Models\Promocode;
use App\Models\PromocodesCode;
use App\Models\MaterialThing;
use App\Models\EmptyPrize;
use App\Models\UserPrize;
use App\Models\Wheel;
use Carbon\Carbon;
use App\Http\Requests\StoreSectorRequest;
use App\Http\Requests\UpdateSectorRequest;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\StatusWheelType;
use App\Services\PrizeTypeService;
use Illuminate\Support\Facades\Config;

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
     *     @OA\Property(property="name", type="string", example="вещь"),
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
     *             @OA\Property(property="name", type="string", example="Промокод на скидку 15%"),
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
     *            @OA\Property(property="name", type="string", example="Промокод на скидку 15%"),
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
        $count_sec = Sector::where('wheel_id', $request->wheel_id)->count();
        if($count_sec == $wheel->count_sectors){
            return response()->json(["message"=>"Нелья добавлять секторы, доступно только ".$wheel->count_sectors." секторов"],403);
        }

        $sectors = Sector::all();
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


        $type_prize = PrizeTypeService::stringToClass($request->prize_type);



        $sector = Sector::create([
            'name'=>$request->name,
            'prize_type'=>$type_prize,
            'prize_id'=>$request->prize_id,
            'wheel_id'=>$request->wheel_id
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
     *             @OA\Property(property="name", type="string", example="Промокод на скидку 15%"),
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
     *            @OA\Property(property="name", type="string", example="Промокод на скидку 15%"),
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

        $sectors = Sector::all();
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
        $type_prize = PrizeTypeService::stringToClass($request->prize_type);
            
        $sector = Sector::findOrFail($id);
        $sector->name = $request->name;
        $sector->prize_type = $type_prize;
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


    /**
     * 
     * @OA\Get(
     *    path="/api/sectors/winSector",
     *    summary="Получение выйгранного сектора",
     *    tags={"Секторы"},
     *    security={{"bearerAuth":{} }},
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
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *        )
     *    ),
     *    @OA\Response(
     *        response=500,
     *        description="Ошибка сервера",
     *        @OA\JsonContent(
     *            @OA\Property(property="error", type="string", example="Произошла ошибка при обработке запроса")
     *        )
     *    )
     * )
     */

    public function getDroppedSector()
    {
        DB::beginTransaction();
        
        try {
            $index = $this->sectorSelection();
            
            $sector = Sector::with('prize')->findOrFail($index);
            $wheel = Wheel::findOrFail($sector->wheel_id);
            $sectors = Sector::where('wheel_id', $sector->wheel_id)->get();
            $user = auth('api')->user();

            $countSectors = Sector::where('wheel_id', $sector->wheel_id)->count();

            $user->attempts = $user->attempts - 1;

            $userPrize = new UserPrize();
            $userPrize->user_id = $user->id;
            $userPrize->prize_type = $sector->prize_type;
            $userPrize->prize_id = $sector->prize_id;
            $userPrize->date = Carbon::now();
            $userPrize->wheel_id = $wheel->id;

            if($sector->prize_type == Promocode::class){
                $promocodesCodes = PromocodesCode::where('promocode_id', $sector->prize_id)
                    ->where('active', true)
                    ->get();

                if ($promocodesCodes->isNotEmpty()) {
                    $code = $promocodesCodes->random();

                    $code->active = false;
                    $code->save();
                    $userPrize->promocodeCode_id = $code->id;
                }

            }
            
            $userPrize->save();

            $max_attempts = config('custom.max_attempts');

            if($sector->prize_type == EmptyPrize::class){
                $empty = EmptyPrize::findOrFail($sector->prize_id);
                if ($empty->name == 'Попытка') {
                    if ($user->attempts < $max_attempts) {
                        $user->attempts = $user->attempts + 1;
                    }
                }
            }
            else if($sector->prize_type == MaterialThing::class){
                $thing = MaterialThing::findOrFail($sector->prize_id);
                $thing->count = $thing->count - 1;
                if($thing->count == 0){
                    $probability = $sector->probability;
                    $probability = $probability / ($countSectors - 1);
                    $sector->probability = 0;
                    foreach($sectors as $item){
                        if($item->id != $sector->id){
                            $item->probability = $item->probability + $probability;
                        }
                    }
                }
            }else if($sector->prize_type == Promocode::class){
                $promocodesCodesCount = PromocodesCode::where('promocode_id',$sector->prize_id)
                ->where('active', true)->count();

                if($promocodesCodesCount == 0){
                    $probability = $sector->probability;
                    $probability = $probability / ($countSectors - 1);
                    $sector->probability = 0;
                    foreach($sectors as $item){
                        if($item->id != $sector->id){
                            $item->probability = $item->probability + $probability;
                        }
                    }
                }
            }
            
            $user->save();

            $sector->prize_type = PrizeTypeService::classToString($sector->prize_type);
            
            DB::commit();
            return response()->json($sector, 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Ошибка в функции getDroppedSector: " . $e->getMessage());
            return response()->json(['error' => 'Произошла ошибка при обработке запроса'], 500);
        }
    }

    private function sectorSelection(){
        $sectors = Sector::all();
        $probabilities = array();
        $sectorsIds = array();
        if(!$sectors->isEmpty()){
            foreach($sectors as $item){
                array_push($probabilities, $item->probability);
                array_push($sectorsIds, $item->id);
            }
        }

        $total = array_sum($probabilities);
        $normalizedProbabilities = array_map(function($p) use ($total) {
            return $p / $total;
        }, $probabilities);

        $cumulativeProbabilities = array();
        $cumulativeSum = 0;

        foreach ($normalizedProbabilities as $prob) {
            $cumulativeSum += $prob;
            array_push($cumulativeProbabilities, $cumulativeSum);
        }

        $randomValue = mt_rand() / mt_getrandmax(); 
        $index = 0;

        for ($i = 0; $i < count($cumulativeProbabilities); $i++) {
            if ($randomValue < $cumulativeProbabilities[$i]) {
                $index = $sectorsIds[$i];
                break;
            }
        }
        return $index;
    }
}
