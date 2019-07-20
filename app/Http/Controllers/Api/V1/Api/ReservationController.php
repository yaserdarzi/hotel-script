<?php

namespace App\Http\Controllers\Api\V1\Api;

use App\Exceptions\ApiException;
use App\Hotel;
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
            CURLOPT_URL => env('CDN_AUTH_URL') . "/api/v1/cp/api/app/get/supplier",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json",
                "sales: api",
                "Authorization: " . $request->header('Authorization'),
                "appToken: " . $request->header('appToken')
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
        if (!$request->input('check_in'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                "plz check your check_in"
            );
        if (!$request->input('check_out'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                "plz check your check_out"
            );
        if (!$request->input('hotel_id'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                "plz check your hotel_id"
            );
        $startExplode = explode('/', $request->input('check_in'));
        $endExplode = explode('/', $request->input('check_out'));
        $start_date = \Morilog\Jalali\CalendarUtils::toGregorian($startExplode[0], $startExplode[1], $startExplode[2]);
        $end_date = \Morilog\Jalali\CalendarUtils::toGregorian($endExplode[0], $endExplode[1], $endExplode[2]);
        $startDay = date_create(date('Y-m-d', strtotime($start_date[0] . '-' . $start_date[1] . '-' . $start_date[2])));
        $endDay = date_create(date('Y-m-d', strtotime($end_date[0] . '-' . $end_date[1] . '-' . $end_date[2])));
        $endDayDate = date('Y-m-d', strtotime($end_date[0] . '-' . $end_date[1] . '-' . $end_date[2]));
        $diff = date_diff($startDay, $endDay);
        $roomId = [];
        $supplierID = json_decode($response)->data;
        $commissions = (array)json_decode($response)->meta->commissions;
        for ($i = 0; $i < $diff->days; $i++) {
            $date = strtotime(date('Y-m-d', strtotime($startDay->format('Y-m-d') . " +" . $i . " days")));
            $roomToday = RoomEpisode::
            where('app_id', $request->input('app_id'))
                ->whereIn('supplier_id', $supplierID)
                ->where([
                    'hotel_id' => $request->input('hotel_id'),
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
                'id as room_id',
                'hotel_id',
                'title',
                'desc',
                'capacity as bed_capacity',
                'bed',
                'is_breakfast',
                'is_lunch',
                'is_dinner',
                DB::raw("CASE WHEN image != '' THEN (concat ( '" . url('') . "/files/hotel/',hotel_id,'/room/', image) ) ELSE '' END as image"),
                DB::raw("CASE WHEN image != '' THEN (concat ( '" . url('') . "/files/hotel/',hotel_id,'/room/thumb/', image) ) ELSE '' END as image_thumb")
            )->get();
        foreach ($rooms as $key => $value) {
            if (in_array(Constants::APP . '-' . $value->hotel_id . '-' . $value->room_id, array_column($commissions, 'shopping_id'))) {
                $commission = $commissions[array_search(Constants::APP . '-' . $value->hotel_id . '-' . $value->room_id, array_column($commissions, 'shopping_id'))];
                $value->episode = RoomEpisode::
                where('app_id', $request->input('app_id'))
                    ->whereIn('supplier_id', $supplierID)
                    ->where([
                        'status' => Constants::STATUS_ACTIVE,
                        'room_id' => $value->room_id
                    ])->whereBetween('date', [$startDay, date('Y-m-d', strtotime('-1 day', strtotime($endDayDate)))])
                    ->orderBy('date')->get();
                $value->is_buy = true;
                $value->is_capacity = true;
                $value->add_price = 0;
                foreach ($value->episode as $keyEpisode => $valEpisode) {
                    $percent = 0;
                    $is_full = false;
                    if ($valEpisode->is_capacity == false)
                        $value->is_capacity = false;
                    else
                        $value->add_price += $valEpisode->add_price;
                    if ($valEpisode->capacity_remaining == 0) {
                        $value->is_buy = false;
                        $is_full = true;
                    }
                    if ($commission->is_price_power_up) {
                        $price = $valEpisode->price;
                        $price_computing = $valEpisode->price_power_up;
                        $price_percent = $valEpisode->price_power_up;
                        if ($valEpisode->type_percent == Constants::TYPE_PERCENT_PERCENT) {
                            if ($value->percent != 0) {
                                $percent = ($value->percent / 100) * $value->price_power_up;
                                $price_percent = $valEpisode->price_power_up - $percent;
                            }
                        } elseif ($valEpisode->type_percent == Constants::TYPE_PERCENT_PRICE) {
                            $percent = $valEpisode->percent;
                            $price_percent = $valEpisode->price_power_up - $valEpisode->percent;
                        }
                    } else {
                        $price = $valEpisode->price;
                        $price_computing = $valEpisode->price;
                        $price_percent = $valEpisode->price;
                        if ($valEpisode->type_percent == Constants::TYPE_PERCENT_PERCENT) {
                            if ($value->percent != 0) {
                                $percent = ($value->percent / 100) * $value->price;
                                $price_percent = $valEpisode->price - $percent;
                            }
                        } elseif ($valEpisode->type_percent == Constants::TYPE_PERCENT_PRICE) {
                            $percent = $valEpisode->percent;
                            $price_percent = $valEpisode->price - $valEpisode->percent;
                        }
                    }
                    if ($commission->type == Constants::TYPE_PERCENT_PERCENT) {
                        if ($commission->percent < 100)
                            $price_percent = intval($price_percent - (($commission->percent / 100) * $price_computing));
                    } elseif ($commission->type == Constants::TYPE_PERCENT_PRICE)
                        $price_percent = $price_percent - $commission->price;
                    $episode = [
                        'date' => CalendarUtils::strftime('Y-m-d', strtotime($valEpisode->date)),
                        'day' => CalendarUtils::strftime('%A', strtotime($valEpisode->date)),
                        'price' => $price,
                        'price_percent' => $price_percent,
                        'capacity_remaining' => $valEpisode->capacity_remaining,
                        'is_full' => $is_full,
                    ];
                    $value->price += $price;
                    $value->percent += $percent;
                    $value->price_percent += $price_percent;
                    $value->episode[$keyEpisode] = $episode;
                }
            } else
                unset($rooms[$key]);
        }
        return $this->respond($rooms->values()->all());
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
