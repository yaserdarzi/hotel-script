<?php

namespace App\Http\Controllers\Api\V1\Site;

use App\Hotel;
use App\Http\Controllers\ApiController;
use App\Room;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;

class HotelController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $hotel = Hotel::whereIn('type_app_id', $request->input('app_id'))
            ->get();
        return $this->respond($hotel);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $hotel_id)
    {
        $hotel = Hotel::whereIn('type_app_id', $request->input('app_id'))
            ->with("gallery")
            ->where(['id' => $hotel_id])
            ->select('*', DB::raw("CASE WHEN icon != '' THEN (concat ( '" . url('') . "/files/hotel/', icon) ) ELSE '' END as icon"))
            ->first();
        return $this->respond($hotel);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        //
    }

    ///////////////////public function///////////////////////


}
