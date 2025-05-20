<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserPrize;
use Illuminate\Http\Request;
use App\Services\PrizeTypeService;

class UserPrizeController extends Controller
{
    /**
     * 
     * @OA\Schema(
     *     schema="UserPrize",
     *     type="object",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="date", type="date",example="2025-06-01"),
     *     @OA\Property(property="prize_type", type="string",example="empty-prize"),
     *     @OA\Property(property="prize_id", type="integer",example=1),
     *     @OA\Property(property="user_id", type="integer",example=1),
     *     @OA\Property(property="wheel_id", type="integer",example=1),
     *     @OA\Property(property="prize", ref="#/components/schemas/Prize"),
     *     @OA\Property(property="user", ref="#/components/schemas/User"),
     *     @OA\Property(property="wheel", ref="#/components/schemas/WheelWithOutSectors"),
     * )
     * 
     * @OA\Get(
     *    path="/api/user-prize",
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
    *    @OA\Parameter(
     *         name="per_page",
     *         description="Количество элементов на странице (по умолчанию 10)",
     *         required=false,
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *    @OA\Response(
     *        response=200,
     *        description="ОК",
     *        @OA\JsonContent(
     *            type="array",
     *            @OA\Items(ref="#/components/schemas/UserPrize")
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
    public function index(Request $request)
    {
        $query = UserPrize::with('prize', 'user', 'wheel');

        $sortField = $request->input('sort', 'id');
        $sortOrder = $request->input('order', 'asc');
        $query->orderBy($sortField, $sortOrder);

        $perPage = $request->input('per_page', 10);
        $userPrizes = $query->paginate($perPage);

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
     *    path="/api/user-prize/{id}",
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
     *             @OA\Property(property="message", type="string", example="Записи с таким id не существует")
     *        )
     *    ),
     * )
     */
    public function show($id)
    {
        $userPrize = UserPrize::with('prize', 'user', 'wheel')->findOrFail($id);
        $userPrize->prize_type = PrizeTypeService::classToString($userPrize->prize_type);
        return response()->json($userPrize, 200);
    }

    
}
