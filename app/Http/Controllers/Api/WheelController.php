<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wheel;
use App\Http\Requests\StoreWheelRequest;
use App\Http\Requests\UpdateWheelRequest;
use App\Models\Sector;
use App\Enums\StatusWheelType;
use Illuminate\Http\Request;
use App\Services\PrizeTypeService;
use Carbon\Carbon;

class WheelController extends Controller
{
    /**
    * @OA\Schema(
    *     schema="Wheel",
    *     type="object",
    *     @OA\Property(property="name", type="string", example="Акция мая"),
    *     @OA\Property(property="status", type="string", example="Не активно"),
    *     @OA\Property(property="date_start", type="date", example="2025-05-01"),
    *     @OA\Property(property="date_end", type="date", example="2025-05-31"),
    *     @OA\Property(property="days_of_week", type="array", @OA\Items(type="string"),example={"Понедельник", "Среда"}),
    *     @OA\Property(property="sectors", type="array", 
    *         @OA\Items(ref="#/components/schemas/Sector")
    *     )
    * )
    *
    * @OA\Schema(
    *     schema="WheelWithOutSectors",
    *     type="object",
    *     @OA\Property(property="name", type="string", example="Акция мая"),
    *     @OA\Property(property="status", type="string", example="Не активно"),
    *     @OA\Property(property="date_start", type="date", example="2025-05-01"),
    *     @OA\Property(property="date_end", type="date", example="2025-05-31"),
    *     @OA\Property(property="days_of_week", type="array", @OA\Items(type="string"),example={"Понедельник", "Среда"}),
    * )
    *
    * 
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
    *     @OA\Parameter(
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
     *    @OA\Response(
     *        response=200,
     *        description="ОК",
     *        @OA\JsonContent(
     *            type="array",
     *            @OA\Items(ref="#/components/schemas/Wheel")
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

        $perPage = $request->input('per_page', 10);
        $wheels = $query->paginate($perPage);

        foreach ($wheels as $wheel) {
            foreach($wheel->sectors as $sector){
                $sector->prize_type = PrizeTypeService::classToString($sector->prize_type);
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
     *        @OA\JsonContent(
     *            @OA\Property(property="name", type="string", example="Акция мая"),
     *            @OA\Property(property="status", type="string", example="Не активно"),
     *            @OA\Property(property="date_start", type="date", example="2025-05-01"),
     *            @OA\Property(property="date_end", type="date", example="2025-05-31"),
     *            @OA\Property(property="days_of_week", type="array", @OA\Items(type="string"),example={"Понедельник", "Среда"}),
     *            @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-07T12:31:41.000000Z"),
     *            @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-07T12:39:53.000000Z"),
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
    public function store(StoreWheelRequest $request)
    {

        $dateStart = Carbon::parse($request->date_start);
        $dateEnd = Carbon::parse($request->date_end);
        if ($dateEnd <= $dateStart) {
            return response()->json(["message" => "Дата окончания должна быть позже даты начала."], 422);
        }

        $wheelDays = $request->days_of_week;
        foreach ($wheelDays as $key => $item) {
            $wheelDays[$key] = mb_strtolower($item);
        }
        
        $wheel = Wheel::create([
            'name'=>$request->name,
            'date_start'=>$request->date_start,
            'date_end'=>$request->date_end,
            'status'=>StatusWheelType::nonActive,
            'days_of_week' =>$wheelDays,
        ]);

        return response()->json($wheel, 201);
    }

    /**
     * 
     * @OA\Get(
     *    path="/api/wheel/{id}",
     *    summary="Получение колеса по id",
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
     *            ref="#/components/schemas/Wheel"
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
        foreach($wheel->sectors as $sector){
            $sector->prize_type = PrizeTypeService::classToString($sector->prize_type);
        }
        return response()->json($wheel, 200);
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
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"Активно", "Не активно", "В архиве"},
     *                 example="Активно"
     *             ),
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
     *        @OA\JsonContent(
     *            @OA\Property(property="name", type="string", example="Акция мая"),
     *            @OA\Property(property="status", type="string", example="Не активно"),
     *            @OA\Property(property="date_start", type="date", example="2025-05-01"),
     *            @OA\Property(property="date_end", type="date", example="2025-05-31"),
     *            @OA\Property(property="days_of_week", type="array", @OA\Items(type="string"),example={"Понедельник", "Среда"}),
     *            @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-07T12:31:41.000000Z"),
     *            @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-07T12:39:53.000000Z"),
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
     *            @OA\Property(property="message", type="string", example="Колесо в архиве, изменять нельзя")
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
    
        $wheel = Wheel::withCount('sectors')->findOrFail($id);
        $sectors_count = $wheel->sectors_count;

        $sectors = Sector::where('wheel_id', $id)->get();
        $probability = 0;
        foreach($sectors as $sector){
            $probability += $sector->probability;
        }

        if($request->status == StatusWheelType::active->value && $sectors_count < 4){
            return response()->json(["message"=>"Нельзя присвоить колесу статус Активный, пока количество секторов меньше чем 4"],403);
        }

        if($request->status == StatusWheelType::active->value && $probability == 0){
            return response()->json(["message"=>"Нельзя присвоить колесу статус Активный, пока общая вероятность равна 0"],403);
        }

        $dateStart = Carbon::parse($request->date_start);
        $dateEnd = Carbon::parse($request->date_end);
        if ($dateEnd <= $dateStart) {
            return response()->json(["message" => "Дата окончания должна быть позже даты начала."], 422);
        }

        $wheelDays = $request->days_of_week;
        foreach ($wheelDays as $key => $item) {
            $wheelDays[$key] = mb_strtolower($item);
        }

        if($wheel->status == StatusWheelType::active->value || $wheel->status == StatusWheelType::nonActive->value){
            $wheel->name = $request->name;
            $wheel->status = StatusWheelType::from($request->status)->value;
            $wheel->date_start = $request->date_start;
            $wheel->date_end = $request->date_end;
            $wheel->days_of_week = $wheelDays;
            $wheel->save();
            return response()->json($wheel, 200);
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
        $wheel = Wheel::findOrFail($id);
        if($wheel->status != StatusWheelType::active->value){
            $wheel->delete();
            return response()->json(["message"=>"Колесо удалено успешно"]);
        }else{
            return response()->json(["message"=>"Колесо активно, удалить нельзя"],403);
        }
    }

    
}
