<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserPrize;
use App\Models\Promocode;
use App\Models\Material_thing;
use App\Http\Requests\StoreUserPrizeRequest;
use App\Http\Requests\UpdateUserPrizeRequest;

class UserPrizeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return UserPrize::with('prize')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserPrizeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $userPrize = UserPrize::with('prize')->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserPrizeRequest $request, UserPrize $userPrize)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    public function get_user_prizes($userId){

        $userPrizes = UserPrize::with('prize')->where('user_id', $userId)->get();
        return response()->json($userPrizes);
    }
}
