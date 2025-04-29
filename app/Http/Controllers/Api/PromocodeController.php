<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promocode;
use App\Http\Requests\StorePromocodeRequest;
use App\Http\Requests\UpdatePromocodeRequest;
use App\Enums\DiscountType;
use App\Models\UserPrize;

class PromocodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Promocode::all();
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePromocodeRequest $request)
    {
        $promocode = Promocode::create([
            'type_discount' => DiscountType::from($request->type_discount),
            'discount_value'=>$request->discount_value,
            'expiry_date'=>$request->expiry_date,
        ]);

        return response()->json($promocode, 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(Promocode $promocode)
    {
        $promocode = Promocode::findOrFail($id);
        return $promocode;
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePromocodeRequest $request, $id)
    {
        $promocode = Promocode::findOrFail($id);
        $promocode->type_discount = DiscountType::from($request->type_discount);
        $promocode->discount_value = $request->discount_value;
        $promocode->expiry_date = $request->expiry_date;

        $promocode->save();
        return $promocode;
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $promocode = Promocode::findOrFail($id);
        $userPrizes = UserPrize::where('prize_type', Promocode::class)->where('prize_id', $id)->get();
        if(!$userPrizes->isEmpty()){
            return response()->json(["message"=>"Этот приз удалить нельзя, его выйграли"], 403);
        }
        $promocode->delete();
        return response()->json(["message"=>"Промокод успешно удален"]);
    }

}
