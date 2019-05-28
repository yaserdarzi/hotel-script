<?php

namespace App\Http\Controllers\Api\V1\Agency;

use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use App\Room;
use App\RoomEpisode;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
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
            CURLOPT_URL => env('CDN_AUTH_URL') . "/api/v1/cp/agency/app/get/supplier",
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
        $startExplode = explode('/', $request->input('start_date'));
        $endExplode = explode('/', $request->input('end_date'));
        $start_date = \Morilog\Jalali\CalendarUtils::toGregorian($startExplode[0], $startExplode[1], $startExplode[2]);
        $end_date = \Morilog\Jalali\CalendarUtils::toGregorian($endExplode[0], $endExplode[1], $endExplode[2]);
        $startDay = date_create(date('Y-m-d', strtotime($start_date[0] . '-' . $start_date[1] . '-' . $start_date[2])));
        $endDay = date_create(date('Y-m-d', strtotime($end_date[0] . '-' . $end_date[1] . '-' . $end_date[2])));
        $diff = date_diff($startDay, $endDay);
        $roomId = [];
        $supplierID = json_decode($response)->data->supplier_sales;
        for ($i = 0; $i <= $diff->days; $i++) {
            $date = strtotime(date('Y-m-d', strtotime($startDay->format('Y-m-d') . " +" . $i . " days")));
            $roomToday = RoomEpisode::
            where('app_id', $request->input('app_id'))
                ->whereIn('supplier_id', $supplierID)
                ->where([
                    'status' => Constants::STATUS_ACTIVE,
                    'date' => date('Y-m-d', $date)
                ])
                ->where('capacity_remaining', '>', 0)
                ->groupBy('room_id')
                ->pluck('room_id');
            if (!sizeof($roomToday)) {
                $roomId = [];
                break;
            }
            if ($i == 0)
                $roomId = array_merge($roomId, $roomToday->toArray());
            else {
                $roomId = array_intersect($roomId, $roomToday->toArray());
            }
        }
        $rooms = Room::where('app_id', $request->input('app_id'))
            ->with('hotel')
            ->whereIn('id', $roomId)
            ->select(
                '*',
                DB::raw("CASE WHEN image != '' THEN (concat ( '" . url('') . "/files/hotel/',hotel_id,'/room/', image) ) ELSE '' END as image"),
                DB::raw("CASE WHEN image != '' THEN (concat ( '" . url('') . "/files/hotel/',hotel_id,'/room/thumb/', image) ) ELSE '' END as image_thumb")
            )
            ->get();
        foreach ($rooms as $key => $value) {
            $value->price = RoomEpisode::
            where('app_id', $request->input('app_id'))
                ->whereIn('supplier_id', $supplierID)
                ->where([
                    'status' => Constants::STATUS_ACTIVE,
                    'room_id' => $value->id
                ])
                ->where('capacity_remaining', '>', 0)
                ->whereBetween('date', [$startDay, $endDay])
                ->sum('price');
            $value->percent = RoomEpisode::
            where('app_id', $request->input('app_id'))
                ->whereIn('supplier_id', $supplierID)
                ->where([
                    'status' => Constants::STATUS_ACTIVE,
                    'room_id' => $value->id,
                    'type_percent' => Constants::TYPE_PERCENT_PRICE
                ])
                ->where('capacity_remaining', '>', 0)
                ->whereBetween('date', [$startDay, $endDay])
                ->sum('percent');
            $value->price_percent = $value->price - $value->percent;
            $percent = RoomEpisode::
            where('app_id', $request->input('app_id'))
                ->whereIn('supplier_id', $supplierID)
                ->where([
                    'status' => Constants::STATUS_ACTIVE,
                    'room_id' => $value->id,
                    'type_percent' => Constants::TYPE_PERCENT_PERCENT
                ])
                ->where('capacity_remaining', '>', 0)
                ->whereBetween('date', [$startDay, $endDay])
                ->get();
            $pricePercent = 0;
            $percentPercent = 0;
            if (sizeof($percent)) {
                foreach ($percent as $valPercent) {
                    $floatPercent = floatval("0." . $valPercent->percent);
                    $percentPercent = $percentPercent + ($valPercent->price * $floatPercent);
                    $pricePercent = $pricePercent + ($valPercent->price - intval($percentPercent));
                }
            }
            $value->percent = $value->percent + $percentPercent;
            $value->price_percent = $value->price_percent + $pricePercent;
        }
        return $this->respond($rooms);
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
