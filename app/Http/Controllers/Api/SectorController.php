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
     * Display a listing of the resource.
     */
    public function index()
    {
        $sectors = Sector::with('prize')->get();
        if(!$sectors->isEmpty()){
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
        return response()->json($sectors, 200);
    }

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
                return response()->json(["message"=>"Неверный тип приза, выберите промокод, или вещь или пустой приз"], 404);
                break;
        }


        $sector = Sector::create([
            'name'=>$request->name,
            'prize_type'=>$type_prize,
            'prize_id'=>$request->prize_id,
            'probability'=>$request->probability,
            'wheel_id'=>$request->wheel_id,
            'count'=>$request->count
        ]);
        return response()->json($sector,201);
       
    }

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
