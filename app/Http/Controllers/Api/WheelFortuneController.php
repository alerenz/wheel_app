<?php

namespace App\Http\Controllers\Api;

use App\Enums\StatusWheelType;
use App\Http\Controllers\Controller;
use App\Models\UserPrize;
use App\Models\Wheel;
use App\Services\PrizeTypeService;
use App\Services\WinSectorService;

class WheelFortuneController extends Controller
{
    /**
     * 
     * @OA\Get(
     *    path="/api/wheel-fortune/active-wheel",
     *    summary="Получение активного колеса",
     *    tags={"Колесо фортуны"},
     *    security={{"bearerAuth":{} }},
     * 
     *  
     *
     *    @OA\Response(
     *        response=200,
     *        description="ОК",
     *        @OA\JsonContent(
     *            ref="#/components/schemas/Wheel"
     *        )
     *      
     *    ),
     * 
     *    @OA\Response(
     *        response=404,
     *        description="Not Found",
     *        @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ничего не найдено")
     *        )
     *    ),
     * 
     *    @OA\Response(
     *        response=401,
     *        description="Неавторизованный доступ",
     *        @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *        )
     *    ),
     * )
     */

    public function getActiveWheel()
    {
        $wheel = Wheel::with(['sectors.prize'])
            ->where('status', StatusWheelType::active->value)
            ->orderBy('created_at', 'desc')
            ->first();
        if(!$wheel){
            return response()->json(["message"=>"Ничего не найдено"], 404);
        }
        
        foreach($wheel->sectors as $sector){
            $sector->prize_type = PrizeTypeService::classToString($sector->prize_type);
        }
        return response()->json($wheel, 200);
    }

    /**
     * 
     * @OA\Get(
     *    path="/api/wheel-fortune/win-sector",
     *    summary="Получение выйгранного сектора",
     *    tags={"Колесо фортуны"},
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

    public function getWinSector()
    {
        $sector = WinSectorService::getDroppedSector();
        if(!$sector){
            return response()->json(["message"=>"Ничего не найдено"]);
        }else{
             return response()->json($sector, 200);
        }
    }

    /**
     * 
     * @OA\Get(
     *    path="/api/user-prizes",
     *    summary="Получение списка призов пользователя",
     *    tags={"Колесо фортуны"},
     *    security={{"bearerAuth":{} }},
     * 
     *    @OA\Response(
     *        response=200,
     *        description="ОК",
     *        @OA\JsonContent(
     *            ref="#/components/schemas/UserPrize"
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
     *        response=404,
     *        description="Ничего не найдено",
     *        @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Пользователя с таким id не существует")
     *        )
     *    ),
     * )
     */

    public function getUserPrizes()
    {
        $user = auth('api')->user(); 
        $userPrizes = UserPrize::with('prize', 'wheel')->where('user_id', $user->id)->get();
        if(!$userPrizes->isEmpty()){
            foreach($userPrizes as $item){
                $item->prize_type = PrizeTypeService::classToString($item->prize_type);
            }
        }
        return response()->json($userPrizes, 200);
    }

    /**
     * 
     * @OA\Get(
     *    path="/api/user/attempts",
     *    summary="Получение кол-во попыток пользователя",
     *    tags={"Колесо фортуны"},
     *    security={{"bearerAuth":{} }},
     * 
     *    @OA\Response(
     *        response=200,
     *        description="ОК",
     *        @OA\JsonContent(
     *             @OA\Property(property="attempts", type="integer", example=4)
     *        )
     *      
     *    ),
     *    @OA\Response(
     *        response=401,
     *        description="Неавторизованный доступ",
     *        @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *        )
     *    )
     * )
     */
    public function getUserAttempts()
    {
        $user = auth('api')->user();
        $attempts = $user->attempts;
        return response()->json(["attempts"=>$attempts], 200);
    }
}
