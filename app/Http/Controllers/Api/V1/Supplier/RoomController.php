<?php

namespace App\Http\Controllers\Api\V1\Supplier;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;

class RoomController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($hotel_id, Request $request)
    {
        $room = Room::whereIn('type_app_id', $request->input('app_id'))
            ->where(['hotel_id' => $hotel_id])
            ->select('*', DB::raw("CASE WHEN image != '' THEN (concat ( '" . url('') . "/files/room/', image) ) ELSE '' END as image"))
            ->get();
        return $this->respond($room);
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
    public function store($hotel_id,Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($hotel_id, Request $request, $room_id)
    {
        $room = Room::whereIn('type_app_id', $request->input('app_id'))
            ->with("gallery")
            ->where(['hotel_id' => $hotel_id, 'id' => $room_id])
            ->select('*', DB::raw("CASE WHEN image != '' THEN (concat ( '" . url('') . "/files/room/', image) ) ELSE '' END as image"))
            ->first();
        return $this->respond($room);
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
    public function update($hotel_id,Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($hotel_id,$id, Request $request)
    {
        //
    }

    ///////////////////public function///////////////////////


}
