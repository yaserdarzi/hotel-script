<?php

namespace App\Http\Controllers\Api\V1\Agency;

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
                "sales: agency",
                "Authorization: " . $request->header('Authorization'),
                "appToken: " . $request->header('appToken'),
                "appName: " . Constants::APP,
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
        $endDayDate = date('Y-m-d', strtotime($end_date[0] . '-' . $end_date[1] . '-' . $end_date[2]));
        $diff = date_diff($startDay, $endDay);
        $roomId = [];
        $supplierID = array_unique(
            array_merge(
                json_decode($response)->data->supplier_sales,
                json_decode($response)->data->supplier_agency
            )
        );
        $commissions = (array)json_decode($response)->data->commissions;
        for ($i = 0; $i < $diff->days; $i++) {
            $date = strtotime(date('Y-m-d', strtotime($startDay->format('Y-m-d') . " +" . $i . " days")));
            $roomToday = RoomEpisode::
            where('app_id', $request->input('app_id'))
                ->whereIn('supplier_id', $supplierID)
                ->where([
                    'status' => Constants::STATUS_ACTIVE,
                    'date' => date('Y-m-d', $date)
                ])->groupBy('room_id')
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
        $hotel = Hotel::where('app_id', $request->input('app_id'))
            ->select(
                'id',
                'app_id',
                'name',
                'address',
                'star',
                'sort',
                DB::raw("CASE WHEN logo != '' THEN (concat ( '" . url('') . "/files/hotel/', logo) ) ELSE '' END as logo"),
                DB::raw("CASE WHEN logo != '' THEN (concat ( '" . url('') . "/files/hotel/thumb/', logo) ) ELSE '' END as logo_thumb")
            )->orderBy('sort')->get();
        foreach ($hotel as $keyHotel => $valHotel) {
            $input = preg_quote(Constants::APP . '-' . $valHotel->id, '~');
            $result = preg_grep('~' . $input . '~', array_column($commissions, 'shopping_id'));
            if (sizeof($result)) {
                $valHotel->rooms = Room::where('app_id', $request->input('app_id'))
                    ->where('hotel_id', $valHotel->id)
                    ->whereIn('id', $roomId)
                    ->select(
                        '*',
                        DB::raw("CASE WHEN image != '' THEN (concat ( '" . url('') . "/files/hotel/',hotel_id,'/room/', image) ) ELSE '' END as image"),
                        DB::raw("CASE WHEN image != '' THEN (concat ( '" . url('') . "/files/hotel/',hotel_id,'/room/thumb/', image) ) ELSE '' END as image_thumb")
                    )->get();
                if (sizeof($valHotel->rooms))
                    foreach ($valHotel->rooms as $key => $value) {
                        if (in_array(Constants::APP . '-' . $valHotel->id . '-' . $value->id, array_column($commissions, 'shopping_id'))) {
                            $commission = $commissions[array_search(Constants::APP . '-' . $valHotel->id . '-' . $value->id, array_column($commissions, 'shopping_id'))];
                            $value->episode = RoomEpisode::
                            where('app_id', $request->input('app_id'))
                                ->whereIn('supplier_id', $supplierID)
                                ->where([
                                    'status' => Constants::STATUS_ACTIVE,
                                    'room_id' => $value->id
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
                                $price = $valEpisode->price;
                                $price_computing = $valEpisode->price_power_up;
                                $price_percent = $valEpisode->price_power_up;
                                if ($commission->type == Constants::TYPE_PERCENT_PERCENT) {
                                    if ($commission->percent < 100)
                                        $price_percent = intval($price_percent + (($commission->percent / 100) * $price_computing));
                                } elseif ($commission->type == Constants::TYPE_PERCENT_PRICE)
                                    $price_percent = $price_percent + $commission->price;
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
                            unset($hotel[$keyHotel]);
                    }
                else
                    unset($hotel[$keyHotel]);
            } else
                unset($hotel[$keyHotel]);
        }
        return $this->respond($hotel->values()->all());
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
