<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empty_prize;
use App\Http\Requests\StoreEmpty_prizeRequest;
use App\Http\Requests\UpdateEmpty_prizeRequest;
use App\Models\UserPrize;

class EmptyPrizeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Empty_prize::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmpty_prizeRequest $request)
    {
        $empty_prize = Empty_prize::create([
            'name'=>$request->name
        ]);

        return response()->json($empty_prize, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $empty_prize = Empty_prize::findOrFail($id);
        return $empty_prize;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmpty_prizeRequest $request, $id)
    {
        $empty_prize = Empty_prize::findOrFail($id);
        $empty_prize->name = $request->name;
        $empty_prize->save();

        return $empty_prize;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $empty_prize = Empty_prize::findOrFail($id);
        $userPrizes = UserPrize::where('prize_type', Empty_prize::class)->where('prize_id', $id)->get();
        if(!$userPrizes->isEmpty()){
            return response()->json(["message"=>"Этот приз удалить нельзя, его выйграли"], 403);
        }
        $empty_prize->delete();
        return response()->json(["message"=>"Пустой приз"]);
    }
}
