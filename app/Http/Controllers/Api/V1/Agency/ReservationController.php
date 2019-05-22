<?php

namespace App\Http\Controllers\Api\V1\Agency;

use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use App\RoomEpisode;
use Illuminate\Http\Request;
use App\Http\Requests;
use Morilog\Jalali\Jalalian;
use Morilog\Jalali\jDate;

class ReservationController extends ApiController
{
    protected $help;

    public function __construct()
    {
        $this->help = new Helpers();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$request->input('start_date'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن تاریخ شروع اجباری می باشد.'
            );
        if (!$request->input('end_date'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن تاریخ پایان اجباری می باشد.'
            );
        $start_date = \Morilog\Jalali\CalendarUtils::toGregorian(Jalalian::forge($request->input('start_date'))->getYear(), Jalalian::forge($request->input('start_date'))->getMonth(), Jalalian::forge($request->input('start_date'))->getDay());
        $end_date = \Morilog\Jalali\CalendarUtils::toGregorian(Jalalian::forge($request->input('end_date'))->getYear(), Jalalian::forge($request->input('end_date'))->getMonth(), Jalalian::forge($request->input('end_date'))->getDay());
        $startDay = date('Y-m-d', strtotime($start_date[0] . '-' . $start_date[1] . '-' . $start_date[2]));
        $endDay = date('Y-m-d', strtotime($end_date[0] . '-' . $end_date[1] . '-' . $end_date[2]));
        $roomEpisode = RoomEpisode::where('app_id', $request->input('app_id'))
            ->with('hotel', 'room')
            ->where(['status' => Constants::STATUS_ACTIVE])
            ->whereBetween('date', [$startDay, $endDay])
            ->select(
                "id",
                "app_id",
                "hotel_id",
                "room_id",
                "supplier_id",
                "capacity",
                "capacity_filled",
                "price",
                "type_percent",
                "percent",
                "date",
                "status"
            )->get();
        return $this->respond($roomEpisode);
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
    public function show(Request $request, $id)
    {
        //
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
    public function destroy($hotel_id, $id, Request $request)
    {
        //
    }

    ///////////////////public function///////////////////////


}
