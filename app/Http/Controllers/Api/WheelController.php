<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wheel;
use App\Models\Promocode;
use App\Models\Material_thing;
use App\Models\Empty_prize;
use App\Http\Requests\StoreWheelRequest;
use App\Http\Requests\UpdateWheelRequest;
use App\Models\Sector;
use App\Enums\StatusWeelType;
use Illuminate\Http\Request;

class WheelController extends Controller
{
    /**
    * 
    * @OA\Get(
    *    path="/api/wheel",
    *    summary="Получение списка колес",
    *    tags={"Колеса"},
    *    security={{"bearerAuth":{"role": "admin"} }},
    *    @OA\Parameter(
    *         name="name",
    *         in="query",
    *         description="Фильтрация по имени колеса",
    *         required=false,
    *         @OA\Schema(type="string")
    *     ),
    *     @OA\Parameter(
    *         name="status",
    *         in="query",
    *         description="Фильтрация по статусу",
    *         required=false,
    *         @OA\Schema(type="string")
    *     ),
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
     *    
     * )
     */
    public function index(Request $request)
    {
        $query = Wheel::with(['sectors.prize']);

        if ($request->has('name') && $request->name != '') {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $sortField = $request->input('sort', 'id');
        $sortOrder = $request->input('order', 'asc');
        $query->orderBy($sortField, $sortOrder);

        $wheels = $query->get();

        foreach ($wheels as $wheel) {
            $wheel->days_of_week = json_decode($wheel->days_of_week);

            foreach($wheel->sectors as $sector){
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
            }
        }

        return response()->json($wheels, 200);
    }

    /**
     * 
     * @OA\Post(
     *    path="/api/wheel",
     *    summary="Создание колеса",
     *    tags={"Колеса"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
     * 
     *    @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Акция мая"),
     *             @OA\Property(property="count_sectors", type="integer", example=5),
     *             @OA\Property(property="animation", type="boolean", example=true),
     *             @OA\Property(property="date_start", type="date", example="2025-05-01"),
     *             @OA\Property(property="date_end", type="date", example="2025-05-31"),
     *             @OA\Property(property="days_of_week", type="array", @OA\Items(type="string"),example={"Понедельник", "Среда"}),
     *             required={"name", "count_sectors","animation", "date_start", "date_end", "days_of_week"}
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
    public function store(StoreWheelRequest $request)
    {
        
        $wheel = Wheel::create([
            'name'=>$request->name,
            'count_sectors'=>$request->count_sectors,
            'animation'=>$request->animation,
            'date_start'=>$request->date_start,
            'date_end'=>$request->date_end,
            'days_of_week' => json_encode($request->days_of_week),
        ]);

        return response()->json($wheel, 201);
    }

    /**
     * 
     * @OA\Get(
     *    path="/api/wheel/{id}",
     *    summary="Получение колеса по id",
     *    tags={"Колеса"},
     *    security={{"bearerAuth":{} }},
     * 
     *    @OA\Parameter(
     *        description="id колеса",
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
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *        )
     *    ),
     *    @OA\Response(
     *        response=404,
     *        description="Ничего не найдено",
     *        @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Колеса с таким id не существует")
     *        )
     *    ),
     *    
     * )
     */
    public function show($id)
    {
        $wheel = Wheel::with(['sectors.prize'])->withCount('sectors')->findOrFail($id);
        $wheel->days_of_week = json_decode($wheel->days_of_week);
        foreach($wheel->sectors as $sector){
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
        }
        return response()->json(["added sectors count"=>$wheel->sectors_count, "data"=>$wheel], 200);
    }

    /**
     * 
     * @OA\Put(
     *    path="/api/wheel/{id}",
     *    summary="Обновление колеса по id",
     *    tags={"Колеса"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
     *    @OA\Parameter(
     *        description="id колеса",
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
     *             @OA\Property(property="name", type="string", example="Акция мая"),
     *             @OA\Property(property="count_sectors", type="integer", example=5),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"Активно", "Не активно", "В архиве"},
     *                 example="Активно"
     *             ),
     *             @OA\Property(property="animation", type="boolean", example=true),
     *             @OA\Property(property="date_start", type="date", example="2025-05-01"),
     *             @OA\Property(property="date_end", type="date", example="2025-05-31"),
     *             @OA\Property(property="days_of_week", type="array", @OA\Items(type="string"),example={"Понедельник", "Среда"}),
     *             required={"name", "count_sectors","status","animation", "date_start", "date_end", "days_of_week"}
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
     *            @OA\Property(property="message", type="string", example="Колесо в архиве, удалять нельзя")
     *        )
     *    ),
     * 
     *    @OA\Response(
     *        response=400,
     *        description="Плохой запрос",
     *        @OA\JsonContent(
     *            @OA\Property(property="message", type="string", 
     *            example="Новая дата окончания не может быть раньше, чем текущая дата окончания")
     *        )
     *    ),
     *    @OA\Response(
     *        response=404,
     *        description="Ничего не найдено",
     *        @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Колеса с таким id не существует")
     *        )
     *    ),
     * )
     */
    public function update(UpdateWheelRequest $request, $id)
    {
    
        $wheel = Wheel::with(['sectors.prize'])->withCount('sectors')->findOrFail($id);
        $sectors_count = $wheel->sectors_count;

        if($request->status == StatusWeelType::active->value && $sectors_count < $wheel->count_sectors){
            return response()->json(["message"=>"Нельзя поставить статус Активный, пока количество секторов меньше чем задано"]);
        }

        if($wheel->status == StatusWeelType::active->value || $wheel->status == StatusWeelType::nonActive->value){
            if (strtotime($request->date_end) < strtotime($wheel->date_end)) {
                return response()->json(["message" => 
                "Новая дата окончания не может быть раньше, чем текущая дата окончания" . $wheel->date_end], 400);
            }
            $wheel->name = $request->name;
            $wheel->count_sectors = $request->count_sectors;
            $wheel->status = StatusWeelType::from($request->status);
            $wheel->animation = $request->animation;
            $wheel->date_start = $request->date_start;
            $wheel->date_end = $request->date_end;
            $wheel->days_of_week = json_encode($request->days_of_week);
            $wheel->save();
            return $wheel;
        }else{
            return response()->json(["message"=>"Колесо в архиве, изменять нельзя"],403);
        }
    }

    /**
     * 
     * @OA\Delete(
     *    path="/api/wheel/{id}",
     *    summary="Удаление колеса по id",
     *    tags={"Колеса"},
     *    security={{"bearerAuth":{"role": "admin"} }},
     * 
     *    @OA\Parameter(
     *        description="id колеса",
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
     *             @OA\Property(property="message", type="string", example="Колесо успешно удалено")
     *        )  
     *    ),
     * 
     *    @OA\Response(
     *        response=403,
     *        description="Колесо активно, удалить нельзя",  
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
     *             @OA\Property(property="message", type="string", example="Колеса с таким id не существует")
     *        )
     *    ),
     * )
     */
    public function destroy($id)
    {
        // $wheel->sectors()->delete();
        $wheel = Wheel::findOrFail($id);
        // dd($wheel->id);
        if($wheel->status != StatusWeelType::active->value){
            $name = $wheel->name;
            $wheel->delete();
            return response()->json(["message"=>"Колесо удалено успешно"]);
        }else{
            return response()->json(["message"=>"Колесо активно, удалить нельзя"],403);
        }
    }
}
