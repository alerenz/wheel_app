<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wheel;
use App\Http\Requests\StoreWheelRequest;
use App\Http\Requests\UpdateWheelRequest;
use App\Models\Sector;
use App\Enums\StatusWeelType;

class WheelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $wheels = Wheel::with(['sectors.prize'])->get();
        foreach($wheels as $wheel){
            $wheel->days_of_week = json_decode($wheel->days_of_week);
        }
        return response()->json($wheels);
    }

    /**
     * Store a newly created resource in storage.
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
     * Display the specified resource.
     */
    public function show($id)
    {
        $wheel = Wheel::with(['sectors.prize'])->withCount('sectors')->findOrFail($id);
        $wheel->days_of_week = json_decode($wheel->days_of_week);
        return response()->json(["added sectors count"=>$wheel->sectors_count, "data"=>$wheel], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWheelRequest $request, $id)
    {
    
        $wheel = Wheel::findOrFail($id);
        if($wheel->status == StatusWeelType::active->value || $wheel->status == StatusWeelType::nonActive->value){
            if (strtotime($request->date_end) < strtotime($wheel->date_end)) {
                return response()->json(["message" => 
                "Новая дата окончания не может быть раньше, чем текущая дата окончания " . $wheel->date_end], 400);
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
            return response()->json(["message"=>"Колесо ".$wheel->name." в архиве, изменять нельзя"],403);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // $wheel->sectors()->delete();
        $wheel = Wheel::findOrFail($id);
        // dd($wheel->id);
        if($wheel->status != StatusWeelType::active->value){
            $name = $wheel->name;
            $wheel->delete();
            return response()->json(["message"=>"Колесо ".$name." удалено успешно"]);
        }else{
            return response()->json(["message"=>"Колесо активно, удалить нельзя"],403);
        }
    }
}
