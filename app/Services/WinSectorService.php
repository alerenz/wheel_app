<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\Sector;
use App\Models\Promocode;
use App\Models\PromocodesCode;
use App\Models\MaterialThing;
use App\Models\EmptyPrize;
use App\Models\UserPrize;
use App\Models\Wheel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\StatusWheelType;
use App\Services\PrizeTypeService;

class WinSectorService
{
    public static function getDroppedSector()
    {
        DB::beginTransaction();
        try {
            $wheel = Wheel::with(['sectors.prize'])
            ->where('status', StatusWheelType::active->value)
            ->orderBy('created_at', 'desc')
            ->first();

            if (!$wheel || $wheel->sectors->isEmpty()) {
                return null;
            }

            $sectors = $wheel->sectors;
            $countSectors = $sectors->count();
            $sector = self::selectSector($sectors);

            if($sector == null){
                return null;
            }

            $user = auth('api')->user();

            $user->attempts--;
            if ($user->attempts < 0) {
                $user->attempts = 0;
            }

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

                if (!$promocodesCodes->isEmpty()) {
                    $code = $promocodesCodes->random();
                    $code->active = false;
                    $code->save();
                    $userPrize->promocodeCode_id = $code->id;
                }

            }
            $userPrize->save();
            $maxAttempts = config('custom.max_attempts');

            if($sector->prize_type == Attempt::class){
                if ($user->attempts < $maxAttempts) {
                    $user->attempts = $user->attempts + 1;  
                }
            }
            else if($sector->prize_type == MaterialThing::class){
                $thing = MaterialThing::findOrFail($sector->prize_id);
                $thing->count = $thing->count - 1;
                $thing->save();
                if($thing->count == 0){
                    $probability = $sector->probability;
                    $probability = $probability/($countSectors - 1);
                    $sector->probability = 0;
                    foreach($sectors as $item){
                        if($item->id != $sector->id){
                            $item->probability = $item->probability + $probability;
                        }
                    }
                    $sector->save();
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
                    $sector->save();
                }
            }

            $user->save();
            
            $sector->prize_type = PrizeTypeService::classToString($sector->prize_type);
            DB::commit();
            return $sector;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Ошибка в функции getDroppedSector: " . $e->getMessage());
            return null;
        }
    }

    private static function selectSector($sectors)
    {
        $probabilities = [];
        $sectorsIds = [];
        
        if (!$sectors->isEmpty()) {
            foreach ($sectors as $item) {
                $probabilities[] = $item->probability;
                $sectorsIds[] = $item->id;
            }
            $total = array_sum($probabilities);
            $normalizedProbabilities = array_map(function($p) use ($total) {
                return $p / $total;
            }, $probabilities);

            $cumulativeProbabilities = [];
            $cumulativeSum = 0;
            foreach ($normalizedProbabilities as $prob) {
                $cumulativeSum += $prob;
                $cumulativeProbabilities[] = $cumulativeSum;
            }
            
            $randomValue = mt_rand() / mt_getrandmax();
            $index = 0;
            
            for ($i = 0; $i < count($cumulativeProbabilities); $i++) {
                if ($randomValue < $cumulativeProbabilities[$i]) {
                    $index = $sectorsIds[$i];
                    break;
                }
            }
            
            return $sectors->firstWhere('id', $index);
        }

        return null; 
    }
}
