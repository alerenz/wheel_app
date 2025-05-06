<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserPrize;
use App\Models\Wheel;
use App\Models\Promocode;
use App\Models\Material_thing;
use App\Models\Empty_prize;
use App\Http\Requests\StoreUserPrizeRequest;
use App\Http\Requests\UpdateUserPrizeRequest;

class UserPrizeController extends Controller
{
    /**
     * 
     * @OA\Get(
     *    path="/api/userPrize",
     *    summary="Получение списка призов, выйгранные пользователями",
     *    tags={"Призы пользователей"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     *    
    *     @OA\Parameter(
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
    public function index(Request $request)
    {
        $query = UserPrize::with('prize');

        $sortField = $request->input('sort', 'id');
        $sortOrder = $request->input('order', 'asc');
        $query->orderBy($sortField, $sortOrder);


        $userPrizes = $query->get();
        if(!$userPrizes->isEmpty()){
            foreach($userPrizes as $item){
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
        return response()->json($userPrizes, 200);
    }


    /**
     * 
     * @OA\Get(
     *    path="/api/userPrize/{id}",
     *    summary="Получение приза по id",
     *    tags={"Призы пользователей"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
     *    @OA\Parameter(
     *        description="id приза",
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
     *        response=404,
     *        description="Ничего не найдено",
     *        @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Записи с таким id не существует")
     *        )
     *    ),
     * )
     */
    public function show($id)
    {
        $userPrize = UserPrize::with('prize')->findOrFail($id);
        switch($userPrize->prize_type){
            case Promocode::class:
                $userPrize->prize_type = "promocode";
                break;
            case Material_thing::class:
                $userPrize->prize_type = "material_thing";
                break;  
            case Empty_prize::class:
                $userPrize->prize_type = "empty_prize";
                break;
        }
    }

/**
     * 
     * @OA\Get(
     *    path="/api/userPrizes/user/{id}",
     *    summary="Получение списка призов пользователя по его id",
     *    tags={"Призы пользователей"},
     *    security={{"bearerAuth":{} }},
     * 
     *    @OA\Parameter(
     *        description="id пользователя",
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
     *        response=404,
     *        description="Ничего не найдено",
     *        @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Пользователя с таким id не существует")
     *        )
     *    ),
     * )
     */

    public function get_user_prizes($userId){

        $userPrizes = UserPrize::with('prize')->where('user_id', $userId)->get();
        if(!$userPrizes->isEmpty()){
            foreach($userPrizes as $item){
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
        return response()->json($userPrizes, 200);
    }
}
