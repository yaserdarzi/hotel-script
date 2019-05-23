<?php

namespace App\Http\Controllers\Api\V1\Agency;

use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use App\RoomEpisode;
use Illuminate\Http\Request;
use App\Http\Requests;
use Morilog\Jalali\CalendarUtils;
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
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('CDN_AUTH_URL') . "/api/v1/app/get/supplier/active/sales",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json",
                "sales: agency"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        if ($err)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                $err
            );
        if ($info['http_code'] != 200)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                json_decode($response)->error
            );
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
        if (!$request->input('capacity'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن تعداد مهمان اجباری می باشد.'
            );
        $start_date = \Morilog\Jalali\CalendarUtils::toGregorian(Jalalian::forge($request->input('start_date'))->getYear(), Jalalian::forge($request->input('start_date'))->getMonth(), Jalalian::forge($request->input('start_date'))->getDay());
        $end_date = \Morilog\Jalali\CalendarUtils::toGregorian(Jalalian::forge($request->input('end_date'))->getYear(), Jalalian::forge($request->input('end_date'))->getMonth(), Jalalian::forge($request->input('end_date'))->getDay());
        $startDay = date('Y-m-d', strtotime($start_date[0] . '-' . $start_date[1] . '-' . $start_date[2]));
        $endDay = date('Y-m-d', strtotime($end_date[0] . '-' . $end_date[1] . '-' . $end_date[2]));
        $roomEpisode = RoomEpisode::
        join(Constants::ROOM_DB, Constants::ROOM_EPISODE_DB . '.room_id', '=', Constants::ROOM_DB . '.id')
            ->where(Constants::ROOM_EPISODE_DB . '.app_id', $request->input('app_id'))
            ->with('hotel', 'room')
            ->whereIn(Constants::ROOM_EPISODE_DB . '.supplier_id', json_decode($response)->data->supplier_id)
            ->where([Constants::ROOM_EPISODE_DB . '.status' => Constants::STATUS_ACTIVE])
            ->where(Constants::ROOM_DB . '.capacity', '>=', $request->input('capacity'))
            ->whereBetween(Constants::ROOM_EPISODE_DB . '.date', [$startDay, $endDay])
            ->select(
                Constants::ROOM_EPISODE_DB . ".id",
                Constants::ROOM_EPISODE_DB . ".app_id",
                Constants::ROOM_EPISODE_DB . ".hotel_id",
                Constants::ROOM_EPISODE_DB . ".room_id",
                Constants::ROOM_EPISODE_DB . ".supplier_id",
                Constants::ROOM_DB . ".capacity as room_capacity",
                Constants::ROOM_EPISODE_DB . ".capacity",
                Constants::ROOM_EPISODE_DB . ".capacity_filled",
                Constants::ROOM_EPISODE_DB . ".capacity_remaining",
                Constants::ROOM_EPISODE_DB . ".price",
                Constants::ROOM_EPISODE_DB . ".type_percent",
                Constants::ROOM_EPISODE_DB . ".percent",
                Constants::ROOM_EPISODE_DB . ".date",
                Constants::ROOM_EPISODE_DB . ".status"
            )->get()->map(function ($value) {
                $value->date_persian = CalendarUtils::strftime('Y-m-d', strtotime($value->date));
                return $value;
            });
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
