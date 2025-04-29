<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material_thing;
use App\Http\Requests\StoreMaterial_thingRequest;
use App\Http\Requests\UpdateMaterial_thingRequest;
use App\Models\UserPrize;

class MaterialThingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Material_thing::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMaterial_thingRequest $request)
    {
        $thing= Material_thing::create([
            'name'=>$request->name,
        ]);

        return response()->json($thing, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $material_thing = Material_thing::findOrFail($id);
        return $material_thing;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMaterial_thingRequest $request, $id)
    {
        $material_thing = Material_thing::findOrFail($id);
        $material_thing->name = $request->name;

        $material_thing->save();
        return $material_thing;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $material_thing = Material_thing::findOrFail($id);
        $userPrizes = UserPrize::where('prize_type', Material_thing::class)->where('prize_id', $id)->get();
        if(!$userPrizes->isEmpty()){
            return response()->json(["message"=>"Этот приз удалить нельзя, его выйграли"], 403);
        }
        $material_thing->delete();
        return response()->json(["message"=>"Вещь успешно удалена"]);
    }
}
