<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sector;
use App\Models\Promocode;
use App\Models\Material_thing;
use App\Models\Empty_prize;
use App\Models\UserPrize;
use App\Models\Wheel;
use Carbon\Carbon;
use App\Http\Requests\StoreSectorRequest;
use App\Http\Requests\UpdateSectorRequest;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\StatusWeelType;

class SectorController extends Controller
{
    /**
     * 
     * @OA\Get(
     *    path="/api/sector",
     *    summary="Получение списка секторов",
     *    tags={"Секторы"},
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
        $sectors = Sector::with('prize')->get();
        if(!$sectors->isEmpty()){
            foreach($sectors as $item){
                switch($item->prize_type){
                    case Promocode::class:
                        $item->prize_type = "promocode";
                        break;
                    case Material_thing::class:
                        $item->prize_type = "material_thing";
                        break;  
                    case Empty_prize::class:
                        $item->prize_type = "empty_prize";
                        break;
                }
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
     *             @OA\Property(property="count", type="integer", example=100),
     *             required={"name", "prize_type","prize_id", "wheel_id", "count"}
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


        $type_prize = $request->prize_type;

         switch ($type_prize) {
            case 'promocode':
                $type_prize = Promocode::class;
                break;
            case 'material_thing':
                $type_prize = Material_thing::class;
                break;
            case 'empty_prize':
                $type_prize = Empty_prize::class;
                break;
            default:
                return response()->json(["message"=>"Неверный тип приза, выберите промокод, или вещь или пустой приз"], 403);
                break;
        }


        $sector = Sector::create([
            'name'=>$request->name,
            'prize_type'=>$type_prize,
            'prize_id'=>$request->prize_id,
            'wheel_id'=>$request->wheel_id,
            'count'=>$request->count
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
        switch($sector->prize_type){
            case Promocode::class:
                $sector->prize_type = "promocode";
                break;
            case Material_thing::class:
                $sector->prize_type = "material_thing";
                break;  
            case Empty_prize::class:
                $sector->prize_type = "empty_prize";
                break;
        }
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
     *             @OA\Property(property="count", type="integer", example=100),
     *             required={"name", "prize_type","prize_id", "probability", "wheel_id", "count"}
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
        $type_prize = $request->prize_type;

         switch ($type_prize) {
            case 'promocode':
                $type_prize = Promocode::class;
                break;
            case 'material_thing':
                $type_prize = Material_thing::class;
                break;
            case 'empty_prize':
                $type_prize = Empty_prize::class;
                break;
            default:
                throw new \InvalidArgumentException('Некорректный тип приза');
                break;
            }
            
        $sector = Sector::findOrFail($id);
        $sector->name = $request->name;
        $sector->prize_type = $type_prize;
        $sector->prize_id = $request->prize_id;
        $sector->probability = $request->probability;
        $sector->wheel_id = $request->wheel_id;
        $sector->count = $request->count;
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
        if($wheel->status == StatusWeelType::active->value){
            return response()->json(["message"=>"При активном колесе нельзя удалить сектор"], 403);
        }
        $sector->delete();
        return response()->json(["message"=>"Сектор успешно удален"]);
    }


    /**
     * 
     * @OA\Get(
     *    path="/api/sectors/droppedSector",
     *    summary="Получение выйгранного сектора",
     *    tags={"Секторы"},
     *    security={{"bearerAuth":{} }},
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
            
            $user->attempts = $user->attempts - 1;
            
            $userPrize = new UserPrize();
            $userPrize->user_id = $user->id;
            $userPrize->prize_type = $sector->prize_type;
            $userPrize->prize_id = $sector->prize_id;
            $userPrize->date = Carbon::now();
            $userPrize->wheel_id = $wheel->id;
            $userPrize->save();

            if($sector->prize_type == Empty_prize::class){
                $empty = Empty_prize::findOrFail($sector->prize_id);
                if ($empty->name == 'Попытка') {
                    if ($user->attempts < 5) {
                        $user->attempts = $user->attempts + 1;
                    }
                }
            }else if($sector->prize_type == Material_thing::class || $sector->prize_type == Promocode::class){
                $sector->count = $sector->count - 1;
                if($sector->count == 0){
                    $probability = $sector->probability;
                    $probability = $probability / ($sectors->count() - 1);
                    $sector->probability = 0;
                    foreach($sectors as $item){
                        if($item->id != $sector->id){
                            $item->probability = $item->probability + $probability;
                        }
                    }
                }
            }
            
            $user->save();

            switch ($sector->prize_type) {
                case Promocode::class:
                    $sector->prize_type = "promocode";
                    break;
                    
                case Material_thing::class:
                    $sector->prize_type = "material_thing";
                    break;
                    
                case Empty_prize::class:
                    $sector->prize_type = "empty_prize";
                    break;
            }
            
            DB::commit();
            return response()->json($sector, 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Ошибка в getDroppedSector: " . $e->getMessage());
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
